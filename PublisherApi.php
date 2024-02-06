<?php

namespace PublisherApi;

class PublisherApi
{
    /** @var string */
    private $api_key = '{YOUR_API_KEY}';

    /** @var string */
    private $flow_hash = '{FLOW_HASH}';

    /** @var string */
    private $target_hash = '{TARGET_HASH}';

    /** @var string */
    private $country_code = '{COUNTRY_CODE}';

    /** @var string */
    private $name;

    /** @var string */
    private $first_name;

    /** @var string */
    private $last_name;

    /** @var string */
    private $phone;

    /** @var string */
    private $phone2;

    /** @var string */
    private $data1;

    /** @var string */
    private $data2 = '';

    /** @var string */
    private $data3 = '';

    /** @var string */
    private $data4 = '';

    /** @var string */
    private $clickid = '';

    /** @var string */
    private $fbclid = '';

    /** @var string */
    private $ttclid = '';

    /** @var string */
    private $fbpx = '';

    /** @var string */
    private $ttpx = '';

    /** @var string */
    private $browser_locale = '';

    /** @var string */
    private $address;

    /** @var string */
    private $state;

    /** @var string */
    private $city;

    /** @var string */
    private $zipcode;

    /** @var string */
    private $email;

    /** @var string */
    private $comment = '';

    /** @var string */
    private $size = '';

    /** @var string */
    private $quantity = '';

    /** @var string */
    private $password = '';

    /** @var string */
    private $language = '';

    /** @var string */
    private $tz_name = '';

    /** @var string */
    private $call_time_frame = '';

    /** @var string */
    private $call_language = '';

    /** @var string */
    private $messenger_code = '';

    /** @var string */
    private $sale_code = '';

    /** @var float */
    private $price = 0.0;

    /** @var string */
    private $log_file_path = '';

    /** @var bool */
    private $enable_write_log = false;

    /** @var array */
    private $curl_info = array();

    /**
     * Make new API lead.
     *
     * @param string $name
     * @param string $phone
     * @return mixed
     * @throws \Exception
     */
    public function makeOrder($name, $phone)
    {
        $this->setProperty('name', $name);
        $this->setProperty('phone', $phone);

        $ch = $this->getCh();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->getRequestParams()));

        $response = curl_exec($ch);
        $this->curl_info = curl_getinfo($ch);
        curl_close($ch);

        if ($this->enable_write_log) {
            $this->writeLog();
        }

        return $response;
    }

    private function getCh()
    {
        if (!extension_loaded('curl')) {
            throw new \Exception('cURL extension not found');
        }

        return curl_init('https://api.aff1.com/v3/lead.create');
    }

    public function getRequestParams()
    {
        $this->validateRequiredParameters();

        return array_merge($this->getFlowHashOrTargetHash(), $this->getOptionalParameters());
    }

    private function getFlowHashOrTargetHash()
    {
        if ($this->hasFlowHashBeenFilled()) {
            return array('flow_hash' => $this->flow_hash);
        }

        return array('target_hash' => $this->target_hash);
    }

    private function getOptionalParameters()
    {
        return array(
            'api_key' => $this->api_key,
            'country_code' => $this->country_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'phone' => $this->phone,
            'phone2' => $this->phone2,
            'data1' => $this->data1,
            'data2' => $this->data2,
            'data3' => $this->data3,
            'data4' => $this->data4,
            'clickid' => $this->clickid,
            'fbclid' => $this->fbclid,
            'ttclid' => $this->ttclid,
            'fbpx' => $this->fbpx,
            'ttpx' => $this->ttpx,
            'ip' => $this->getIp(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
            'browser_locale' => $this->browser_locale,
            'address' => $this->address,
            'state' => $this->state,
            'city' => $this->city,
            'zipcode' => $this->zipcode,
            'email' => $this->email,
            'comment' => $this->comment,
            'size' => $this->size,
            'quantity' => $this->quantity,
            'password' => $this->password,
            'language' => $this->language,
            'tz_name' => $this->tz_name,
            'call_time_frame' => $this->call_time_frame,
            'call_language' => $this->call_language,
            'messenger_code' => $this->messenger_code,
            'sale_code' => $this->sale_code,
            'price' => $this->price,
        );
    }

    private function validateRequiredParameters()
    {
        if ($this->api_key === '{YOUR_API_KEY}') {
            die('Parameter API_KEY is not set.');
        }

        if (!$this->hasFlowHashBeenFilled() && !$this->hasTargetHashBeenFilled()) {
            die('Either parameter TARGET_HASH or FLOW_HASH must be set.');
        }
    }

    private function hasFlowHashBeenFilled()
    {
        return $this->flow_hash && $this->flow_hash !== '{FLOW_HASH}';
    }

    private function hasTargetHashBeenFilled()
    {
        return $this->target_hash && $this->target_hash !== '{TARGET_HASH}';
    }

    public function getIp()
    {
        if ($this->issetXForwarderForIp() && $this->isValidXForwarderForIp()) {
            return $this->getXForwarderForIp();
        }

        if (isset($_SERVER['HTTP_CLIENTIP']) && !empty($_SERVER['HTTP_CLIENTIP'])) {
            return $_SERVER['HTTP_CLIENTIP'];
        }

        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '';
    }

    public function issetXForwarderForIp()
    {
        return isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function isValidXForwarderForIp()
    {
        return ip2long($this->parseXForwardedForIp()) !== false;
    }

    public function getXForwarderForIp()
    {
        if ($this->xForwardedForHasSeveralIps()) {
            return $this->parseXForwardedForIp();
        }

        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    private function xForwardedForHasSeveralIps()
    {
        return count($this->getXForwarderForIps()) > 0;
    }

    private function parseXForwardedForIp()
    {
        $ips = $this->getXForwarderForIps();

        return reset($ips);
    }

    private function getXForwarderForIps()
    {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function getBrowserLocale()
    {
        $accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';

        return substr($accept_language, 0, 2);
    }

    public function setProperty($property, $value)
    {
        if (!property_exists($this, $property)) {
            throw new \Exception("Property {$property} does not exists");
        }

        $this->{$property} = $value;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getCurlInfo()
    {
        return $this->curl_info;
    }

    public function getLogFilePath()
    {
        return $this->log_file_path;
    }

    public function setLogFilePath($log_file_path)
    {
        $this->log_file_path = $log_file_path;
    }

    public function isEnableWriteLog()
    {
        return $this->enable_write_log;
    }

    public function enableWriteLog($enable = false, $log_file_path = '')
    {
        $this->enable_write_log = $enable;
        $this->log_file_path = $log_file_path;

        return $this;
    }

    public function setEnableWriteLog($enable_write_log)
    {
        $this->enable_write_log = $enable_write_log;
    }

    private function writeLog()
    {
        @file_put_contents(
            $this->log_file_path,
            sprintf("[%s] %s\n", date("Y-m-d H:i:s"), http_build_query($this->getRequestParams())),
            FILE_APPEND
        );
    }
}
