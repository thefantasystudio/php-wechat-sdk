<?php
namespace FantasyStudio\WeChat\Auth;

use FantasyStudio\WeChat\Foundation\Instance;

/**
 * 微信网页授权
 * @package FantasyStudio\WeChat\Auth
 * @version 1.0
 * @copyright Fantasy Studio
 * @author Andylee <leefongyun@gmail.com>
 */
class Auth extends Instance
{
    /**
     * 生成微信网页授权URL入口
     * @param string $state 你需要携带的自定义参数
     * @return string
     * @author Andylee <leefongyun@gmail.com>
     */
    public function url($state)
    {
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->config["app_id"]}&redirect_uri={$this->config["redirect"]["url"]}&response_type=code&scope={$this->config["redirect"]["scope"]}&state={$state}#wechat_redirect";
    }

    /**
     * 获取用户信息
     * @throws \Exception
     * @return mixed|null
     * @author Andylee <leefongyun@gmail.com>
     */
    public function user()
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token";

        $result = $this->sendGet($url, [
            "appid" => $this->config["app_id"],
            "secret" => $this->config["app_secret"],
            "code" => $_GET["code"],
            "grant_type" => "authorization_code"
        ]);

        if ($result->getResponseData["errcode"] != 0) {
            throw new \Exception("can not get access_token, errcode is {$result->getResponseData["errcode"]}, message is {$result->getResponseData["errmsg"]}");
        }

        if ($this->config["redirect"]["scope"] !== "snsapi_base") {
            return $result->getResponseData();
        } else {
            $user_info = $this->sendGet("https://api.weixin.qq.com/sns/userinfo", [
                "access_token" => $result->getResponseData()["access_token"],
                "openid" => $result->getResponseData()["openid"],
                "lang" => "zh_CN"]);
            if ($user_info->getResponseData()["errcode"] != 0) {
                throw new \Exception("can not get access_token, errcode is {$user_info->getResponseData["errcode"]}, message is {$user_info->getResponseData["errmsg"]}");
            }

            return $user_info->getResponseData();
        }

    }
}