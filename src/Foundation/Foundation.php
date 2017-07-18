<?php
namespace FantasyStudio\WeChat\Foundation;

use GuzzleHttp\Client;

/**
 * wechat php sdk foundation
 * @package FantasyStudio\WeChat\Foundation
 * @version 1.0
 * @copyright tiidian co,ltd
 * @author AndyLee <andylee@tiidian.com>
 */
trait Foundation
{
    public $cache_driver; //定制token缓存驱动

    /**
     * 随机字符串
     * @param int $length
     * @return string
     */
    function random($length = 16)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    public function setCacheDriver($driver, $obj = "")
    {
        $type = ["file", "apc", "memcache", "memcached", "xcache", "redis"];
        if (!in_array($driver, $type)) {
            throw new \RuntimeException("驱动方式不可用，请参考文档");
        }

        if ($driver == "file") {
            $this->cache_driver = new \Doctrine\Common\Cache\ArrayCache();
        }
        if ($driver == "apc") {
            $this->cache_driver = new \Doctrine\Common\Cache\ApcCache();
        }
        if ($driver == "memcache") {
            $this->cache_driver = new \Doctrine\Common\Cache\MemcacheCache();
            $this->cache_driver->setMemcache($obj);
        }
        if ($driver == "memcached") {
            $this->cache_driver = new \Doctrine\Common\Cache\MemcachedCache();
            $this->cache_driver->setMemcached($obj);
        }
        if ($driver == "xcache") {
            $this->cache_driver = new \Doctrine\Common\Cache\XcacheCache();
        }
        if ($driver == "redis") {
            $this->cache_driver = new \Doctrine\Common\Cache\RedisCache();
            $this->cache_driver->setRedis($obj);
        }
    }

    public function getCache()
    {
        return $this->cache_driver;
    }


    /**
     * 发送GET请求
     * @param       $uri
     * @param array $data
     * @return mixed
     * @throws \HttpException
     * @author Andylee <leefongyun@gmail.com>
     */
    public function sendGet($uri, array $data)
    {
        $debug = false;
        if (array_key_exists("debug", $this->config) and $this->config["debug"] == true){
            $debug = true;
        }
        $client = new Client();
        $result = $client->request("GET", $uri, [
            "query" => $data,
            'debug' => $debug
        ]);

        if (property_exists($this, "request_data")) {
            $this->request_data = $data;
        }

        if (property_exists($this, "request_data")) {
            $this->request_data = json_decode((string)$result->getBody(), true);
        }

        return new Response($data, $result);

    }

    /**
     * 发送POST请求
     * @param string $uri 请求的目标 uri
     * @param string $type 请求格式
     * @param array  $data
     * @param string $ca_path 证书路径
     * @throws \HttpException
     * @return Response
     * @author Andylee <andylee@tiidian.com>
     */
    public function sendPost($uri, $type, array $data, $ca_path = "")
    {
        $debug = false;
        if (array_key_exists("debug", $this->config) and $this->config["debug"] == true){
            $debug = true;
        }
        $headers = [
            "debug" => $debug
        ];
        $origin_data = $data;
        if ($type == "json") {
            $headers = [
                "json" => $data
            ];
        } elseif ($type == "xml") {
            $data = $this->toXML($data);
            $headers = ['body' => $data, 'Content-Type' => 'text/xml; charset=UTF8'];

        }

        if (!empty($ca_path)) {
            $headers["cert"] = $ca_path;
        }

        $client = new Client();
        $result = $client->request("POST", $uri, $headers);
        if ($result->getStatusCode() != 200) {
            throw new \HttpException("Http Exception, status code is {$result->getStatusCode()}");
        }

        if (property_exists($this, "request_data")) {
            $this->request_data = $origin_data;
        }

        if (property_exists($this, "response_data")) {

            $body = (string)$result->getBody();

            $decoded_data = json_decode($body, true);

            if ($decoded_data === null && json_last_error() !== JSON_ERROR_NONE) {
                $code = json_last_error();
                throw new \InvalidArgumentException("decoded json data fail, error code is {$code}");
            }
            $this->response_data = $decoded_data;
        }

        return new Response($data, $result);
    }

    /**
     * array to xml
     * @param array $data
     * @return bool|string
     */
    public function toXML($data)
    {
        if (!is_array($data) || count($data) == 0) {
            return false;
        }

        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

}