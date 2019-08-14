<?php

class Quarticon_Quartic_Model_Observer_Adminhtml
{

    /**
     * Akcja wykonywana przed zaladowaniem kazdej strony backendu
     * @param Varien_Event_Observer $observer
     */
    public function adminPageBefore(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getRequest()->getControllerName() == 'system_config') {
            $params = Mage::app()->getRequest()->getParams();
            if (isset($params['section']) && in_array($params['section'], array('quartic', 'plugin'))) {
                $helper = Mage::helper('quartic');

                // jesli brakuje sprobuj utworzyc shop
                $helper->initQuarticShop();

                // sprawdz czy jest powiazanie z customerem
                $helper->prepareCustomerData();
            }
        }
    }

    /**
     * Verify api keys entered by user
     * Run after configuration is saved
     *
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Exception
     */
    public function handle_adminSystemConfigChangedSection(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('quartic');
        /* @var $api Mage_Core_Model_Config */
        $config = Mage::getModel('core/config');
        /* @var $api Quarticon_Quartic_Model_Client_Api */
        $api = Mage::getModel('quartic/client_api');
		$currentArea = 'default';
		$currentAreaId = 0;
		if($observer->getStore()) {
			$storeId = Mage::getModel('core/store')->load($observer->getStore(), 'code')->getId();
			$currentArea = 'stores';
			$currentAreaId = $storeId;
		} elseif($observer->getWebsite()) {
			$websiteId = Mage::getModel('core/website')->load($observer->getWebsite(), 'code')->getId();
			$storeId = Mage::app()->getWebsite($websiteId)->getDefaultGroup()->getDefaultStoreId();
			$currentArea = 'websites';
			$currentAreaId = $websiteId;
		} else {
			$storeId = Mage::app()->getDefaultStoreView()->getStoreId();
		}
        /**
         * Clean products feed cache
         */
        $cache = Mage::app()->getCacheInstance();
        $cache->cleanType('quartic-products-' . $storeId);

        /**
         * Verify api connection
         */
		if($currentArea == 'websites') {
			$status_array = Mage::app()->getWebsite($currentAreaId)->getConfig('quartic/config');
		} else {
			$status_array = Mage::getStoreConfig("quartic/config", $observer->getStore());
		}

        $status = (
            !empty($status_array['active']) &&
            !empty($status_array['customer']) &&
            !empty($status_array['hash'])
            );

        /**
         * Only if fields are filled
         */
        if ($status) {
            try {
                /**
                 * Force fresh login
                 */
                $api->dropToken($observer->getStore());
                $ret = $api->requestToken();
            } catch (Exception $e) {
                /**
                 * If login failed, return error
                 */
				 
                $config->saveConfig('quartic/config/status', 0, $currentArea, $currentAreaId);
                Mage::app()->getCacheInstance()->cleanType('config');
                throw new Mage_Core_Exception(Mage::helper('quartic')->__('Could not log in. Check your credentials and try again.'));
            }
        }

        /**
         * Set module status based on config and result
         */
        $config->saveConfig('quartic/config/status', (int) $status, $currentArea, $currentAreaId);
        $cache->cleanType('config');

        if ($status) {
            $cleared = $this->markRegistered($observer, $status_array['customer'], $currentArea, $currentAreaId);
        } else {
            $cleared = false;
        }

        /**
         * Registration
         */
        if ($status) {
            $registered = Mage::getStoreConfig("quartic/config/registered/c_{$status_array['customer']}", $observer->getStore());
            if (empty($registered)) {
                try {
                    $helper->log('POST pluginActivation');
                    $data = array(
                        'platform' => 56 //hardcoded magento platform code
                    );
                    $helper->log(var_export(array('data' => $data), true));
                    $helper->log(var_export($api->post('pluginActivation', array('data' => $data)), true));
                    $config->saveConfig("quartic/config/registered/c_{$status_array['customer']}", 1, $currentArea, $currentAreaId);
                    Mage::app()->getCacheInstance()->cleanType('config');
                } catch (Exception $e) {
                    $helper->log("Quartic activation failed: " . $e->getMessage());
                }
            }
        }
        /**
         * Catalogs
         */
        if ($status && !$cleared) {
            $catalog_id = isset($status_array['catalog_id']) ? (int) $status_array['catalog_id'] : 0;
            if ($catalog_id > 0) {
                /**
                 * Synchronizuj katalog
                 */
                $cache->cleanType('quartic-products-' . $storeId);
                $data = array();
                $data['url'] = Mage::getUrl('quartic/feed/products', array('hash' => $status_array['hash'],'store' => $storeId,'_nosid' => true,'_store' => $storeId,'_type' =>'direct_link'));
                $helper->log('PUT catalog');
                $helper->log(var_export(array('data' => $data), true));
                $helper->log(var_export($api->put('catalogs', $catalog_id, array('data' => $data)), true));
            } elseif ($catalog_id == -1) {
                /**
                 * Utwórz nowy
                 */
                $cache->cleanType('quartic-products-' . $storeId);
                $data = array(
                    'url' => Mage::getUrl('quartic/feed/products', array('hash' => $status_array['hash'],'store' => $storeId,'_nosid' => true,'_store' => $storeId,'_type' =>'direct_link')),
                    'name' => 'magento_' . time(),
                    'typeId' => $api->catalogType(),
                );
                $helper->log('POST catalog');
                $helper->log(var_export(array('data' => $data), true));
                $new_catalog = $api->post('catalogs', array('data' => $data));
                $helper->log(var_export($new_catalog, true));
                $retData = current($new_catalog['body']['data']);
                $config->saveConfig('quartic/config/catalog_id', $retData['id'], $currentArea, $currentAreaId);
                $cache->cleanType('config');
            }
        }
    }

    /**
     * 
     * @param Varien_Event_Observer $observer
     * @param string $qcustomer QON account login
     * @return boolean True if data was cleared
     */
    public function markRegistered(Varien_Event_Observer $observer, $qcustomer, $currentArea, $currentAreaId)
    {
		if($currentArea == 'websites') {
			$registered = Mage::app()->getWebsite($currentAreaId)->getConfig('quartic/config/registered/current');
		} else {
			$registered = Mage::getStoreConfig("quartic/config/registered/current", $currentAreaId);
		}
        $cache = Mage::app()->getCacheInstance();
        /* @var $api Mage_Core_Model_Config */
        $config = Mage::getModel('core/config');
		
        /**
         * First login - save id
         */
        if (empty($registered)) {
            $config->saveConfig('quartic/config/registered/current', $qcustomer, $currentArea, $currentAreaId);
            return false;
        }
        /*
         * Changed accounts - clean previous data
         */
        if ($registered !== $qcustomer) {
            /**
             * Remove frames
             */
            /*
            $frames = Mage::getModel('quartic/placement');
            $collection = $frames->getCollection();
            $collection->addFieldToFilter('api_name', $qcustomer);
            foreach ($collection as $item) {
                $item->delete();
            }*/

            $config->deleteConfig('quartic/config/modified_placements', $qcustomer);
            $config->deleteConfig('quartic/config/catalog_id', $qcustomer);
            $config->deleteConfig('quartic/frames_homepage', $qcustomer);
            $config->deleteConfig('quartic/frames_category', $qcustomer);
            $config->deleteConfig('quartic/frames_product', $qcustomer);
            $config->deleteConfig('quartic/frames_cart', $qcustomer);

            $config->saveConfig('quartic/config/registered/current', $qcustomer, $currentArea, $currentAreaId);

            $cache->cleanType('config');
            return true;
        }
    }
}
