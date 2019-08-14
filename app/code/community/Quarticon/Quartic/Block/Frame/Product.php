<?php

class Quarticon_Quartic_Block_Frame_Product extends Mage_Catalog_Block_Product_View
{

    protected $frame = null;

    protected function getFrame()
    {
        if (is_null($this->frame)) {
            $this->frame = Mage::getModel('quartic/frame');
            $this->frame->setFrameName('product');
            $this->frame->setPlacement($this->getPlacement());
            $this->frame->setFrameDivId('slt_product');
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

    public function getProductId()
    {
        return Mage::helper('quartic')->getProduct($this->getProduct());
    }
}
