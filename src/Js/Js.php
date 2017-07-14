<?php
namespace FantasyStudio\WeChat\Js;

use FantasyStudio\WeChat\Foundation\Instance;

class Js extends Instance
{
    /**
     * 获取api_ticket
     * @return string
     * @description  api_ticket 是用于调用微信卡券JS API的临时票据，有效期为7200 秒，通过access_token 来获取。
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getApiTicket()
    {
        return $this->getCardApiTicket();
    }

    /**
     * 拉取适用卡券列表的签名
     * @param $location_id
     * @param $card_id
     * @param $card_type
     * @return string
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getCardSign($location_id = "", $card_id = "", $card_type = "")
    {

        $sort_array = [
            "api_ticket" => $this->getApiTicket(),
            "appid" => $this->config["app_id"],
            "location_id" => !empty($location_id) ? $location_id : "",
            "timestamp" => time(),
            "nonce_str" => $this->random(),
            "card_id" => !empty($card_id) ? $card_id : "",
            "card_type" => !empty($card_type) ? $card_type : ""
        ];
        asort($sort_array);

        $str = "";

        foreach ($sort_array as $k => $value) {
            $str .= $value;
        }

        return sha1($str);
    }


    /**
     * 创建添加卡券签名
     * @see https://mp.weixin.qq.com/wiki?action=doc&id=mp1421141115&t=0.47257553543867203#fl4
     * @param        $card_id 卡券ID
     * @param string $code 卡券code
     * @param string $open_id 指定领取用户的open_id
     * @param string $outer_str 活动来源
     * @return string
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getAddCardExt($card_id, $code = "", $open_id = "", $outer_str = "")
    {
        $ext["code"] = $code;
        $ext["openid"] = $open_id;
        $ext["timestamp"] = strval(time());
        $ext["nonce_str"] = $this->random(16);
        $ext["api_ticket"] = $this->getApiTicket();
        $ext["card_id"] = $card_id;

        asort($ext);

        $str = "";
        foreach ($ext as $key => $item) {
            $str .= $item;
        }
        $ext["signature"] = sha1($str);

        $card_ext = [
            "timestamp" => $ext["timestamp"],
            "outer_str" => $outer_str,
            "nonce_str" => $ext["nonce_str"],
            "signature" => $ext["signature"]
        ];

        return json_encode($card_ext);
    }
}