<?php

class Quarticon_Quartic_Model_Client_Resource_Curl extends Mage_HTTP_Client_Curl
{

    const CURL_API_URL = 'https://api.quarticon.com/api/v1/';
    const CURL_API_INPUT = 'application/json';
    const CURL_API_OUTPUT = 'json';

    /**
     * Make GET request
     *
     * @param string $uri uri relative to host, ex. "/index.php"
     */
    public function get($uri, $params=array(), $url = null)
    {
        $this->makeRequest("GET", $uri, $params, $url);
        return $this->returnResponse();
    }

    /**
     * Make POST request
     * @see lib/Mage/HTTP/Mage_HTTP_Client#post($uri, $params)
     */
    public function post($uri, $params, $url = null)
    {
        $this->makeRequest("POST", $uri, $params, $url);
        return $this->returnResponse();
    }

    /**
     * Make POST request
     * @see lib/Mage/HTTP/Mage_HTTP_Client#post($uri, $params)
     */
    public function put($uri, $params)
    {
        $this->makeRequest("PUT", $uri, $params);
        return $this->returnResponse();
    }

    /**
     *
     * @return array
     */
    protected function returnResponse()
    {
        $ret = array(
            'status' => $this->getStatus(),
            'headers' => $this->getHeaders(),
        );

        if (isset($ret['headers']['Content-Type'])) {
            $sep = strpos($ret['headers']['Content-Type'], ';');
            if ($sep !== false) {
                $contentType = trim(substr($ret['headers']['Content-Type'], 0, $sep));
            } else {
                $contentType = trim($ret['headers']['Content-Type']);
            }
        } else {
            $contentType = self::CURL_API_OUTPUT;
        }
        $ret['type'] = $contentType;

        $response = $this->getBody();

        if ($contentType == 'application/json') {
            $ret['body'] = json_decode($response, true);
        } else {
            $ret['body'] = $response;
        }
        return $ret;
    }

    /**
     * Make request
     * Rewrite parent, for "PUT" requests
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param string $url (optional) base url
     * @return null
     */
    protected function makeRequest($method, $uri, $params = array(), $url = null)
    {
        if (!$url) {
            $url = self::CURL_API_URL;
        }
        $uri = $url . $uri;
        Mage::log($method . ' ' . $uri, null, 'quartic.log');
        $this->_ch = curl_init();
        $this->curlOption(CURLOPT_URL, $uri);
        $postfields = null;
        $postfile = null;
        if ($method == 'POST') {
            $this->curlOption(CURLOPT_POST, 1);
            //$postfields = http_build_query($params);
            $postfields = json_encode($params);
            $this->_headers['Content-Type'] = 'application/json';
        } elseif ($method == 'PUT') {
            $this->curlOption(CURLOPT_PUT, 1);
            $postfile = json_encode($params);
            $this->_headers['Content-Type'] = 'application/json';
        } elseif ($method == "GET") {
            $this->curlOption(CURLOPT_HTTPGET, 1);
        } else {
            $this->curlOption(CURLOPT_CUSTOMREQUEST, $method);
        }
        if (!is_null($postfields)) {
            $this->curlOption(CURLOPT_POSTFIELDS, $postfields);
        }
        if (!is_null($postfile)) {
            $fp = fopen('php://temp/maxmemory:256000', 'w');

            if (!$fp) {
                throw new \Exception('Could not open temp memory data');
            }

            fwrite($fp, $postfile);
            fseek($fp, 0);

            $this->curlOption(CURLOPT_BINARYTRANSFER, true);
            $this->curlOption(CURLOPT_INFILE, $fp); // file pointer
            $this->curlOption(CURLOPT_INFILESIZE, strlen($postfile));
        }

        //var_dump($this->_headers);
        if (count($this->_headers)) {
            $heads = array();
            foreach ($this->_headers as $k => $v) {
                $heads[] = $k . ': ' . $v;
            }
            $this->curlOption(CURLOPT_HTTPHEADER, $heads);
        }

        if (count($this->_cookies)) {
            $cookies = array();
            foreach ($this->_cookies as $k => $v) {
                $cookies[] = "$k=$v";
            }
            $this->curlOption(CURLOPT_COOKIE, implode(";", $cookies));
        }

        if ($this->_timeout) {
            $this->curlOption(CURLOPT_TIMEOUT, $this->_timeout);
        }

        if ($this->_port != 80) {
            $this->curlOption(CURLOPT_PORT, $this->_port);
        }

        $this->curlOption(CURLINFO_HEADER_OUT, 1);

        //$this->curlOption(CURLOPT_HEADER, 1);
        $this->curlOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curlOption(CURLOPT_HEADERFUNCTION, array($this, 'parseHeaders'));


        if (count($this->_curlUserOptions)) {
            foreach ($this->_curlUserOptions as $k => $v) {
                $this->curlOption($k, $v);
            }
        }

        $this->_headerCount = 0;
        $this->_responseHeaders = array();
        $this->_responseBody = curl_exec($this->_ch);
        $err = curl_errno($this->_ch);
        if ($err) {
            $this->doError(curl_error($this->_ch));
        }
        //var_dump($postfields);
        //var_dump(curl_getinfo($this->_ch));
        curl_close($this->_ch);
    }

    /**
     * Set headers from hash

     * @param array $headers
     */
    public function setHeaders($headers)
    {
        parent::setHeaders($headers);
        return $this;
    }

    /**
     * Add header
     *
     * @param $name name, ex. "Location"
     * @param $value value ex. "http://google.com"
     */
    public function addHeader($name, $value)
    {
        parent::addHeader($name, $value);
        return $this;
    }

    /**
     * Remove specified header
     *
     * @param string $name
     */
    public function removeHeader($name)
    {
        parent::removeHeader($name);
        return $this;
    }
}
