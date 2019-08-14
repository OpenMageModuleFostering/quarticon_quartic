<?php
class Quarticon_Quartic_Model_Attribute extends Mage_Core_Model_Abstract
{
    
    protected function _construct()
    {
        $this->_init('quartic/attribute');
    }
    
    public function toOptionArray()
    {
        $attributes_collection = Mage::getModel('catalog/entity_attribute')->getCollection()
            ->addFieldToFilter('entity_type_id', $this->_getEntityTypeId());
        $res = array();
        foreach ($attributes_collection as $attribute) {
            $res[$attribute->getAttributeCode()] = array(
                'label' => $attribute->getAttributeCode(),
                'value' => $attribute->getAttributeCode()
            );
        }
        ksort($res);
        return array_merge(array(array('label' => '', 'value' => '')), $res);
    }
    
    public function getOptionsByCode($code)
    {
        $attr = Mage::getModel('eav/config')->getAttribute('catalog_product', $code);
        $options = $attr->getSource()->getAllOptions();
        $res = array();
        foreach ($options as $option) {
            $res[$option['value']] = $option['label'];
        }
        unset($res['']);
        return $res;
    }
    
    /**
     * Gets catalog product EAV entity type id.
     *
     * @return string
     */
    protected function _getEntityTypeId()
    {
        $collection = Mage::getModel('eav/entity_type')->getCollection()
                ->addFieldToFilter('entity_type_code', 'catalog_product');
        $item = $collection->getFirstItem();
        return $item->getId();
    }
}
