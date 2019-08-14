<?php

class Quarticon_Quartic_Model_Catalog extends Mage_Core_Model_Abstract
{

    protected $_options;

    /**
     * Return options for select field in configuration
     *
     * @param bool $isMultiselect
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {		
        if (!$this->_options) {
            $this->_options = $this->prepareOptionArray();
        }

        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, array('value' => '-1', 'label' => Mage::helper('quartic')->__("--Create new--")));
            array_unshift($options, array('value' => '0', 'label' => Mage::helper('quartic')->__("--Don't sync--")));
        }

        return $options;
    }

    /**
     * Return collection for toOptionArray function
     *
     * @return array
     */
    protected function prepareOptionArray()
    {
		$storeCode = Mage::app()->getRequest()->getParam('store');
		$storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
        $cache = Mage::app()->getCacheInstance();
        $catalogs = $cache->load('quartic-catalogs-list-' . $storeId);
        if ($catalogs !== false) {
            return unserialize($catalogs);
        } else {
            return $this->apiLoad();
        }
    }

    /**
     * Label used in configuration form
     *
     * @return type
     */
    public function getLabel()
    {
        return "{$this->getParentName()} - {$this->getName()} ({$this->getDivId()})";
    }

    /**
     * Request placements from API
     *
     * @return array
     */
    public function apiLoad()
    {
        try {
            /* @var $api Quarticon_Quartic_Model_Client_Api */
            $api = Mage::getModel('quartic/client_api');
            $ret = $api->get('catalogs');
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        return $this->apiLoaded($ret['body']['data']);
    }

    /**
     * Insert or update placements returned from API
     *
     * @param array $data Decoded from json
     * @return array Ids of placements: qon_id=>magento_id
     */
    protected function apiLoaded($data = array())
    {
		$storeCode = Mage::app()->getRequest()->getParam('store');
		$storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
        $cache = Mage::app()->getCacheInstance();
        $cache->save(serialize($data), 'quartic-catalogs-' . $storeId, array(), 30 * 24 * 3600);
        $list = array();
        foreach ($data as $catalog) {
            $list[] = array(
                'value' => $catalog['id'],
                'label' => $catalog['name'],
            );
        }
        $cache->save(serialize($list), 'quartic-catalogs-list-' . $storeId, array(), 30 * 24 * 3600);
        return $list;
    }
}
