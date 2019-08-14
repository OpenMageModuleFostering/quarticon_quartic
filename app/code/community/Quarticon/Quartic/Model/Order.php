<?php

class Quarticon_Quartic_Model_Order extends Mage_Core_Model_Abstract
{

    const ITERATION_STEP = 1000;

    protected $_db = null;
    protected $_orders_table = null;
    protected $_order_items_table = null;
    protected $_products_table = null;
    protected $_store_id = null;

    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }

    protected function _getStoreId()
    {
        if (!$this->_store_id) {
            $store = Mage::app()->getStore();
            $this->_store_id = $store->getStoreId();
        }
        return $this->_store_id;
    }

    protected function _getDB()
    {
        if (!$this->_db) {
            $this->_db = Mage::getSingleton('core/resource')->getConnection('core_read');
        }
        return $this->_db;
    }

    protected function _getOrdersTable()
    {
        if (!$this->_orders_table) {
            $this->_orders_table = $this->_getDB()->getTableName('sales_flat_order');
        }
        return $this->_orders_table;
    }

    protected function _getOrderItemsTable()
    {
        if (!$this->_order_items_table) {
            $this->_order_items_table = $this->_getDB()->getTableName('sales_flat_order_item');
        }
        return $this->_order_items_table;
    }

    protected function _getProductsTable()
    {
        if (!$this->_products_table) {
            $this->_products_table = $this->_getDB()->getTableName('catalog_product_entity');
        }
        return $this->_products_table;
    }

    public function getCollectionCount()
    {
        $sql = 'select count(*) as c from ' . $this->_getOrdersTable() . ' as o ' .
            'left join ' . $this->_getOrderItemsTable() . ' as i on(i.order_id = o.entity_id and i.parent_item_id is null) ' .
            'where o.state="complete"';
            //'where o.store_id = ' . $this->_getStoreId();

        $q = $this->_getDB()->fetchRow($sql);
        return $q['c'];
    }

    public function getAll($page_num = 1, $page_size = 10)
    {
        $sql = 'select o.increment_id, o.created_at, o.customer_id, i.product_id, i.sku, i.qty_ordered, i.price_incl_tax, i.product_type, p.sku as real_sku from ' . $this->_getOrdersTable() . ' as o ' .
            'left join ' . $this->_getOrderItemsTable() . ' as i on(i.order_id = o.entity_id and i.parent_item_id is null) ' .
            'left join ' . $this->_getProductsTable() . ' as p on(i.product_id = p.entity_id) ' .
            'where o.state="complete" order by i.item_id limit ' . (($page_num - 1) * $page_size) . ', ' . $page_size;
        //'where o.store_id = ' . $this->_getStoreId() . ' order by i.item_id limit ' . (($page_num - 1) * $page_size) . ', ' . $page_size;
        $collection = $this->_getDB()->fetchAll($sql);
        foreach ($collection as $order) {
            $o = array(
                'id' => $order['increment_id'],
                'timestamp' => $order['created_at'],
                'user' => $order['customer_id'] ? $order['customer_id'] : '',
                'product' => Mage::helper('quartic')->getProduct($order),
                'quantity' => $order['qty_ordered'] * 1,
                'price' => $order['price_incl_tax']
            );
            $orders[] = $o;
            unset($o);
        }
        unset($collection);
        return $orders;
    }
    
    public function getFilePath() {
        return 'var/cache/quartic_order_feed.xml';
    }
}
