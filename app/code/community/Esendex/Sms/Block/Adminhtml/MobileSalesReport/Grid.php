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

/**
 * Class Esendex_Sms_Block_Adminhtml_MobileSalesReport_Grid
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Block_Adminhtml_MobileSalesReport_Grid extends Esendex_Sms_Block_Adminhtml_Trigger_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('mobileSalesReportGrid');
    }

    /**
     * Prepare collection
     *
     * @return self
     */
    protected function _prepareCollection()
    {
        if (!$this->getCollection()) {
            $collection = Mage::getModel('esendex_sms/trigger')
                ->getCollection()
                ->addEvents()
                ->onlyMobileSalesReports();

            $this->setCollection($collection);
        }

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid collection
     *
     * @return self
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('event_name');
    }
}
