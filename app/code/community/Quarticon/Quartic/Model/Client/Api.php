<?php

class Quarticon_Quartic_Model_Client_Api extends Quarticon_Quartic_Model_Client_AbstractCurl
{

    protected $_user_symbol;
    protected $_user_key;

    /**
     * GET resource from API
     *
     * @param string $resource
     * @return array
     */
    public function get($resource,$storeId = false)
    {
        $token = $this->getToken();
        if (empty($token)) {
            $token = $this->requestToken($storeId);
        }
        $ret = $this->getClient()->get($resource . '?token=' . $token);
        if (empty($ret)) {
            throw new Exception('Quartic Api returned empty result.');
        }
        if (!isset($ret['body']['status']) || strtolower($ret['body']['status']) != 'ok') {
            Mage::log(var_export($ret, true), null, 'quartic.log');
            if (isset($ret['body']['data']['error_code'])) {
                throw new Exception('Quartic Api returned error: ' . $ret['body']['data']['error_message']);
            } else {
                throw new Exception('Quartic Api returned error: ' . print_r($ret['body'], true));
            }
        }
        return $ret;
    }

    /**
     * POST new resource into API
     *
     * @param string $resource
     * @param string $id
     * @param mixed $data
     * @return array
     */
    public function post($resource, $data = array())
    {
        $token = $this->getToken();
        if (empty($token)) {
            $token = $this->requestToken();
        }
        $data['token'] = $token;
        $ret = $this->getClient()->post("{$resource}", $data);
        //$ret = $this->getClient()->post("{$resource}?token=" . $token, $data);
        if (empty($ret)) {
            throw new Exception('Quartic Api returned empty result.');
        }
        if (!isset($ret['body']['status']) || strtolower($ret['body']['status']) != 'ok') {
            Mage::log(var_export("{$resource}", true), null, 'quartic.log');
            Mage::log(var_export($data, true), null, 'quartic.log');
            Mage::log(var_export($ret, true), null, 'quartic.log');
            if (isset($ret['body']['data']['error_code'])) {
                throw new Exception('Quartic Api returned error: ' . $ret['body']['data']['error_message']);
            } else {
                throw new Exception('Quartic Api returned error: ' . print_r($ret['body'], true));
            }
        }
        return $ret;
    }

    /**
     * PUT updated resource into API
     *
     * @param string $resource
     * @param string $id
     * @param mixed $data
     * @return array
     */
    public function put($resource, $id, $data = array())
    {
        $token = $this->getToken();
        if (empty($token)) {
            $token = $this->requestToken();
        }
        $data['token'] = $token;
        $ret = $this->getClient()->put("{$resource}/{$id}", $data);
        //$ret = $this->getClient()->put("{$resource}/{$id}?token=" . $token, $data);
        if (empty($ret)) {
            throw new Exception('Quartic Api returned empty result.');
        }
        if (!isset($ret['body']['status']) || strtolower($ret['body']['status']) != 'ok') {
            Mage::log(var_export("{$resource}/{$id}", true), null, 'quartic.log');
            Mage::log(var_export($data, true), null, 'quartic.log');
            Mage::log(var_export($ret, true), null, 'quartic.log');
            if (isset($ret['body']['data']['error_code'])) {
                throw new Exception('Quartic Api returned error: ' . $ret['body']['data']['error_message']);
            } else {
                throw new Exception('Quartic Api returned error: ' . print_r($ret['body'], true));
            }
        }
        return $ret;
    }

    /**
     * Request new token if session have expired
     *
     * @return string
     * @throws \Exception
     */
    public function requestToken($storeId = false)
    {		
		// TODO: przenieść pobieranie storeId do helpera
		$params = Mage::app()->getRequest()->getParams();
		if(isset($params['store'])) {
			$storeId = (is_numeric($params['store'])) ? (int)$params['store'] : Mage::getModel('core/store')->load($params['store'], 'code')->getId();
		} elseif(isset($params['website'])) {
			$website = (is_numeric($params['website'])) ? Mage::getModel('core/website')->load($params['website']) : Mage::getModel('core/website')->load($params['website'], 'code');
			$storeId = $website->getDefaultGroup()->getDefaultStoreId();
		} else {
			$storeId = Mage::app()->getStore()->getId();
		}
		if($storeId == 0) $storeId = Mage::app()->getDefaultStoreView()->getStoreId();
		

        $symbol = Mage::getStoreConfig("quartic/config/customer", $storeId);
        $key = Mage::getStoreConfig("quartic/config/api_key", $storeId);

        $ret = $this->getClient()
            ->post('login', array(
            'symbol' => $symbol,
            'key' => $key,
            ));
        if (isset($ret['body']['data']['token'])) {
            /**
             * TODO: wymyśl coś z datą, którą dostajemy z api
             */
            //$end = Mage::getModel('core/date')->timestamp($ret['body']['data']['end_date']);
            $end = Mage::getModel('core/date')->timestamp('+5 minutes');
            $this->setToken($ret['body']['data']['token'], $end, $storeId);
            return $ret['body']['data']['token'];
        } else {
            throw new \Exception('Could not get token.');
        }
    }

    /**
     * Remove token to force re-login on ext request
     */
    public function dropToken($storeId)
    {
        $end = Mage::getModel('core/date')->timestamp('-1 year');
        $this->setToken(null, $end,$storeId);
    }
    
    public function catalogType()
    {
        return 4;
    }
}
