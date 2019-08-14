<?php

class Quarticon_Quartic_Model_Config extends Mage_Core_Model_Config
{

    public function getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function saveHash($area = 'default',$id = 0)
    {
        $hash = md5(microtime());
        Mage::getModel('core/config')->saveConfig('quartic/config/hash', $hash, $area, $id);
    }

    public function getHash($store_id = null)
    {
        return Mage::getStoreConfig('quartic/config/hash', $store_id);
    }
	
    public function isDebug($store_id = null)
    {
        return (bool) Mage::getStoreConfig('quartic/config/debug', $store_id);
    }

    public function isActive($store_id = null)
    {
        return (bool) Mage::getStoreConfig('quartic/config/active', $store_id);
    }

    public function isFrameEnabled($frame, $store_id = null)
    {
        $frame = Mage::getStoreConfig('quartic/frames/' . $frame, $store_id);
        return !empty($frame);
    }

    public function getFrameId($frame, $store_id = null)
    {
        return (string) Mage::getStoreConfig('quartic/frames/' . $frame, $store_id);
    }

    public function getCustomer($store_id = null)
    {
        return Mage::getStoreConfig('quartic/config/customer', $store_id);
    }

    public function isUsingSkuEnabled($store_id = null)
    {
        return (bool) Mage::getStoreConfig('quartic/config/use_sku', $store_id);
    }

    public function showOnlyInStock($store_id = null)
    {
        return (bool) Mage::getStoreConfig('quartic/config/only_in_stock', $store_id);
    }

    public function addThumbs($store_id = null)
    {
        return (bool) Mage::getStoreConfig('quartic/config/add_thumbs', $store_id);
    }

    public function showDisabledProducts($store_id = null)
    {
        return (bool) Mage::getStoreConfig('quartic/config/show_disabled_products', $store_id);
    }

    public function shouldUseProductFrameInCategory($store_id = null)
    {
        return (bool) Mage::getStoreConfig('quartic/frames/category_use_product_frame', $store_id);
    }

    /**
     * Gets minimum quantity for which a product status should be set to 1 in XML feed.
     *
     * @param int $store_id
     * @return int
     */
    public function getMinQty($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/config/min_qty', $store_id);
    }

    public function getVisibility($store_id = null)
    {
        return explode(',',Mage::getStoreConfig('quartic/config/visibility', $store_id));
    }

    public function getConfigurablePrice($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/composite/configurable_price', $store_id);
    }

    public function getShowConfigurableChilds($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/composite/configurable_childs', $store_id);
    }

    public function getConfigurableChildImage($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/composite/configurable_child_image', $store_id);
    }

    public function getConfigurableChildPrice($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/composite/configurable_child_price', $store_id);
    }

    public function getConfigurableChildRedirect($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/composite/configurable_child_redirect', $store_id);
    }

    public function getGroupedChildImage($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/composite/grouped_child_image', $store_id);
    }

    public function getGroupedChildPrice($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/composite/grouped_child_price', $store_id);
    }

    public function getGroupedChildRedirect($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/composite/grouped_child_redirect', $store_id);
    }

    public function getUseFileCache($store_id = null)
    {
        return (int) Mage::getStoreConfig('quartic/config/feed/product/use_file_cache', $store_id);
    }
}
