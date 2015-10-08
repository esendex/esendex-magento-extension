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

use Esendex\Http\HttpException;

/**
 * Class Esendex_Sms_Model_SentMessages
 *
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Sms_Model_SentMessages extends Varien_Data_Collection
{
    /**
     * @var Esendex_Sms_Model_Api_Api
     */
    private $esendexApi = null;

    /**
     * @return Esendex_Sms_Model_Api_Api
     */
    public function getEsendexApi()
    {
        if (!$this->esendexApi) {
            $this->esendexApi = Esendex_Sms_Model_Api_Factory::getInstance();
        }
        return $this->esendexApi;
    }

    /**
     * @param Esendex_Sms_Model_Api_Api $esendexApi
     */
    public function setEsendexApi(Esendex_Sms_Model_Api_Api $esendexApi)
    {
        $this->esendexApi = $esendexApi;
    }

    /**
     * Get Collection Data from API
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @throws Exception
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            try {
                // First make an empty API call to get totalSize
                $this->_totalRecords = $this->getEsendexApi()->getSentMessages(0, 0)->totalCount();

                /**
                 * @var Esendex\Model\SentMessagesPage
                 */
                $messages = $this->getEsendexApi()->getSentMessages(
                    ($this->getCurPage() -1) * $this->getPageSize(),
                    $this->getPageSize()
                );
            } catch (HttpException $e) {
                throw $e;
            }

            foreach ($messages as $message) {
                $messageObj = new Varien_Object();

                $submittedAt = $message->submittedAt() instanceof \DateTime
                    ? $message->submittedAt()->format('d/m/Y - H:i:s')
                    : 'Not submitted';

                $lastStatusAt = $message->lastStatusAt() instanceof \DateTime
                    ? $message->lastStatusAt()->format('d/m/Y - H:i:s')
                    : 'No updates';

                $sentAt = $message->sentAt() instanceof \DateTime
                    ? $message->sentAt()->format('d/m/Y - H:i:s')
                    : 'Not sent';

                $deliveredAt = $message->deliveredAt() instanceof \DateTime
                    ? $message->deliveredAt()->format('d/m/Y - H:i:s')
                    : 'Not delivered';

                $messageObj->setData([
                    "type"         => $message->type(),
                    "originator"   => $message->originator(),
                    "recipient"    => $message->recipient(),
                    "summary"      => $message->summary(),
                    "status"       => $message->status(),
                    "submittedAt"  => $submittedAt,
                    "lastStatusAt" => $lastStatusAt,
                    "sentAt"       => $sentAt,
                    "deliveredAt"  => $deliveredAt
                ]);

                $this->addItem($messageObj);
            }

            $this->_setIsLoaded();
        }

        return $this;
    }

    /**
     * Get the set size from API call
     * Override because we don't want an infinite loop by calling load()
     *
     * @return int
     */
    public function getSize()
    {
        return $this->_totalRecords;
    }

    /**
     * Set page size
     * Change loaded flag to false
     *
     * @param int $size
     * @return $this
     */
    public function setPageSize($size)
    {
        parent::setPageSize($size);
        $this->_setIsLoaded(false);
        return $this;
    }

    /**
     * Set the current page
     * Change loaded flag to false
     *
     * @param int $page
     * @return $this
     */
    public function setCurPage($page)
    {
        parent::setCurPage($page);
        $this->_setIsLoaded(false);
        return $this;
    }
}
