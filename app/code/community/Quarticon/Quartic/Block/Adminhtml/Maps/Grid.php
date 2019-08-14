<?php

class Quarticon_Quartic_Block_Adminhtml_Maps_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('quartic_maps_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
    }

    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('quartic/maps')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('quartic')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));
        $this->addColumn('quartic_attribute', array(
            'header' => Mage::helper('quartic')->__('Quartic Attribute'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'quartic_attribute',
        ));

        $this->addColumn('magento_attribute', array(
            'header' => Mage::helper('quartic')->__('Magento Attribute'),
            'align' => 'left',
            'renderer' => 'quartic/adminhtml_maps_renderer_select',
            'index' => 'magento_attribute'
        ));

        return parent::_prepareColumns();
    }
}
