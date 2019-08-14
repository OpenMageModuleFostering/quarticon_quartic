<?php

class Quarticon_Quartic_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_debug;

    protected $preparedClientData = array();

    /**
     * Output log to file
     * @param string $content
     * @param string $filename
     */
    public function log($content = '', $filename = 'quartic.log')
    {
        if ($this->getDebug()) {
            Mage::log($content, null, $filename);
        }
    }

    /**
     * Get config object
     * @return Quarticon_Quartic_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }

    /**
     * Is debug enabled?
     * @return bool
     */
    protected function getDebug()
    {
        if (is_null($this->_debug)) {
            $this->_debug = $this->getConfig()->isDebug();
        }
        return $this->_debug;
    }

    /**
     * Gets product SKU or ID based on module configuration.
     *
     * @param  array|object $item
     * @return string
     */
    public function getProduct($item)
    {
        if ($item) {
            $use_sku = $this->getConfig()->isUsingSkuEnabled();
            $product = $use_sku ?
                (
                is_array($item) ? ($item['real_sku'] ? $item['real_sku'] : $item['sku']) : $this->_getRealSku($item)
                ) :
                (
                is_array($item) ? (isset($item['product_id']) ? $item['product_id'] : $item['id']) :
                    ($item->getProductId() ? $item->getProductId() : $item->getId())
                );
                return $product;
        }
        return false;
    }

    /**
     * Gets real SKU of item, ie. parent item's SKU
     *
     * @param  array|object $item
     * @return string
     */
    protected function _getRealSku($item)
    {
        if ($item->getProductType() === 'configurable') {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $sku = $product->getSku();
            $product->clearInstance();
            unset($product);
            return $sku;
        } else {
            return $item->getSku();
        }
    }

    /**
     * Get link to quartic admin page
     * @return string
     */
    public function getAdminPageLink() {
        $iframeLink = Mage::getStoreConfig('quartic/config/iframe_link');
        return $iframeLink.'?'.$this->getAuthQuery();
    }

    /**
     * Get store name hash for given scope
     * @return string
     */
    private function getStoreNameHash() {
        $scope = $this->getScopeDetails();
        $name = Mage::getStoreConfig('quartic/config/storeName');
        if ($scope['scope'] != 'default') {
            $name .= '_'.$scope['scopeId'];
        }
        return $name;
    }

    /**
     * Get plugin authentication url query
     * @return string
     */
    public function getAuthQuery() {
        $version = Mage::getConfig()->getModuleConfig("Quarticon_Quartic")->version;
        $secret = Mage::getStoreConfig('quartic/config/secret');
        $storeName = $this->getStoreNameHash();
        $key = time();
        $hash = hash_hmac('sha512', $secret, $key);
        $params = array(
            'storeName' => $storeName,
            'hash' => $hash,
            'key' => $key,
            'ver' => reset($version),
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
            'storeConfig' => base64_encode(json_encode(array(
                'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'country' => Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId())
            )))
        );
        return http_build_query($params);
    }

    /**
     * Send upgrade request for every initialized shopview
     */
    public function sendUpgradeEvent() {
        $secret = Mage::getStoreConfig('quartic/config/secret');
        $name = Mage::getStoreConfig('quartic/config/storeName');

        $ss = Mage::getStoreConfig("quartic/config/storeScopes");
        if ($ss) {
            $scopes = json_decode($ss, true);
            foreach ($scopes as $storeId => $data) {
                if (isset($data['shopInit']) && $data['shopInit']) {
                    $this->log('upgrade for store: '.$storeId);
                    $storeName = ($storeId == 0) ? $name : $name . '_' . $storeId;
                    Mage::helper('quartic')->sendEventStoreInit($storeName, $secret);
                }
            }
        }
    }

    /**
     * Store first init action
     * @param $name store name identifier
     * @param $secret store secret
     */
    public function sendEventStoreInit($name, $secret) {
        $this->log('sendEventStoreInit');
        $eventName = 'store.init';
        $params = array(
            'storeName' => $name,
            'secret' => $secret,
            'storeUrl' => Mage::getStoreConfig('web/secure/base_url'),
            'pluginVersion' => reset(Mage::getConfig()->getModuleConfig("Quarticon_Quartic")->version),
            'storeVersion' => Mage::getVersion()
        );

        $this->sendEvent($eventName, $params);
    }

    /**
     * Send event to quartic webhook
     * @param $eventName event name
     * @param $params data
     */
    private function sendEvent($eventName, $params) {
        $this->log('sendEvent');
        $url = Mage::getStoreConfig('quartic/config/webhook_link');
        $curl = Mage::getModel('quartic/client_resource_curl');
        $curl->addHeader('x-webhook-name', $eventName);
        $curl->post('', $params, $url);
    }

    /**
     * Set data for given scope
     * @param $scopeId
     * @param $key
     * @param $val
     * @param $reset
     */
    public function setStoreScopeData($scopeId, $key, $val, $reset = false) {
        $data = array();
        $ss = Mage::getStoreConfig("quartic/config/storeScopes");
        if ($ss) {
            $data = json_decode($ss, true);
        }
        if (!isset($data[$scopeId])) {
            $data[$scopeId] = array();
        }
        $data[$scopeId][$key] = $val;
        $this->setStoreConfig('quartic/config/storeScopes', json_encode($data), true);
        if ($reset) {
            Mage::app()->getStore()->resetConfig();
        }
    }

    /**
     * Get data from scope
     * @param $scopeId
     * @param $key
     * @return null
     */
    public function getStoreScopeData($scopeId, $key) {
        $ss = Mage::getStoreConfig("quartic/config/storeScopes");
        if (!$ss) {
            return null;
        }
        $data =  json_decode($ss, true);
        if (isset($data[$scopeId][$key])) {
            return $data[$scopeId][$key];
        }
        return null;
    }

    /**
     * Init quartic shop for current scope in Quartic
     */
    public function initQuarticShop() {
        $scope = $this->getScopeDetails();
        if ($this->getStoreScopeData($scope['scopeId'], 'shopInit')) {
                $this->log('Shop already initialized for scope: '.$scope['scopeId']);
                return;
            }
        if ($scope['scope'] != 'default') {
            $storeName = $this->getStoreNameHash();
            $this->sendEventStoreInit($storeName, $secret = Mage::getStoreConfig('quartic/config/secret'));
            $this->setStoreConfig('quartic/config/storeName', $storeName);
        }
        $this->setStoreScopeData($scope['scopeId'], 'shopInit', true);
    }

    /**
     * If not set already tries to retrieve customer data from quartic
     * @return bool true if customer data retreived
     */
    public function prepareCustomerData() {
        $scope = $this->getScopeDetails();
        if ($this->getStoreScopeData($scope['scopeId'], 'custData')) {
            $this->log('Customers data already retrieved: '.$scope['scopeId']);
            return;
        }
        $this->log('prepareCustomerData for scope: '.json_encode($scope));

        $url = Mage::getStoreConfig('quartic/config/clientData_link') . '?' . $this->getAuthQuery();
        $curl = Mage::getModel('quartic/client_resource_curl');
        $result = $curl->get('', array(), $url);

        if (is_array($result['body']) && isset($result['body']['found']) && $result['body']['found']) {
            $this->log('store customer data in config: '.json_encode($result['body']));
            $this->setStoreConfig('quartic/config/api_key', $result['body']['api_key']);
            $this->setStoreConfig('quartic/config/customer', $result['body']['symbol']);
            $this->setStoreConfig('quartic/config/status', 1, true);
            $this->setStoreConfig('quartic/config/active', 1);

            // flush config
            Mage::app()->getStore()->resetConfig();

            // order history api call
            $_order = Mage::getModel('quartic/order');
            $_order->sendTransacionsEvent($scope['scopeId']);
            
            // set catalog link api call
            $feed = Mage::getModel('quartic/feed');
            $feed->sendCatalogEvent($scope['scopeId']);

            $this->setStoreScopeData($scope['scopeId'], 'custData', true);
            return true;
        }
        return false;
    }

    /**
     * Stores config in databases
     * @param $key
     * @param $val
     * @param bool $default save to default scope?
     */
    public function setStoreConfig($key, $val, $default = false) {
        $scope = $this->getScopeDetails();
        if ($default) {
            $scope['scope'] = 'default';
            $scope['scopeId'] = 0;
        }
        Mage::getModel('core/config')->saveConfig($key, $val, $scope['scope'], $scope['scopeId']);
    }

    /**
     * Get scope and scope id
     * @return array
     */
    public function getScopeDetails() {
        $website = Mage::app()
            ->getWebsite(true);
        $defaultStoreId = 0;
        if ($website) {
            $defaultStoreId = $website
                ->getDefaultGroup()
                ->getDefaultStoreId();
        }

        $params = Mage::app()->getRequest()->getParams();
        if (isset($params['store']) && !empty($params['store']) && is_numeric($params['store'])) {
            if ($params['store'] != $defaultStoreId) {
                return array('scope' => 'stores', 'scopeId' => $params['store']);
            }
        } elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) {
            $store_id = Mage::getModel('core/store')->load($code)->getId();
            if ($store_id != $defaultStoreId) {
                return array('scope' => 'stores', 'scopeId' => $store_id);
            }
        } elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) {
            $website_id = Mage::getModel('core/website')->load($code)->getId();
            $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
            if ($store_id != $defaultStoreId) {
                return array('scope' => 'websites', 'scopeId' => $store_id);
            }
        }
        return array('scope' => 'default', 'scopeId' => 0);
    }

    /**
     * Get store id from request
     * @return int
     */
    public function getStoreId() {
        $scope = $this->getScopeDetails();
        return $scope['scopeId'];
    }
}
