<?php

class Quarticon_Quartic_Block_Frame_Cart extends Mage_Checkout_Block_Cart
{

    protected $frame = null;

    protected function getFrame()
    {
        if (is_null($this->frame)) {
            $this->frame = Mage::getModel('quartic/frame');
            $this->frame->setFrameName('cart');
            $this->frame->setPlacement($this->getPlacement());
            $this->frame->setFrameDivId('slt_cart');
        }
        return $this->frame;
    }

    public function isActive()
    {
        return $this->getFrame()->isActive();
    }

    public function getCustomer()
    {
        return $this->getFrame()->getCustomer();
    }

    public function getUser()
    {
        return $this->getFrame()->getUser();
    }

    public function getSnippetBody()
    {
        return $this->getFrame()->getSnippetBody();
    }

    public function getSnippetId()
    {
        return $this->getFrame()->getSnippetId();
    }

    public function getProducts()
    {
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
