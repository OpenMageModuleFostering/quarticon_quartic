<?php

class Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Field_Register extends Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Field_Text
{
    /*
     * Set template
     */

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('quartic/system/config/button_register.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return 'http://www.quarticon.com/magento/';
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'id' => 'quartic_register',
                    'label' => $this->helper('adminhtml')->__('Register'),
                    'onclick' => 'javascript:qon_check(); return false;'
                    )
            );

        return $button->toHtml();
    }

    protected function quarticStatus()
    {
        $status = !(parent::quarticStatus());
        return $status;
    }
}
