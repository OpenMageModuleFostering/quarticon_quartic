<?php
class Quarticon_Quartic_Block_Getter_Product extends Mage_Catalog_Block_Product_View {
    
    protected function getConfig() {
        return Mage::getModel('quartic/config');
    }
    
    public function isActive() {
        return $this->getConfig()->isActive();
    }
    
    public function getCustomer() {
        return $this->getConfig()->getCustomer();
    }
    
    public function getUser() {
        return $this->getConfig()->getSession()->getCustomer()->getId();
    }
    
}