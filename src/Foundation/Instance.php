<?php
namespace FantasyStudio\WeChat\Foundation;

class Instance
{
    use Foundation;

    protected $config;
    protected $driver;

    public $response_data;
    public $request_data;

    public function __construct($config, $driver)
    {
        $this->config = $config;
        $this->driver = $driver;
    }

    public function getAccessToken()
    {
        $access_token = sprintf("access_token_%s", $this->config["app_id"]);

        /**
         * 如果存在 access token 则返回
         * 不处理access token 失败的异常。
         */

        if ($this->driver->contains($access_token)) {
            return $this->driver->fetch($access_token);
        } else {
            //已经过期，重新申请

            $result = $this->sendGet("https://api.weixin.qq.com/cgi-bin/token", [
                "grant_type" => "client_credential",
                "appid" => $this->config["app_id"],
                "secret" => $this->config["app_secret"]
            ]);

            if (!array_key_exists("access_token", $result->getResponseData())){

                $error = json_encode($result->getResponseData());

                throw new \Exception("获取微信 access_token 失败, 错误详情: {$error}");
            }
            $this->driver->save($access_token, $result->response["access_token"], 7100);
            return $result->getResponseData()["access_token"];
        }
    }

    /**
     * 获取微信卡券 API ticket
     * @return mixed
     * @throws \Exception
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getCardApiTicket()
    {

        $js_ticket = sprintf("card_api_ticket_%s", $this->config["app_id"]);

        if ($this->driver->contains($js_ticket)) {
            return $this->driver->fetch($js_ticket);
        }else{
            $token =  $this->getAccessToken();
            $result = $this->sendGet("https://api.weixin.qq.com/cgi-bin/ticket/getticket", [
                "access_token" => $token,
                "type" => "wx_card"
            ]);

            if (!array_key_exists("ticket", $result->getResponseData())){
                $error = json_encode($result->getResponseData());
                throw new \Exception("获取微信 JS API Ticket 失败, 错误详情: {$error}");
            }

            $this->driver->save($js_ticket, $result->getResponseData()["ticket"], 7100);

            return $result->getResponseData()["ticket"];
        }
    }


    /**
     * 获取微信卡券 API ticket
     * @return mixed
     * @throws \Exception
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getJsApiTicket()
    {

        $js_ticket = sprintf("jssdk_api_ticket_%s", $this->config["app_id"]);

        if ($this->driver->contains($js_ticket)) {
            return $this->driver->fetch($js_ticket);
        }else{
            $token =  $this->getAccessToken();
            $result = $this->sendGet("https://api.weixin.qq.com/cgi-bin/ticket/getticket", [
                "access_token" => $token,
                "type" => "jsapi"
            ]);

            if (!array_key_exists("ticket", $result->getResponseData())){
                $error = json_encode($result->getResponseData());
                throw new \Exception("获取微信 JS API Ticket 失败, 错误详情: {$error}");
            }

            $this->driver->save($js_ticket, $result->getResponseData()["ticket"], 7100);

            return $result->getResponseData()["ticket"];
        }
    }
}