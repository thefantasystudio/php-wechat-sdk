<?php
namespace FantasyStudio\WeChat\Card;

use FantasyStudio\WeChat\Foundation\Instance;

/**
 * 卡券相关API
 * @package FantasyStudio\WeChat\Card
 * @version 1.0
 * @copyright fantasy studio
 * @author Andylee <leefongyun@gmail.com>
 */
class Card extends Instance
{

    /**
     * 生成创建卡券二维码
     * @description 开发者可调用该接口生成一张卡券二维码供用户扫码后添加卡券到卡包。
     * @param         $card_id
     * @param string  $code
     * @param string  $outer_str
     * @param string  $open_id
     * @param bool $is_unique 指定下发二维码，生成的二维码随机分配一个code，领取后不可再次扫描。填写true或false。默认false，注意填写该字段时，卡券须通过审核且库存不为0。
     * @param integer $expire_time 卡券过期时间
     * @return mixed
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getAddCardQr($card_id, $code = "", $expire_time = 1800, $is_unique = false, $outer_str = "", $open_id = "")
    {
        $data = [
            "action_name" => "QR_CARD",
            "expire_seconds" => $expire_time,
            "action_info" => [
                "card" => [
                    "card_id" => $card_id,
                    "code" => $code,
                    "openid" => $open_id,
                    "is_unique_code" => $is_unique,
                    "outer_str" => $outer_str
                ]
            ]
        ];
        $url = sprintf("https://api.weixin.qq.com/card/qrcode/create?access_token=%s", $this->getAccessToken());
        $result = $this->sendPost($url, "json", $data);

        if ($result->getResponseData()["errcode"] == 0) {
            return $result->getResponseData();
        }

        return false;
    }

    /**
     * 查询Code接口
     * @param string $card_id
     * @param string $code
     * @param bool   $check_consume
     * @return bool
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getQueryCode($card_id, $code, $check_consume = false)
    {
        $result = $this->sendPost("https://api.weixin.qq.com/card/code/get?access_token={$this->getAccessToken()}",
            "json", [
                "card_id" => $card_id,
                "code" => $code,
                "check_consume" => $check_consume ? $check_consume : false
            ]);

        $this->response_data = $result->getResponseData();

        if ($check_consume) {
            //当check_consume为true时返回数据
            if ($this->response_data["errcode"] == 0 and
                $this->response_data["can_consume"] == true
            ) {
                return true;
            }
            return false;

        } else {
            if ($this->response_data["errcode"] == 0 and $this->response_data["can_consume"] == true) {
                return true;
            }
            return false;
        }
    }

    /**
     * 核销卡券
     * @param        $code
     * @param string $card_id
     * @return array
     * @author Andylee <leefongyun@gmail.com>
     */
    public function consumeCode($code, $card_id = "")
    {
        $result = $this->sendPost("https://api.weixin.qq.com/card/code/consume?access_token={$this->getAccessToken()}",
            "json", [
                "code" => $code,
                "card_id" => $card_id
            ]);

        return $result->getResponseData();

    }

    /**
     * 获取卡券详情
     * @return array
     * @param $card_id 卡券ID
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getCardDetail($card_id)
    {
        $result = $this->sendPost("https://api.weixin.qq.com/card/get?access_token={$this->getAccessToken()}", "json",
            [
                "card_id" => $card_id
            ]);
        return $result->getResponseData();
    }

    /**
     * 处理微信返回状态
     * @param $data
     * @return bool
     * @author Andylee <leefongyun@gmail.com>
     */
    public function checkResponse($data)
    {
        if ($data["errcode"] == 0) {
            return true;
        }
        return false;
    }

    /**
     * 批量查询卡券列表
     * @param        $offset
     * @param int    $count
     * @param string $status_list
     * @return array
     * @author Andylee <leefongyun@gmail.com>
     */
    public function batchGetCards($offset = 0, $count = 50, $status_list = "")
    {
        $result = $this->sendPost("https://api.weixin.qq.com/card/batchget?access_token={$this->getAccessToken()}",
            "json", [
                "offset" => $offset,
                "count" => $count,
                "status_list" => $status_list
            ]);
        return $result->getResponseData();
    }


    /**
     * 获取免费券数据接口
     * 支持开发者调用该接口拉取免费券（优惠券、团购券、折扣券、礼品券）在固定时间区间内的相关数据。
     * @param        $start_time 开始时间
     * @param        $end_time 结束时间
     * @param int    $cond_source 卡券来源，0为公众平台创建的卡券数据、1是API创建的卡券数据
     * @param string $card_id 卡券ID。填写后，指定拉出该卡券的相关数据。
     * @return array
     * @author Andylee <leefongyun@gmail.com>
     */
    public function getCardUsedInfo($start_time, $end_time, $cond_source = 0, $card_id = "")
    {
        $result = $this->sendPost("https://api.weixin.qq.com/datacube/getcardcardinfo?access_token={$this->getAccessToken()}",
            "json", [
                "begin_date" => $start_time,
                "end_date" => $end_time,
                "cond_source" => $cond_source,
                "card_id" => $card_id
            ]);

        return $result->getResponseData();
    }
}