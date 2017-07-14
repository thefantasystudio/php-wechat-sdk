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
     * @return mixed|null
     * @author Andylee <leefongyun@gmail.com>
     */
    public function user()
    {
        $result = $this->sendPost("https://api.weixin.qq.com/sns/oauth2/access_token", "json", [
            "appid" => $this->config["app_id"],
            "secret" => $this->config["app_secret"],
            "code" => $_REQUEST["code"],
            "grant_type" => "authorization_code"
        ]);

        if ($this->config["redirect"]["scope"] == "code"){
            return $result->getResponseData();
        }else{
            return $this->sendGet(" https://api.weixin.qq.com/sns/userinfo?access_token={$result->getResponseData()["access_token"]}&openid={$result->getResponseData()["openid"]}&lang=zh_CN", [])->getResponseData();
        }

    }
}