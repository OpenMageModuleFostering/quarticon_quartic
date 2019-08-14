<?php
class Quarticon_Quartic_Block_Getter_Order extends Quarticon_Quartic_Block_Script
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
    
    public function getOrder()
    {
        return Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
    }
    
    public function getProducts()
    {
        $items = $this->getOrder()->getAllItems();
        $products = array();
        foreach ($items as $item) {
            if (!$item->getParentItemId()) {
                $product = Mage::helper('quartic')->getProduct($item);
                $products[] = array(
                    'product' => $product,
                    'price' => $item->getPriceInclTax(),
                    'quantity' => (int)$item->getQtyOrdered()
                );
            }
        }
        return $products;
    }
}
