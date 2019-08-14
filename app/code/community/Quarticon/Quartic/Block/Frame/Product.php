<?php
class Quarticon_Quartic_Block_Frame_Product extends Mage_Catalog_Block_Product_View {
    
    protected function getConfig() {
        return Mage::getModel('quartic/config');
    }
    
    public function isActive() {
        return $this->getConfig()->isActive() && $this->getConfig()->isFrameEnabled('product');
    }
    
    public function getCustomer() {
        return $this->getConfig()->getCustomer();
    }
    
    public function getUser() {
        return $this->getConfig()->getSession()->getCustomer()->getId();
    }
    
    public function getProductId() {
        return Mage::helper('quartic')->getProduct($this->getProduct());
    }
    
}