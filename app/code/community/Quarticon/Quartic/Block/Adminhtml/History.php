<?php
class Quarticon_Quartic_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('quartic/history.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('quartic/adminhtml_history_grid', 'quartic_history_grid'));
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
