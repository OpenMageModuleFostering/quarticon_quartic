<?php

class Quarticon_Quartic_Block_Cart extends Mage_Core_Block_Template
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

    public function getStoreName()
    {
        return $this->getConfig()->getStoreName();
    }

    public function getProducts() {
        $quote = Mage::helper('checkout/cart')->getCart()->getQuote();
        $items = $quote->getAllVisibleItems();
        $ids = array();
        foreach($items as $item) {
            $ids[] = $item->getProductId();
        }
        return implode(',', $ids);
    }
}
