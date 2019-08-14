<?php

/**
 * Used in fields that require registration to show
 */
class Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Field_Placement extends Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Field_Text
{

    protected function quarticStatus()
    {
        $status_api = (bool) Mage::getStoreConfig("quartic/config/status", Mage::app()->getStore());
        $status_placements = (bool) Mage::getStoreConfig("quartic/config/modified_placements", Mage::app()->getStore());
        return $status_api && $status_placements;
    }
}
