<?php
class Quarticon_Quartic_Block_Frame_Cart extends Mage_Checkout_Block_Cart {
    
    protected function getConfig() {
        return Mage::getModel('quartic/config');
    }
    
    public function isActive() {
        return $this->getConfig()->isActive() && $this->getConfig()->isFrameEnabled('cart');
    }
    
    public function getCustomer() {
        return $this->getConfig()->getCustomer();
    }
    
    public function getUser() {
        return $this->getConfig()->getSession()->getCustomer()->getId();
    }
    
    public function getProducts() {
        $items = $this->getQuote()->getAllItems();
        $products = array();
        foreach ($items as $item) {
			if (!$item->getParentItemId()) {
				$product = Mage::helper('quartic')->getProduct($item);
				if (!in_array($product, $products)) {
					$products[] = $product;
				}
			}
        }
        return implode(',', $products);
    }
    
}