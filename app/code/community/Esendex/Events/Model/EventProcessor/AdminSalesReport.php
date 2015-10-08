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

use Psr\Log\LoggerInterface;

/**
 * Class Esendex_Events_Model_EventProcessor_AdminSalesReport
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Events_Model_EventProcessor_AdminSalesReport
    extends Esendex_Sms_Model_EventProcessor_Abstract
    implements Esendex_Sms_Model_EventProcessor_Interface
{
    /**
     * @var array
     */
    public static $frequencies = array(
        'daily'   => 'Daily',
        'weekly'  => 'Weekly',
        'monthly' => 'Monthly'
    );

    /**
     * @var array
     */
    protected $variables = array(
        'store_name'      => 'storename',
        'start_date'      => 'startdate',
        'end_date'        => 'enddate',
        'total_sales'     => 'numberoforders',
        'qty_items_sold'  => 'numberofitemssold',
        'net_total'       => 'nettotal',
        'gross_total'     => 'grandtotal'
    );

    /**
     * @var LoggerInterface|null
     */
    protected $logger = null;

    /**
     * Start date for report
     *
     * @var null|DateTime
     */
    protected $startDate;

    /**
     * End date for report
     *
     * @var null|DateTime
     */
    protected $endDate;

    /**
     * Current date string for better unit tests
     * @var string
     */
    protected $currentDate = 'now';

    /**
     * Allow for better unit tests
     * @var int
     */
    protected $firstDay;

    /**
     * Allow for better unit tests
     * @var string|null
     */
    protected $timezone = null;

    /**
     * We need to check the frequency of the cron
     *
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     * @return bool
     */
    public function shouldSend(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        try {
            $startDate  = new DateTime($trigger->getData('start_date'));
            $now        = new DateTime($this->currentDate);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }

        // Don't send if we haven't reached the start date yet
        if ($startDate > $now) {
            return false;
        }

        $frequency = $trigger->getData('frequency');

        // If its daily we only run the cron once a day so send
        if ($frequency === 'daily') {
            return true;
        }

        // Get the diff between the two dates
        $diff = $now->diff($startDate);

        if ($frequency === 'weekly') {
            // Weekly check, get total days diff
            $daysDiff = (int) $diff->format('%a');

            // If exactly divisible by 7 we are a valid week diff
            if ($daysDiff % 7 === 0) {
                return true;
            }
        } else {
            // Return if same day of the month as start date
            if ($now->format('d') === $startDate->format('d')) {
                return true;
            }

            // if start day is greater than total days in this month
            $isLargerMonth = (int) $startDate->format('d') > (int) $now->format('t');

            // if current day is last day of month
            if ($isLargerMonth && $now->format('d') === $now->format('t')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build container with available variables
     *
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     * @return Varien_Object
     * @throws Exception
     */
    public function getVariableContainer(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        $data = array();
        if ($this->parameters instanceof Varien_Object) {
            $data = $this->parameters->getData();
        }

        $frequency = $trigger->getData('frequency');
        $this->setPeriodDateByFrequency($frequency);

        $storeId   = reset($trigger->getData('store_id'));
        $currency  = Mage::app()->getStore($storeId)->getDefaultCurrency();
        $endTotals = $this->getReportTotals($this->startDate, $this->endDate, $storeId);

        $data = array_merge($data, array(
            'store_name'     => $this->getStoreName($storeId),
            'start_date'     => $this->startDate->format('d/m/Y H:i'),
            'end_date'       => $this->endDate->format('d/m/Y H:i'),
            'net_total'      => $currency->format($endTotals->getData('net'), array(), false),
            'gross_total'    => $currency->format($endTotals->getData('gross'), array(), false),
            'total_sales'    => $endTotals->getData('total_orders'),
            'qty_items_sold' => number_format($endTotals->getData('total_items'), 2)
        ));

        $container = new Varien_Object($data);
        return $container;
    }

    /**
     * Get start and end DateTime objects from frequency
     *
     * @param $frequency
     * @throws Exception
     */
    protected function setPeriodDateByFrequency($frequency)
    {
        $timezone  = new DateTimeZone($this->getTimezone());

        try {
            $startDate = new DateTime($this->currentDate, $timezone);
            $endDate   = clone $startDate;
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        }

        // DateTime intervals
        $dayInterval    = new DateInterval('P1D');
        $monthInterval  = new DateInterval('P1M');

        // Get to and from dates
        switch ($frequency) {
            case 'weekly':
                $now          = clone $startDate;
                $firstWeekDay = $this->getFirstDay();
                $days         = array('sun', 'mon', 'tue', 'wed', 'thur', 'fri', 'sat');

                $endDate
                    ->modify(sprintf('this %s', $days[$firstWeekDay]))
                    ->sub($dayInterval)
                    ->setTime(23, 59, 59);

                if ($endDate > $now) {
                    $endDate->sub(new DateInterval('P7D'));
                }

                $startDate = clone $endDate;
                $startDate
                    ->sub(new DateInterval('P6D'))
                    ->setTime(0, 0, 0);
                break;
            case 'monthly':
                $startDate
                    ->sub($monthInterval)
                    ->modify('first day of this month')
                    ->setTime(0, 0, 0);
                $endDate
                    ->modify('first day of this month')
                    ->setTime(0, 0, 0)
                    ->sub(new DateInterval('PT1S'));
                break;
            case 'daily':
            default:
                $startDate
                    ->sub($dayInterval)
                    ->setTime(0,0,0);
                $endDate
                    ->sub($dayInterval)
                    ->setTime(23, 59, 59);
                break;
        }

        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }


    /**
     * @param DateTime   $startDate
     * @param DateTime   $endDate
     * @param string|int $storeId
     * @return Varien_Object
     */
    protected function getReportTotals(DateTime $startDate, DateTime $endDate, $storeId)
    {
        $collection = $this->getOrderResourceCollection();

        $collection->addFieldToFilter('created_at', array(
            'from' => $startDate->format(Varien_Date::DATETIME_PHP_FORMAT),
            'to'   => $endDate->format(Varien_Date::DATETIME_PHP_FORMAT)
        ));

        $collection->addFieldToFilter('store_id', $storeId);

        $collection->calculateTotals();

        return $collection->getFirstItem();
    }

    /**
     * Get current store name or view name if not set
     *
     * @param int $storeId
     * @return string
     */
    public function getStoreName($storeId)
    {
        $storeName = Mage::getStoreConfig('general/store_information/name', $storeId);

        return $storeName ?: Mage::app()->getStore($storeId)->getName();
    }

    /**
     * Get the order resource collection model
     *
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function getOrderResourceCollection()
    {
        return Mage::getResourceModel('esendex_events/adminSalesReport_order_collection');
    }

    /**
     * Get the recipients for this cron
     *
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     *
     * @return array
     */
    public function getRecipient(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        $recipients = $trigger->getData('recipients');

        if (!is_array($recipients)) {
            return array();
        }

        return $recipients;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return int
     */
    public function getFirstDay()
    {
        if ($this->firstDay) {
            return $this->firstDay;
        }

        return Mage::getStoreConfig('general/locale/firstday');

    }

    /**
     * Allow to inject first day for unit tests
     *
     * @param int $firstDay
     */
    public function setFirstDay($firstDay)
    {
        $this->firstDay = $firstDay;
    }

    /**
     * Set the current date for better unit tests
     *
     * @param string $date
     */
    public function setCurrentDate($date)
    {
        $this->currentDate = $date;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        if ($this->timezone) {
            return $this->timezone;
        }

        return Mage::getStoreConfig('general/locale/timezone');
    }

    /**
     * Set timezone for better unit tests
     *
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return null;
    }

    /**
     * @return DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
}