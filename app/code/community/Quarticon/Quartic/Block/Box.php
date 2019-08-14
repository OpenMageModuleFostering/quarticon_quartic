<?php
class Quarticon_Quartic_Block_Box extends Mage_Core_Block_Template
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
}
