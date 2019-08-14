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

    public function getProduct()
    {
        $product = $this->getData('product');
        if (empty($product)) {
            $product = Mage::registry('current_product');
            $this->setProduct($product);
        }
        return $product;
    }
}
