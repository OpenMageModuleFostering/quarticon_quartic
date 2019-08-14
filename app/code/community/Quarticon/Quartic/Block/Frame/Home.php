<?php

class Quarticon_Quartic_Block_Frame_Home extends Mage_Core_Block_Template
{

    protected $frame = null;

    protected function getFrame()
    {
        if (is_null($this->frame)) {
            $this->frame = Mage::getModel('quartic/frame');
            $this->frame->setFrameName('homepage');
            $this->frame->setPlacement($this->getPlacement());
            $this->frame->setFrameDivId('slt_home');
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
}
