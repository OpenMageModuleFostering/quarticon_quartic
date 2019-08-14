<?php



class Quarticon_Quartic_Model_System_Config_Source_Configurableprice
{
    protected $_options;
    
    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options[] = array('value' => 0, 'label' => Mage::helper('quartic')->__('Standard configurable product price'));
			$this->_options[] = array('value' => 1, 'label' => Mage::helper('quartic')->__('Minimum price'));
			$this->_options[] = array('value' => 2, 'label' => Mage::helper('quartic')->__('Maximum price'));
//			$this->_options[] = array('value' => 3, 'label' => Mage::helper('quartic')->__('Price range'));
        }
        return $this->_options;
    }
}
