<?php

class Quarticon_Quartic_Block_Adminhtml_Maps_Renderer_Select extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{

    /**
     * Render select for mapping grid
     */
    public function render(Varien_Object $row)
    {

        $value = $row->getData($this->getColumn()->getIndex());
        $data = $row->getData();
        $id = $data['id'];
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        $html = '<select name="mapped[' . $id . ']" style="width: 100%">';
        $html .= '<option value="">--</option>';
        foreach ($attributes as $attribute) {
            $selected = '';
            if ($attribute->getAttributecode() == $value) {
                $selected = "selected";
            }
            $html .= '<option value="' . $attribute->getAttributecode() . '" ' . $selected . '>' . $attribute->getAttributecode() . '</option>';
        }
        $html .= "</select>";

        return $html;
    }
}
