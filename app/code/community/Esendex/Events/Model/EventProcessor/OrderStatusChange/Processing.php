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
 * Class Esendex_Events_Model_EventProcessor_OrderStatusChange_Processing
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Events_Model_EventProcessor_OrderStatusChange_Processing
    extends Esendex_Events_Model_EventProcessor_OrderStatusChange_Abstract
{
    /**
     * Order status to notify on
     */
    protected $orderStatus = 'processing';
}