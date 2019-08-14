<?php



class Quarticon_Quartic_Model_System_Config_Source_Configurablechilds
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options[] = array('value' => 0, 'label' => Mage::helper('quartic')->__('Do not show'));
            $this->_options[] = array('value' => 1, 'label' => Mage::helper('quartic')->__('Show if possible'));
        }
        return $this->_options;
    }
}
