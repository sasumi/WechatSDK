<?php

namespace LFPhp\WechatSdk\CustomerService;

use LFPhp\WechatSdk\Base\AuthorizedService;

use const LFPhp\Func\HTTP_METHOD_GET;
use const LFPhp\Func\HTTP_METHOD_POST;

class CustomerSession extends AuthorizedService {
    /**
     * @param string $kf_account 完整客服账号，格式为：账号前缀@公众号微信号
     * @param string $openid 粉丝的openid
     * @return array
     */
    public static function createSession($kf_account, $openid) {
        $url = 'https://api.weixin.qq.com/customservice/kfsession/create';
        $rsp = self::sendJsonRequest($url, [
            'kf_account' => $kf_account,
            'openid' => $openid,
        ], HTTP_METHOD_POST);
        self::assertResultSuccess($rsp);
        return $rsp;
    }

    /**
     * @param string $kf_account 完整客服账号，格式为：账号前缀@公众号微信号
     * @param string $openid 粉丝的openid
     * @return array
     */
    public static function closeSession($kf_account, $openid) {
        $url = 'https://api.weixin.qq.com/customservice/kfsession/close';
        $rsp = self::sendJsonRequest($url, [
            'kf_account' => $kf_account,
            'openid' => $openid,
        ], HTTP_METHOD_POST);
        self::assertResultSuccess($rsp);
        return $rsp;
    }

    /**
     * @param string $openid 粉丝的openid
     * @return array
     */
    public static function getSession($openid) {
        $url = 'https://api.weixin.qq.com/customservice/kfsession/getsession';
        $rsp = self::sendJsonRequest($url, [
            'openid' => $openid,
        ], HTTP_METHOD_GET);
        self::assertResultSuccess($rsp);
        return [
            'kf_account' => $rsp['kf_account'] ?? null,
            'createtime' => $rsp['createtime'] ?? null,
        ];
    }

    /**
     * @param string $kf_account 完整客服账号，格式为：账号前缀@公众号微信号
     * @return array
     */
    public static function getSessionList($kf_account) {
        $url = 'https://api.weixin.qq.com/customservice/kfsession/getsessionlist';
        $rsp = self::sendJsonRequest($url, [
            'kf_account' => $kf_account,
        ], HTTP_METHOD_GET);
        self::assertResultSuccess($rsp);
        return $rsp['sessionlist'] ?? [];
    }

    public static function getWaitCaseList() {
        $url = 'https://api.weixin.qq.com/customservice/kfsession/getwaitcase';
        $rsp = self::sendJsonRequest($url, [], HTTP_METHOD_GET);
        self::assertResultSuccess($rsp);
        return $rsp['waitcaselist'] ?? [];
    }
}
