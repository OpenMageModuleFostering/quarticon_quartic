<?php
class Quarticon_Quartic_Block_Adminhtml_Urls extends Mage_Adminhtml_Block_Widget_Container
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('quartic/urls.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('quartic/adminhtml_urls_grid', 'quartic_urls_grid'));
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
