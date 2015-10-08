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
 * Class Esendex_Sms_Block_Adminhtml_System_Config_CronTime
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Events_Block_Adminhtml_System_Config_CronTime extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->addClass('select');

        $hours      = 0;
        $minutes    = 0;

        if ($value = $element->getValue()) {
            $values = explode(',', $value);
            if (is_array($values) && count($values) == 2) {
                $hours      = $values[0];
                $minutes    = $values[1];
            }
        }
        $optionTemplate  = '<option value="%s" %s>%s</option>';
        $html            = sprintf('<input type="hidden" id="%s" />', $element->getHtmlId());
        $html           .= sprintf('<select name="%s" %s style="width:60px">', $element->getName(), $element->serialize($element->getHtmlAttributes()));

        for ($i = 0; $i < 24; $i++) {
            $hour    = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html   .= sprintf($optionTemplate, $hour, ((int) $hours === $i) ? 'selected="selected"' : '', $hour);
        }

        $html .= '</select>&nbsp;:&nbsp;';
        $html .= sprintf('<select name="%s" %s style="width:60px">', $element->getName(), $element->serialize($element->getHtmlAttributes()));

        for ($i = 0; $i < 60; $i++) {
            $minute  = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html   .= sprintf($optionTemplate, $minute, ((int) $minutes === $i) ? 'selected="selected"' : '', $minute);
        }
        $html .= '</select>';
        $html .= $element->getAfterElementHtml();
        return $html;
    }
}