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
		
        return (bool) Mage::getStoreConfig("quartic/config/status", $storeId);
    }
}
