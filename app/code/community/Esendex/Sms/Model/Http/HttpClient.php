<?php
/**
 * Copyright (C) 2015 Esendex Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the Esendex Community License v1.0 as published by
 * the Esendex Ltd.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * Esendex Community Licence v1.0 for more details.
 *
 * You should have received a copy of the Esendex Community Licence v1.0
 * along with this program.  If not, see <http://www.esendex.com/esendexcommunitylicence/>
 */

use Esendex\Authentication\IAuthentication;
use Esendex\Http\BadRequestException;
use Esendex\Http\IHttp;
use Esendex\Http\MethodNotAllowedException;
use Esendex\Http\NotImplementedException;
use Esendex\Http\PaymentRequiredException;
use Esendex\Http\RequestTimedOutException;
use Esendex\Http\ResourceNotFoundException;
use Esendex\Http\ServerErrorException;
use Esendex\Http\ServiceUnavailableException;
use Esendex\Http\UnauthorisedException;
use Esendex\Http\UserCredentialsException;

/**
 * Class Esendex_Sms_Model_Http_HttpClient
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Http_HttpClient implements IHttp
{
    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var string
     */
    private static $certificateBundle;

    /**
     * @var bool
     */
    private $isSecure;

    /**
     * @param string $userAgent
     * @param string $apiCertLocation
     * @param bool   $secure
     */
    public function __construct($userAgent, $apiCertLocation, $secure = true)
    {
        $this->isSecure     = $secure;
        $this->userAgent    = $userAgent;

        self::$certificateBundle = $apiCertLocation;
        if (empty(self::$certificateBundle)) {
            throw new RuntimeException("WARN: Could not locate CA Bundle. Secure web requests will fail");
        }
    }

    /**
     * @param null|bool $secure
     * @return bool
     */
    public function isSecure($secure = null)
    {
        if (isset($secure) && is_bool($secure)) {
            $this->isSecure = $secure;
        }
        return $this->isSecure;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getUserAgentDetail()
    {
        return $this->userAgent;
    }

    /**
     * @param string          $url
     * @param IAuthentication $authentication
     *
     * @return string
     */
    public function get($url, IAuthentication $authentication)
    {
        $results = $this->request($url, $authentication, 'GET');
        return $results['data'];
    }

    /**
     * @param string          $url
     * @param IAuthentication $authentication
     * @param mixed           $data
     *
     * @return string
     */
    public function put($url, IAuthentication $authentication, $data)
    {
        $results = $this->request($url, $authentication, 'PUT', $data);
        return $results['data'];
    }

    /**
     * @param string          $url
     * @param IAuthentication $authentication
     * @param mixed           $data
     *
     * @return string
     */
    public function post($url, IAuthentication $authentication, $data)
    {
        $results = $this->request($url, $authentication, 'POST', $data);
        return $results['data'];
    }

    /**
     * @param string          $url
     * @param IAuthentication $authentication
     *
     * @return int
     */
    public function delete($url, IAuthentication $authentication)
    {
        $results = $this->request($url, $authentication, 'DELETE');

        return $results['statuscode'];
    }

    /**
     * @param string            $url
     * @param IAuthentication   $authentication
     * @param string            $method
     * @param null|mixed        $data
     *
     * @return array
     * @throws Exception
     */
    private function request($url, $authentication, $method, $data = null)
    {
        $httpHeaders = array("Authorization: {$authentication->getEncodedValue()}");

        $curlHandle = \curl_init();

        \curl_setopt($curlHandle, CURLOPT_URL, $url);
        \curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);
        \curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true); // Allow redirects.
        \curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        \curl_setopt($curlHandle, CURLOPT_HEADER, false);
        \curl_setopt($curlHandle, CURLOPT_CAINFO, self::$certificateBundle);
        \curl_setopt($curlHandle, CURLOPT_USERAGENT, $this->userAgent);
        \curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
        if ($method == 'PUT' || $method == 'POST') {
            \curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
            \curl_setopt($curlHandle, CURLOPT_BINARYTRANSFER, true);
            if (strlen($data) == 0) {
                $httpHeaders[] = 'Content-Length: 0';
            }
            $httpHeaders[] = 'Content-Type: application/xml; charset=utf-8';
        }
        \curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $httpHeaders);

        $result = \curl_exec($curlHandle);
        $curlInfo = \curl_getinfo($curlHandle);

        $results = array();
        $results['data'] = $result;
        $results['statuscode'] = $curlInfo["http_code"];

        \curl_close($curlHandle);

        if ($results['statuscode'] < 200 || $results['statuscode'] >= 300) {
            throw $this->getHttpException($results, $curlInfo);
        }

        $results['url'] = $curlInfo['url'];
        return $results;
    }

    /**
     * @param array $result
     * @param array $info
     *
     * @return Exception
     */
    private function getHttpException(array $result, array $info = null)
    {
        $http_code = $result["statuscode"];
        $data = $result["data"];
        $error_message = strlen($data) != 0 && $data != $http_code
            ? $data
            : "The requested URL returned error: {$http_code}";
        switch ($http_code) {
            case 400:
                return new BadRequestException($error_message, $http_code, $info);
            case 401:
                return new UnauthorisedException($error_message, $http_code, $info);
            case 402:
                return new PaymentRequiredException($error_message, $http_code, $info);
            case 403:
                return new UserCredentialsException($error_message, $http_code, $info);
            case 404:
                return new ResourceNotFoundException($error_message, $http_code, $info);
            case 405:
                return new MethodNotAllowedException($error_message, $http_code, $info);
            case 408:
                return new RequestTimedOutException($error_message, $http_code, $info);
            case 500:
                return new ServerErrorException($error_message, $http_code, $info);
            case 501:
                return new NotImplementedException($error_message, $http_code, $info);
            case 503:
                return new ServiceUnavailableException($error_message, $http_code, $info);
            default:
                $error_message = "An unexpected error occurred processing the web request";
                return new \Exception($error_message, $http_code);
        }
    }
}