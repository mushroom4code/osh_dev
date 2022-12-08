<?php
namespace Ipolh\SDEK\Api\Client;

use Exception;

/**
 * Class curl
 * @package Ipolh\SDEK\Api\Client
 */
class curl
{
    /**
     * @var null|resource
     */
    private $client;
    /**
     * @var mixed
     */
    private $answer;
    /**
     * @var null|int
     */
    private $code;
    /**
     * @var string
     */
    private $url = '';
    /**
     * @var int
     */
    private $curlErrNum = 0;
    /**
     * @var array
     */
    private $arrResponseHeaders = [];

    /**
     * curl constructor.
     * @param string $url
     * @param array $config
     * @throws Exception
     */
    public function __construct($url = false, array $config = [])
    {
        if (!function_exists('curl_init')) {
            throw new Exception('No CURL library');
        }
        $this->client = curl_init();
        if ($url) {
            $this->setUrl($url);
        }
        if ($config) {
            $this->config($config);
        }
    }

    /**
     * @param string $data
     * @return $this
     */
    public function post($data = '')
    {
        $this->setOpt(CURLOPT_POST, TRUE);
        if($data) {
            $this->setOpt(CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($this->client, CURLOPT_HEADERFUNCTION, [$this, 'responseHeaderParser']);
        $this->request();
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function get(array $data = [])
    {
        if ($data) {
            if (strpos($this->url, '?') !== false) {
                $this->url = substr($this->url, 0, strpos($this->url, '?'));
            }
            $this->url .= '?' . http_build_query($data);
        }
        $this->request();

        return $this;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function put($data = '')
    {
        curl_setopt($this->client,CURLOPT_CUSTOMREQUEST, 'PUT');
        if($data) {
            $this->setOpt(CURLOPT_POSTFIELDS, $data);
        }
        $this->request();

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        $this->setOpt(CURLOPT_CUSTOMREQUEST, "DELETE");

        $this->request();

        return $this;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function config(array $args)
    {
        curl_setopt_array($this->client, $args);

        return $this;
    }

    /**
     * @param int $opt
     * @param mixed $val
     * @return $this
     */
    public function setOpt($opt, $val)
    {
        curl_setopt($this->client, $opt, $val);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return array('code' => $this->getCode(), 'answer' => $this->getAnswer());
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return $this
     */
    public function flee()
    {
        if ($this->client) {
            curl_close($this->client);
        }
        return $this;
    }

    /**
     * @param bool $close
     * @return $this
     */
    private function request($close = true)
    {
        $this->setOpt(CURLOPT_URL, $this->url);
        $this->answer = curl_exec($this->client);
        $this->code = curl_getinfo($this->client, CURLINFO_HTTP_CODE);
        if ($this->code === 0) {
            $this->curlErrNum = curl_errno($this->client);
        }
        if ($close) {
            $this->flee();
        }
        return $this;
    }

    private function responseHeaderParser($curl, $header)
    {// this function is called by curl for each header received
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) { // ignore invalid headers
            return $len;
        }

        $this->arrResponseHeaders[strtolower(trim($header[0]))][] = trim($header[1]);
        return $len;
    }

    /**
     * @return int
     */
    public function getCurlErrNum()
    {
        return $this->curlErrNum;
    }

    /**
     * @return array
     */
    public function getArrResponseHeaders()
    {
        return $this->arrResponseHeaders;
    }
}