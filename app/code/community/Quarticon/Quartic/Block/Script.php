<?php

class Quarticon_Quartic_Block_Script extends Mage_Core_Block_Template
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
}
