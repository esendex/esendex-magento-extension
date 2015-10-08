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
 * Class Esendex_Events_Model_Resource_AdminSalesReport_Order_Collection
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Events_Model_Resource_AdminSalesReport_Order_Collection
    extends Mage_Reports_Model_Resource_Order_Collection
{
    /**
     * Override to...
     *
     *  * Add GROSS total
     *  * Use store values not base
     *  * Ignore canceled and closed on NET
     *
     * @return $this
     */
    public function calculateTotals()
    {
        $this->setMainTable('sales/order');
        $this->removeAllFieldsFromSelect();

        // For net total sub query
        $netSelect = clone $this->getSelect();
        $netSelect->where('main_table.state NOT IN (?)', array(
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            Mage_Sales_Model_Order::STATE_NEW,
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CLOSED,
        ));

        // Build net revenue expressions
        $netExp = $this->_getSalesAmountExpression();

        // Net expression * base rate
        $netSelect->columns(array(
            'total' => new Zend_Db_Expr(sprintf('SUM(%s)', $netExp))
        ));

        // Build final select
        $adapter       = $this->getConnection();
        $totalInvoiced = $adapter->getIfNullSql('main_table.total_invoiced', 0);

        $this
            ->getSelect()
            ->columns(array(
                'net'          => $netSelect,
                'gross'        => new Zend_Db_Expr(sprintf('SUM(%s)', $totalInvoiced)),
                'total_orders' => 'COUNT(main_table.entity_id)',
                'total_items'  => 'SUM(main_table.total_qty_ordered)'
            ))
            ->where('main_table.state NOT IN (?)', array(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_NEW
            ));

        return $this;
    }

    /**
     * Get sales amount expression
     *
     * Override to use store not base values
     *
     * @return string
     */
    protected function _getSalesAmountExpression()
    {
        if (is_null($this->_salesAmountExpression)) {
            $adapter       = $this->getConnection();
            $expressionDTO = new Varien_Object(array(
                'expression' => '%s - %s - %s - (%s - %s - %s)',
                'arguments' => array(
                    $adapter->getIfNullSql('main_table.total_invoiced', 0),
                    $adapter->getIfNullSql('main_table.tax_invoiced', 0),
                    $adapter->getIfNullSql('main_table.shipping_invoiced', 0),
                    $adapter->getIfNullSql('main_table.total_refunded', 0),
                    $adapter->getIfNullSql('main_table.tax_refunded', 0),
                    $adapter->getIfNullSql('main_table.shipping_refunded', 0),
                )
            ));

            Mage::dispatchEvent('sales_prepare_amount_expression', array(
                'collection'        => $this,
                'expression_object' => $expressionDTO,
            ));

            $this->_salesAmountExpression = vsprintf(
                $expressionDTO->getExpression(),
                $expressionDTO->getArguments()
            );
        }

        return $this->_salesAmountExpression;
    }
}