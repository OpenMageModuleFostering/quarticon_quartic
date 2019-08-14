<?php
class Quarticon_Quartic_Block_Frame_Category extends Mage_Catalog_Block_Category_View {
    
    protected function _construct() {
        if ($this->getConfig()->shouldUseProductFrameInCategory()) {
            $this->setTemplate('quartic/frame/product.phtml');
        } else {
            $this->setTemplate('quartic/frame/category.phtml');
        }
    }
    
    protected function getConfig() {
        return Mage::getModel('quartic/config');
    }
    
    public function isActive() {
        return $this->getConfig()->isActive() && $this->getConfig()->isFrameEnabled('category');
    }
    
    public function getCustomer() {
        return $this->getConfig()->getCustomer();
    }
    
    public function getUser() {
        return $this->getConfig()->getSession()->getCustomer()->getId();
    }
    
    /**
     * Gets SKU/id of random product in the current category.
     * 
     * @return string
     */
    public function getProductId() {
        return Mage::helper('quartic')->getProduct($this->getRandomProduct());
    }
    
    /**
     * Gets random product from current category
     * 
     * @return Mage_Catalog_Model_Product
     */
    public function getRandomProduct() {
        $category = Mage::getModel('catalog/category')->load($this->getCurrentCategory()->getId());
        $count = $category->getProductCount();
        if ($count) {
            $randomIndex = rand(0, $count - 1);
            $productCollection = $category->getProductCollection()
                    ->setPageSize(1)
                    ->setCurPage($randomIndex);
            return $productCollection->getFirstItem();
        }
        return null;
    }
    
}