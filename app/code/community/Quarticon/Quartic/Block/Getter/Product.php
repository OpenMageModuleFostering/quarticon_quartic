<?php

class Quarticon_Quartic_Block_Getter_Product extends Quarticon_Quartic_Block_Script
{

    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }

    public function isActive()
    {
        return $this->getConfig()->isActive();
    }

    public function getCustomer()
    {
        return $this->getConfig()->getCustomer();
    }

    public function getUser()
    {
        return $this->getConfig()->getSession()->getCustomer()->getId();
    }

    /**
     * returns current page product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $product = $this->getData('product');
        if (empty($product)) {
            $product = Mage::registry('current_product');
            $this->setProduct($product);
        }
        return $product;
    }

    /**
     * returns products prices for tags
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductPrices($product) {
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $price_old = 0;
            $price = $product->getPriceModel()->getTotalPrices($product, 'min', true);
        } else {
            $price_old = Mage::helper('tax')->getPrice($product, $product->getPrice(), true);
            $price = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
        }
        return array(
            'price_old' => $price_old,
            'price' => $price
        );
    }

    /**
     * if show quartic tags
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function showQuarticTags($product) {
        return in_array($product->getTypeId(), array(
            Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
            Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
            'downloadable'
        ));
    }
}
