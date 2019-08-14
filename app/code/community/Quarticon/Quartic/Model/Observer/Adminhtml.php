<?php

class Quarticon_Quartic_Model_Observer_Adminhtml
{

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
        /**
         * Clean products feed cache
         */
        $cache = Mage::app()->getCacheInstance();
        $cache->cleanType('quartic-products');

        /**
         * Verify api connection
         */
        $status_array = Mage::getStoreConfig("quartic/config", $observer->getStore());

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
                $api->dropToken();
                $ret = $api->requestToken();
            } catch (Exception $e) {
                /**
                 * If login failed, return error
                 */
                $config->saveConfig('quartic/config/status', 0, 'default', 0);
                Mage::app()->getCacheInstance()->cleanType('config');
                throw new Mage_Core_Exception(Mage::helper('quartic')->__('Could not log in. Check your credentials and try again.'));
            }
        }

        /**
         * Set module status based on config and result
         */
        $config->saveConfig('quartic/config/status', (int) $status, 'default', 0);
        $cache->cleanType('config');

        if ($status) {
            $cleared = $this->markRegistered($observer, $status_array['customer']);
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
                    $config->saveConfig("quartic/config/registered/c_{$status_array['customer']}", 1, 'default', 0);
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
                $cache->cleanType('quartic-products');
                $data = array();
                $data['url'] = Mage::getUrl('quartic/feed/products', array('hash' => $status_array['hash']));
                $helper->log('PUT catalog');
                $helper->log(var_export(array('data' => $data), true));
                $helper->log(var_export($api->put('catalogs', $catalog_id, array('data' => $data)), true));
            } elseif ($catalog_id == -1) {
                /**
                 * Utwórz nowy
                 */
                $cache->cleanType('quartic-products');
                $data = array(
                    'url' => Mage::getUrl('quartic/feed/products', array('hash' => $status_array['hash'])),
                    'name' => 'magento_' . time(),
                    'typeId' => $api->catalogType(),
                );
                $helper->log('POST catalog');
                $helper->log(var_export(array('data' => $data), true));
                $new_catalog = $api->post('catalogs', array('data' => $data));
                $helper->log(var_export($new_catalog, true));
                $retData = current($new_catalog['body']['data']);
                $config->saveConfig('quartic/config/catalog_id', $retData['id'], 'default', 0);
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
    public function markRegistered(Varien_Event_Observer $observer, $qcustomer)
    {
        $registered = Mage::getStoreConfig("quartic/config/registered/current", $observer->getStore());
        $cache = Mage::app()->getCacheInstance();
        /* @var $api Mage_Core_Model_Config */
        $config = Mage::getModel('core/config');
        /**
         * First login - save id
         */
        if (empty($registered)) {
            $config->saveConfig('quartic/config/registered/current', $qcustomer);
            return false;
        }
        /*
         * Changed accounts - clean previous data
         */
        if ($registered !== $qcustomer) {
            /**
             * Remove frames
             */
            $frames = Mage::getModel('quartic/placement');
            $collection = $frames->getCollection();
            foreach ($collection as $item) {
                $item->delete();
            }

            $config->deleteConfig('quartic/config/modified_placements');
            $config->deleteConfig('quartic/config/catalog_id');
            $config->deleteConfig('quartic/frames_homepage');
            $config->deleteConfig('quartic/frames_category');
            $config->deleteConfig('quartic/frames_product');
            $config->deleteConfig('quartic/frames_cart');

            $config->saveConfig('quartic/config/registered/current', $qcustomer);

            $cache->cleanType('config');
            return true;
        }
    }
}
