<?php

class Quarticon_Quartic_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_debug;

    public function log($content = '')
    {
        if ($this->getDebug()) {
            Mage::log($content, null, 'quartic.log');
        }
    }

    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }

    protected function getDebug()
    {
        if (is_null($this->_debug)) {
            $this->_debug = $this->getConfig()->isDebug();
        }
        return $this->_debug;
    }

    /**
     * Gets product SKU or ID based on module configuration.
     *
     * @param  array|object $item
     * @return string
     */
    public function getProduct($item)
    {
        if ($item) {
            $use_sku = $this->getConfig()->isUsingSkuEnabled();
            $product = $use_sku ?
                (
                is_array($item) ? ($item['real_sku'] ? $item['real_sku'] : $item['sku']) : $this->_getRealSku($item)
                ) :
                (
                is_array($item) ? (isset($item['product_id']) ? $item['product_id'] : $item['id']) :
                    ($item->getProductId() ? $item->getProductId() : $item->getId())
                );
            return $product;
        }
        return false;
    }

    /**
     * Gets real SKU of item, ie. parent item's SKU
     *
     * @param  array|object $item
     * @return string
     */
    protected function _getRealSku($item)
    {
        if ($item->getProductType() === 'configurable') {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $sku = $product->getSku();
            $product->clearInstance();
            unset($product);
            return $sku;
        } else {
            return $item->getSku();
        }
    }
}
