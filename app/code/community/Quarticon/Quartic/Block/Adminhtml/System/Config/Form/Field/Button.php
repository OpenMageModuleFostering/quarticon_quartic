<?php

/**
 * Used in fields that require registration to show
 */
class Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Field_Button extends Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Field_Text
{

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl($element->getFieldConfig()->button_url);

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($element->getData('label'))
            ->setOnClick("setLocation('{$url}')")
            ->toHtml();

        return $html;
    }
}
