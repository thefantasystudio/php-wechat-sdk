<?php
namespace FantasyStudio\WeChat\Card;

use FantasyStudio\WeChat\Foundation\Instance;

/**
 * 会员卡相关
 * @package FantasyStudio\WeChat\Card
 * @version 1.0
 * @copyright Fantasy Studio
 * @author Andylee <leefongyun@gmail.com>
 */
class MemberCard extends Instance
{
    /**
     * 设置快速买单
     * @return bool
     * @param $card_id
     * @author Andylee <leefongyun@gmail.com>
     */
    public function setFastBuy($card_id)
    {
        $this->request_data = [
            "card_id" => $card_id,
            "is_open" => true
        ];
        $url = sprintf("https://api.weixin.qq.com/card/paycell/set?access_token=%s", $this->getAccessToken());
        $result = $this->sendPost($url, "json", $this->request_data);

        $this->response_data = $result->getResponseData();

        if ($result->getResponseData()["errcode"] == 0) {
            return true;
        }
        return false;
    }

    /**
     * 拉取单张会员卡使用数据接口
     * @return array
     * @description 支持开发者调用该接口拉取API创建的会员卡数据情况
     * @param $start_time 示例值 2015-06-15
     * @param $end_time
     * @param $card_id
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getSingeMemberCardUsedData($start_time, $end_time, $card_id)
    {
        $result = $this->sendPost("https://api.weixin.qq.com/datacube/getcardmembercarddetail?access_token={$this->getAccessToken()}",
            "json", [
                "begin_date" => $start_time,
                "end_date" => $end_time,
                "card_id" => $card_id
            ]);

        return $result->getResponseData();
    }

    /**
     * 获取指定会员卡的会员信息
     * @return array
     * @param $card_id
     * @param $code
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getMemberCardUserInfo($card_id, $code)
    {
        $result = $this->sendPost("https://api.weixin.qq.com/card/membercard/userinfo/get?access_token={$this->getAccessToken()}",
            "json", [
                "card_id" => $card_id,
                "code" => $code
            ]);

        return $result->getResponseData();
    }


    /**
     * 更新会员卡资料
     * @param $data
     * @return mixed|null
     * @throws \Exception
     * @author Andylee <leefongyun@gmail.com>
     */
    public function updateMemberCardUserInfo($data)
    {
        /**
         * {
         * "code": "179011264953",
         * "card_id": "p1Pj9jr90_SQRaVqYI239Ka1erkI",
         * "background_pic_url": "https://mmbiz.qlogo.cn/mmbiz/0?wx_fmt=jpeg",
         * "record_bonus": "消费30元，获得3积分",
         * "bonus": 3000,
         * "add_bonus": 30,
         * "balance": 3000,
         * "add_balance": -30,
         * "record_balance": "购买焦糖玛琪朵一杯，扣除金额30元。",
         * "custom_field_value1": "xxxxx"，
         * "custom_field_value2": "xxxxx"，
         * "notify_optional": {
         * "is_notify_bonus": true,
         * "is_notify_balance": true,
         * "is_notify_custom_field1":true
         * }
         * }
         */

        $required_fields = [
            "code", "card_id"
        ];

        $option_fields = [
            "record_balance", "add_balance", "record_bonus", "bonus", "add_bonus", "balance"
        ];

        foreach ($required_fields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new \Exception("the {$field} field must be required");
            }
        }

        foreach ($data as $key => $item) {
            if (!in_array($key, ["card_id", "code"])) {
                if (!in_array($key, $option_fields)) {
                    throw new \Exception("the {$key} field is invalid, only allow  \"record_balance\", \"add_balance\", \"record_bonus\", \"bonus\", \"add_bonus\", \"balance\"");
                }
            }
        }

        $data["notify_optional"]["is_notify_bonus"] = true;
        $data["notify_optional"]["is_notify_balance"] = true;

        $result = $this->sendPost("https://api.weixin.qq.com/card/membercard/updateuser?access_token={$this->getAccessToken()}", "json", $data);

        return $result->getResponseData();

    }

    /**
     * 设置卡券失效
     * @description 此操作不可逆
     * @param $card_id
     * @param $code
     * @param $reason 操作理由
     * @return array
     * @author Andylee <leefongyun@gmail.com>
     */
    public function setMemberCardInvalid($card_id, $code, $reason)
    {
        return $this->sendPost("https://api.weixin.qq.com/card/code/unavailable?access_token={$this->getAccessToken()}", "json", [
            "card_id" => $card_id,
            "code" => $code,
            "reason" => $reason
        ])->getResponseData();
    }

    /**
     * 开启会员卡动态码支付
     * @param $card_id
     * @return array
     * @author Andylee <leefongyun@gmail.com>
     */
    public function enableDynamicCodePay($card_id)
    {
        return $this->sendPost("https://api.weixin.qq.com/card/update?access_token={$this->getAccessToken()}", "json", [
            "card_id" => $card_id,
            "member_card" => [
                "base_info" => [
                    "use_dynamic_code" => true
                ]
            ]
        ])->getResponseData();
    }

    /**
     * 根据动态码查询code接口
     * @param string $code 动态码
     * @param bool $is_expire_dynamic_code 是否查询过期动态码，设置该参数为true时，开发者可以查询到超时的动态码，用于处理因断网导致的积压订单。默认为false
     * @return array
     * @author Andylee <leefongyun@gmail.com>
     */
    public function queryCardWithDynamicCode($code, $is_expire_dynamic_code = false)
    {
        return $this->sendPost("https://api.weixin.qq.com/card/code/get?access_token={$this->getAccessToken()}","json", [
            "code" => $code,
            "is_expire_dynamic_code" => $is_expire_dynamic_code

        ])->getResponseData();
    }

}