<?php
class Quarticon_Quartic_Block_Products extends Mage_Catalog_Block_Product_List
{
    
    protected function getConfig()
    {
        return Mage::getSingleton('quartic/config');
    }
    
    public function getLoadedProductCollection()
    {
        
        $use_sku = $this->getConfig()->isUsingSkuEnabled(); /* SKU or ID */
        $request = $this->getRequest();
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*');

        if ($use_sku) {
            $sku_array = $request->getParam('sku');
            $collection->addFieldToFilter('sku', array('in' => $sku_array));
        } else {
            $id_array = $request->getParam('sku');
            $collection->addFieldToFilter('entity_id', array('in' => $id_array));
        }
        if ($this->getConfig()->showOnlyInStock()) {
            $collection->joinField(
                'qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            )
                ->addAttributeToFilter('qty', array('gt' => 0));
        }
        return $collection;
    }
    
    public function getQuarticUrl($product)
    {
        $use_sku = $this->getConfig()->isUsingSkuEnabled(); /* SKU or ID */
        if ($use_sku) {
            $skuid = $product->getSku();
        } else {
            $skuid = $product->getId();
        }
        $request = $this->getRequest();
        $skuid_array = $request->getParam('sku');
        $url_array = $request->getParam('url');
        $i = array_search($skuid, $skuid_array);
        $quartic_url = $url_array[$i] ? $url_array[$i] : $product->getProductUrl();
        return $quartic_url;
    }
    
    public function getQuarticAddToCartUrl($product)
    {
        return $this->getAddToCartUrl($product);
    }
}
