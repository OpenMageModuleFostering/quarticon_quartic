<?php
class Quarticon_Quartic_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('quartic_history_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
    }
    
    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('quartic/history')->getCollection();
        foreach ($collection as &$item) {
            $item->setQuarticProductsUrl($item->getUrl('quartic/feed/products', array('hash' => $this->getConfig()->getHash())));
            $item->setQuarticOrdersUrl($item->getUrl('quartic/feed/orders', array('hash' => $this->getConfig()->getHash())));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('store_id', array(
            'header' => Mage::helper('quartic')->__('Store ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'store_id',
        ));
        $this->addColumn('date', array(
            'header' => Mage::helper('quartic')->__('Transaction date'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'date',
        ));
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        //return $row->getCeneoUrl();
    }
}
