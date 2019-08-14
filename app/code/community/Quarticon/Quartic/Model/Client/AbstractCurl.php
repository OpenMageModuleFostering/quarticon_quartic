<?php

abstract class Quarticon_Quartic_Model_Client_AbstractCurl
{

    /**
     * @var Quarticon_Quartic_Model_Client_Resource_Curl
     */
    protected $_client;

    /**
     * @var string
     */
    protected $_token;

    /**
     * @var array
     */
    protected static $_counter = array();

    /**
     * @return Quarticon_Quartic_Model_Client_Resource_Curl
     */
    public function getClient()
    {
        if (!$this->_client) {
            $this->_client = Mage::getModel('quartic/client_resource_curl');
        }
        return $this->_client;
    }

    /**
     *
     * @param string $resource
     * @return array
     */
    public function get($resource)
    {
        return $this->getClient()->get($resource);
    }

    /**
     *
     * @param string $resource
     * @param array $params
     * @return array
     */
    public function post($resource, $params = array())
    {
        return $this->getClient()->post($resource, $params);
    }

    /**
     *
     * @return string
     */
    protected function getToken()
    {
        $storeCode = Mage::app()->getRequest()->getParam('store');
        $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
        if (is_null($this->_token)) {
            $temp = Mage::getSingleton('core/session')->getQuarticApiToken();
            $this->_token = isset($temp[$storeId]) ? $temp[$storeId] : null;
        }
        if (!is_null($this->_token)) {
            $temp2 = Mage::getSingleton('core/session')->getQuarticApiTokenExpires();
            $end = $temp2[$storeId];
            if ($end < Mage::getModel('core/date')->timestamp()) {
                return null;
            }
        }
        return $this->_token;
    }

    /**
     * Request new token if session have expired
     *
     * @return string
     * @throws \Exception
     */
    protected function setToken($value, $end, $storeId)
    {
        $this->_token = $value;

        $token = Mage::getSingleton('core/session')->getQuarticApiToken();
        $token[$storeId] = $value;
        Mage::getSingleton('core/session')->setQuarticApiToken($token);
        
        $tokenExp = Mage::getSingleton('core/session')->setQuarticApiTokenExpires();
        $tokenExp[$storeId] = $end;
        Mage::getSingleton('core/session')->setQuarticApiTokenExpires($token);
    }

    /**
     *
     */
    protected function requestToken()
    {
        return null;
    }
}
