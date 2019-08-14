<?php
class Quarticon_Quartic_Block_Adminhtml_Maps extends Mage_Adminhtml_Block_Widget_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('quartic/maps.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('quartic/adminhtml_maps_grid', 'quartic_maps_grid'));
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
