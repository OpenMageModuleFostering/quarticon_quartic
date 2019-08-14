<?php

class Quarticon_Quartic_Model_Order extends Mage_Core_Model_Abstract
{

    const ITERATION_STEP = 1000;

    protected $_resource = null;
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
    
    protected function _getResource()
    {
        if (!$this->_resource) {
            $this->_resource = Mage::getSingleton('core/resource');
        }
        return $this->_resource;
    }

    protected function _getDB()
    {
        if (!$this->_db) {
            $this->_db = $this->_getResource()->getConnection('core_read');
        }
        return $this->_db;
    }

    protected function _getOrdersTable()
    {
        if (!$this->_orders_table) {
            $this->_orders_table = $this->_getResource()->getTableName('sales_flat_order');
        }
        return $this->_orders_table;
    }

    protected function _getOrderItemsTable()
    {
        if (!$this->_order_items_table) {
            $this->_order_items_table = $this->_getResource()->getTableName('sales_flat_order_item');
        }
        return $this->_order_items_table;
    }

    protected function _getProductsTable()
    {
        if (!$this->_products_table) {
            $this->_products_table = $this->_getResource()->getTableName('catalog_product_entity');
        }
        return $this->_products_table;
    }

    public function getCollectionCount($storeId = false)
    {
        $sql = 'select count(*) as c from ' . $this->_getOrdersTable() . ' as o '
            . 'left join ' . $this->_getOrderItemsTable() . ' as i '
            . 'on(i.order_id = o.entity_id and i.parent_item_id is null) '
        ;
		if($storeId) {
			$storeId = (int)$storeId;
			$sql .= 'where o.store_id = ' . $storeId;
		}

        $q = $this->_getDB()->fetchRow($sql);
        return $q['c'];
    }

    public function getAll($page_num = 1, $page_size = 10, $storeId = false)
    {
        $sql = 'select o.increment_id, o.created_at, o.customer_id, i.product_id, i.sku, i.qty_ordered, i.price_incl_tax, i.product_type, p.sku as real_sku from ' . $this->_getOrdersTable() . ' as o '
            . 'left join ' . $this->_getOrderItemsTable() . ' as i on(i.order_id = o.entity_id and i.parent_item_id is null) '
            . 'left join ' . $this->_getProductsTable() . ' as p on(i.product_id = p.entity_id)';
			
		if($storeId) {
			$storeId = (int)$storeId;
			$sql .= ' where o.store_id = ' . $storeId;
		}
			
		$sql . ' order by i.item_id limit ' . (($page_num - 1) * $page_size) . ', ' . $page_size;
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

    public function getFilePath()
    {
        return 'var/cache/quartic_order_feed.xml';
    }

    /**
     * Send event "order history is ready" to quartic
     * @param bool|int $storeId
     */
    public function sendTransacionsEvent($storeId = false) {
        $helper = Mage::helper('quartic');
        $api = Mage::getModel('quartic/client_api');
        if (!$storeId) {
            $storeId = 0;
        }
        $helper->log('sendTransacionsEvent, storeId: '.$storeId);
        if ($helper->getStoreScopeData($storeId, 'orders')) {
            $this->log('Orders history already sent: '.$storeId);
            return;
        }

        $data = array(
            'url' => Mage::getUrl('quartic/feed/orders', array('store' => $storeId)),
        );
        try {
            $result = $api->post('transactions', array('data' => $data));
        } catch (Exception $e) {
            $helper->log('Problem during sending transaction history for storeId: '.$storeId);
        }
        $helper->setStoreScopeData($storeId, 'orders', true, true);
    }
}
