<?php
class Quarticon_Quartic_Block_Adminhtml_System_Config_Form_Iframe extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $url = Mage::helper('quartic')->getAdminPageLink();
        return '<iframe style="border: 0;" src="'.$url.'" frameborder="0" width="100%" height="640px"></iframe>';
    }
}