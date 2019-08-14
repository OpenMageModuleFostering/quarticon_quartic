<?php



class Quarticon_Quartic_Model_System_Config_Source_Visibility
{
    protected $_options;
    
    public function toOptionArray()
    {
        if (!$this->_options) {
            $options = Mage::getModel('catalog/product_visibility')->getOptionArray();
			foreach($options as $value => $option) {
				$this->_options[] = array('value' => $value, 'label' => $option);
			}
        }
        return $this->_options;
    }
}
