<?php
class Quarticon_Quartic_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    
    protected function _initAction()
    {
        return $this;
    }
    
    public function urlsAction()
    {
        $this->_title($this->__('System'))
            ->_title($this->__('Quartic'))
            ->_title($this->__('Feed URLs'));
        $this->loadLayout();
        $this->renderLayout();
    }
}
