<?php

class Quarticon_Quartic_Model_Cron
{
	/**
	 * Refresh product feeds for all stores
	 */
	public function refreshProductsFeed()
	{
		$stores = $this->getStoresFeedEnabled();
		$feed = Mage::getModel('quartic/feed');
		foreach($stores as $storeId) {
			$feed->refreshProductsFeed($storeId);
		}
	}

	/**
	 * Get possible stores
	 * @return array
	 */
	public function getStoresFeedEnabled()
	{
		$core_resource = Mage::getSingleton('core/resource');
		$tablename = $core_resource->getTableName('core_config_data');
		$connection = $core_resource->getConnection('core_read');
		$select = $connection->select()->from($tablename, array('scope', 'scope_id', 'path', 'value'));
		$select->where('path="quartic/config/active"');

		$result = array();
		$rowset = $connection->fetchAll($select);
		foreach($rowset as $row) {
			if($row['value'] == 1) {
				switch ($row['scope']) {
					case 'default':
						$result[] = Mage::app()->getDefaultStoreView()->getStoreId();
						break;
					case 'websites':
						$result[] = Mage::app()->getWebsite($row['scope_id'])->getDefaultGroup()->getDefaultStoreId();
						break;
                    default:
						$result[] = $row['scope_id'];
						break;
				}
			}
		}
		$result = array_unique($result);
		return $result;
	}

}