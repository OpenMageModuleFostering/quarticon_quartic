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
    public function apiLoad($storeId)
    {
        try {
            /* @var $api Quarticon_Quartic_Model_Client_Api */
            $api = Mage::getModel('quartic/client_api');
            
            $ret = $api->get('placements',$storeId);
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        return $this->apiLoaded($ret['body']['data'], $storeId);
    }

    /**
     * Insert or update placements returned from API
     *
     * @param array $data Decoded from json
     * @return array Ids of placements: qon_id=>magento_id
     */
    protected function apiLoaded($data = array(), $storeId)
    {
        $ret = array();
        foreach ($data as $placement) {
            try {
                $ret[$placement['id']] = $this->saveReplace($placement, $storeId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Mage::getModel('core/config')->saveConfig('quartic/config/modified_placements',
                Mage::getModel('core/date')->timestamp(), 'default', 0);
        Mage::app()->getCacheInstance()->cleanType('config');
        return $ret;
    }

    /**
     * FIXME: Updates
     * @param type $placement
     * @return type
     */
    protected function saveReplace($placement, $storeId)
    {
        $apiName = Mage::getStoreConfig("quartic/config/customer", $storeId);
        $check = $this->getCollection()
                ->addFieldToFilter('api_name', $apiName)
                ->addFieldToFilter('qon_id', $placement['id'])
                ->getFirstItem();
        
        $entity = $this;
        $entity
            ->setData(array(
                'id' => $check->getId(),
                'api_name' => $apiName,
                'qon_id' => $placement['id'],
                'qon_parent_id' => $placement['parent_id'],
                'parent_name' => $placement['parent_name'],
                'name' => $placement['name'],
                'div_id' => $placement['div_id'],
                'snippet' => $placement['snippet'],
            ));

            $entity->save();
 
        $entity->save();
        return $entity->getId();
    }
	
	public function getStoreId($allowAdmin = true)
	{
		$params = Mage::app()->getRequest()->getParams();
		if(isset($params['store'])) {
			$storeId = (is_numeric($params['store'])) ? (int)$params['store'] : Mage::getModel('core/store')->load($params['store'], 'code')->getId();
		} elseif(isset($params['website'])) {
			$website = (is_numeric($params['website'])) ? Mage::getModel('core/website')->load($params['website']) : Mage::getModel('core/website')->load($params['website'], 'code');
			$storeId = $website->getDefaultGroup()->getDefaultStoreId();
		} else {
			$storeId = Mage::app()->getStore()->getId();
		}
		if($storeId == 0 && !$allowAdmin) $storeId = Mage::app()->getDefaultStoreView()->getStoreId();
		
		return $storeId;
	}
	
	public function deleteAll($apiName = false)
	{
        $collection = $this->getCollection();
		if($apiName) {
			$collection->addFieldToFilter('api_name', $apiName);
		}
		
        try {
			foreach ($collection as $item) {
				$item->delete();
			}
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
		
		return true;
	}
}
