<?php

class Quarticon_Quartic_Block_Frame_Category extends Mage_Catalog_Block_Category_View
{

    protected $frame = null;

    protected function _construct()
    {
        if ($this->getConfig()->shouldUseProductFrameInCategory()) {
            $this->setTemplate('quartic/frame/product.phtml');
        } else {
            $this->setTemplate('quartic/frame/category.phtml');
        }
    }

    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }

    protected function getFrame()
    {
        if (is_null($this->frame)) {
            $this->frame = Mage::getModel('quartic/frame');
            $this->frame->setFrameName('category');
            $this->frame->setPlacement($this->getPlacement());
            $this->frame->setFrameDivId('slt_category');
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

    /**
     * Gets SKU/id of random product in the current category.
     *
     * @return string
     */
    public function getProductId()
    {
        return Mage::helper('quartic')->getProduct($this->getRandomProduct());
    }

    /**
     * Gets random product from current category
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getRandomProduct()
    {
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
