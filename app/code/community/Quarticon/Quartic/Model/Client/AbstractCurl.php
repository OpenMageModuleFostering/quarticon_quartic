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
        if (is_null($this->_token)) {
            $this->_token = Mage::getSingleton('core/session')->getQuarticApiToken();
        }
        if (!is_null($this->_token)) {
            $end = Mage::getSingleton('core/session')->getQuarticApiTokenExpires();
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
    protected function setToken($value, $end)
    {
        $this->_token = $value;
        Mage::getSingleton('core/session')->setQuarticApiToken($value);
        Mage::getSingleton('core/session')->setQuarticApiTokenExpires($end);
    }

    /**
     *
     */
    protected function requestToken()
    {
        return null;
    }
}
