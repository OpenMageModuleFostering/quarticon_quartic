<?php

class Quarticon_Quartic_Block_Getter_Cart extends Quarticon_Quartic_Block_Script
{

    protected $_quote = null;

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

    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        return $this->_quote;
    }

    public function getProductsCsv()
    {
        $items = $this->getQuote()->getAllItems();
        $products = array();
        foreach ($items as $item) {
            if (!$item->getParentItemId()) {
                $products[] = Mage::helper('quartic')->getProduct($item);
            }
        }
        return implode(',', $products);
    }
}
