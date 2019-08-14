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
    public function get($resource, $storeId = false)
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
            $this->getHelper()->log(var_export($ret, true));
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
            $this->getHelper()->log(var_export("{$resource}", true));
            $this->getHelper()->log(var_export($data, true));
            $this->getHelper()->log(var_export($ret, true));
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
            $this->getHelper()->log(var_export("{$resource}/{$id}", true));
            $this->getHelper()->log(var_export($data, true));
            $this->getHelper()->log(var_export($ret, true));
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
		if ($storeId === false) {
            $storeId = $this->getHelper()->getStoreId();
		}

        $symbol = Mage::getStoreConfig("quartic/config/customer", $storeId);
        $key = Mage::getStoreConfig("quartic/config/api_key", $storeId);

        $data = array(
            'symbol' => $symbol,
            'key' => $key,
        );
        $this->getHelper()->log('Login data: '.var_export($data, true));
        $ret = $this->getClient()
            ->post('login', $data);
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
        $this->setToken(null, $end, $storeId);
    }

    /**
     * Get catalog type
     * @param string $name catalog type name
     * @return int
     */
    public function catalogType($name = null)
    {
        $defaultId = 4;
        if ($name == null) {
            // default quartic xml - backward compatibility
            return $defaultId;
        }

        try {
            $result = $this->get('catalogTypes');
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }

        $types = $result['body']['data'];
        foreach ($types as $type) {
            if ($type['name'] == $name) {
                return $type['id'];
            }
        }
        return $defaultId;
    }
}
