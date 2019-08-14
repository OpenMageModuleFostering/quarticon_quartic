<?php

/**
 * Config form fieldset renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);

        /**
         * Empty check begin
         */
        $fields = $this->_getFieldsHtml($element);
        if (empty($fields)) {
            return '';
        }
        $html .= $fields;
        unset($fields);
        /**
         * Empty check end
         */
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * Return rendered fields of an element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getFieldsHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '';
        foreach ($element->getSortedElements() as $field) {
            $html.= $field->toHtml();
        }
        return $html;
    }
}
