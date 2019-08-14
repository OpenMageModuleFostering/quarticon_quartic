<?php



class Quarticon_Quartic_Model_System_Config_Source_Groupedchildimage
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options[] = array('value' => 0, 'label' => Mage::helper('quartic')->__('Get from simple product'));
            $this->_options[] = array('value' => 1, 'label' => Mage::helper('quartic')->__('Get from parent product'));
        }
        return $this->_options;
    }
}
