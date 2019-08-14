<?php



class Quarticon_Quartic_Model_System_Config_Source_Groupedprice
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options[] = array('value' => 0, 'label' => Mage::helper('quartic')->__('Minimum from simple products'));
            $this->_options[] = array('value' => 1, 'label' => Mage::helper('quartic')->__('Maximum from simple products'));
        }
        return $this->_options;
    }
}
