<?php
class Quarticon_Quartic_FrameController extends Mage_Core_Controller_Front_Action
{
    
    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }
    
    public function IndexAction()
    {
        $slot = $this->getRequest()->getParam('slot');
        $block = $this->getLayout()->createBlock('quartic/products')->setTemplate('quartic/products/'.$slot.'.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }
}
