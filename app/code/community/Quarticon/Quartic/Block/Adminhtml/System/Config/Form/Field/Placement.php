<?php

/**
 * Used in fields that require registration to show
 */
class Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Field_Placement extends Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Field_Text
{

    protected function quarticStatus()
    {
		// TODO: przenieść pobieranie storeId do helpera
		$params = Mage::app()->getRequest()->getParams();
		if(isset($params['store'])) {
			$storeId = (is_numeric($params['store'])) ? (int)$params['store'] : Mage::getModel('core/store')->load($params['store'], 'code')->getId();
		} elseif(isset($params['website'])) {
			$website = (is_numeric($params['website'])) ? Mage::getModel('core/website')->load($params['website']) : Mage::getModel('core/website')->load($params['website'], 'code');
			$storeId = $website->getDefaultGroup()->getDefaultStoreId();
		} else {
			$storeId = Mage::app()->getStore()->getId();
		}
		
        $status_api = (bool) Mage::getStoreConfig("quartic/config/status", $storeId);
        $status_placements = (bool) Mage::getStoreConfig("quartic/config/modified_placements", $storeId);
        return $status_api && $status_placements;
    }
}
