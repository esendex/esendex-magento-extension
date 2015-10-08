<?php

/**
 * Class Esendex_Sms_Block_Adminhtml_Grid_Renderer_Translate
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Sms_Block_Adminhtml_Grid_Renderer_Translate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render the string translated by Esendex SMS helper
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        return Mage::helper('esendex_sms')->__($value);
    }
}
