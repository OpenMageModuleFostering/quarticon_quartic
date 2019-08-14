<?php

/**
 * Used in fields that require registration to show
 */
class Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Field_Text extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $post = $element->getId();
        $status = $this->quarticStatus();
        if (!empty($status)) {
            return parent::render($element);
        } else {
            return '';
        }
    }

    protected function quarticStatus()
    {
        return (bool) Mage::getStoreConfig("quartic/config/status", Mage::app()->getStore());
    }
}
