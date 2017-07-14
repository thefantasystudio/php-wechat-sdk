<?php
namespace FantasyStudio\WeChat\Foundation;

use \GuzzleHttp\Psr7\Response as Res;

/**
 * Class Response
 * @package FantasyStudio\WeChat\Foundation
 * @version 1.0
 * @copyright fantasy studio
 * @author Andylee <leefongyun@gmail.com>
 */
class Response
{
    public $request;
    public $response;
    public $query_status;

    public function __construct($request, Res $response)
    {

        if ($response->getStatusCode() != 200) {
            throw new \HttpException("发送请求失败，HTTP STATUS CODE IS {$response->getStatusCode()}");
        }

        $body = (string)$response->getBody();

        $decoded_data = json_decode($body, true);

        if ($decoded_data === null && json_last_error() !== JSON_ERROR_NONE) {
            $code = json_last_error();
            throw new \InvalidArgumentException("decoded json data fail, error code is {$code}");
        }

        $this->request = $request;
        $this->response = $decoded_data;
        $this->query_status = true;
    }

    public function getRequestData()
    {
        return $this->request;
    }

    public function getResponseData()
    {
        return $this->response;
    }

    public function isSuccessful()
    {
        return $this->query_status;
    }
}