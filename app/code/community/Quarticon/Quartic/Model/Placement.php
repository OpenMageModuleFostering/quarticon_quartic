<?php

class Quarticon_Quartic_Model_Placement extends Mage_Core_Model_Abstract
{

    protected $_options;

    protected function _construct()
    {
        $this->_init('quartic/placement');
    }

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
            array_unshift($options, array('value' => '', 'label' => Mage::helper('quartic')->__('--No Frame--')));
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
        return $this->getCollection()
                ->loadData()
                ->toOptionArray(false);
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
            $ret = $api->get('placements');
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
        $ret = array();
        foreach ($data as $placement) {
            try {
                $ret[$placement['id']] = $this->saveReplace($placement);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Mage::getModel('core/config')->saveConfig('quartic/config/modified_placements', Mage::getModel('core/date')->timestamp(), 'default', 0);
        Mage::app()->getCacheInstance()->cleanType('config');
        return $ret;
    }

    /**
     * FIXME: Updates
     * @param type $placement
     * @return type
     */
    protected function saveReplace($placement)
    {
        $check = Mage::getModel('quartic/placement')->load($placement['id'], 'qon_id');
        $entity = $this
            ->load($placement['id'], 'qon_id');
        $entity
            ->setData(array(
                'id' => $check->getId(),
                'qon_id' => $placement['id'],
                'qon_parent_id' => $placement['parent_id'],
                'parent_name' => $placement['parent_name'],
                'name' => $placement['name'],
                'div_id' => $placement['div_id'],
                'snippet' => $placement['snippet'],
            ));
        $entity
            ->save();
        return $entity->getId();
    }
}
