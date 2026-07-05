<?php

namespace LFPhp\WechatSdk\CustomerService;

use LFPhp\WechatSdk\Base\AuthorizedService;

use const LFPhp\Func\HTTP_METHOD_GET;
use const LFPhp\Func\HTTP_METHOD_POST;

/**
 * 客服消息接口
 */
class CustomerAccount extends AuthorizedService {
    const ONLINE_STATUS_ONLINE = 1;
    const ONLINE_STATUS_OFFLINE = 0;

    const INVITE_STATUS_WAITING = 'waiting';
    const INVITE_STATUS_ACCEPTED = 'accepted';
    const INVITE_STATUS_REJECTED = 'rejected';

    /**
     * @param string $kf_account 完整客服账号，格式为：账号前缀@公众号微信号
     * @param string $file_path 头像文件路径
     * @return array
     */
    public static function uploadCustomerAvatar($kf_account, $file_path) {
        $url = 'https://api.weixin.qq.com/customservice/kfaccount/uploadheadimg';
        $rsp = self::sendJsonRequest($url, [
            'kf_account' => $kf_account,
        ], HTTP_METHOD_POST, ['media' => $file_path]);
        self::assertResultSuccess($rsp);
        return $rsp;
    }

    /**
     * 删除客服账号
     * @param string $kf_account 完整客服账号，格式为：账号前�缀@公众号微信号
     * @param string $business_id 业务ID，微信公众平台分配的唯一标识，用于区分不同的业务场景
     * @return array
     */
    public static function deleteCustomer($kf_account, $business_id) {
        $url = 'https://api.weixin.qq.com/customservice/kfaccount/del';
        $rsp = self::sendJsonRequest($url, [
            'kf_account' => $kf_account,
            'business_id' => $business_id,
        ], HTTP_METHOD_POST);
        self::assertResultSuccess($rsp);
        return $rsp;
    }

    public static function addCustomer($kf_account, $nickname, $business_id) {
        $url = 'https://api.weixin.qq.com/customservice/kfaccount/add';
        $rsp = self::sendJsonRequest($url, [
            'kf_account' => $kf_account,
            'nickname' => $nickname,
            'business_id' => $business_id,
        ], HTTP_METHOD_POST);
        self::assertResultSuccess($rsp);
        return $rsp;
    }

    /**
     * 更新客服信息
     * @param string $kf_account 完整客服账号，格式为：账号前缀@公众号微信号
     * @param string $nickname 新的客服昵称
     * @return array
     */
    public static function updateCustomer($kf_account, $nickname) {
        $url = 'https://api.weixin.qq.com/customservice/kfaccount/update';
        $rsp = self::sendJsonRequest($url, [
            'kf_account' => $kf_account,
            'nickname' => $nickname,
        ], HTTP_METHOD_POST);
        self::assertResultSuccess($rsp);
        return $rsp;
    }

    /**
     * 获取在线客服列表
     * @return array 在线客服列表，每个元素包含以下字段：
     * - id: 客服编号
     * - open_id: 客服openid
     * - account: 客服账号，格式为：账号前缀@公众号微信号
     * - status: 客服在线状态
     * - accepted_case: 已接待的会话数
     */
    public static function getOnlineList() {
        $url = 'https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist';
        $rsp = self::sendJsonRequest($url, [], HTTP_METHOD_GET);
        self::assertResultSuccess($rsp);
        $online_list = [];
        foreach ($rsp['kf_online_list'] ?? [] as $item) {
            $online_list[] = [
                'id' => $item['kf_id'],
                'open_id' => $item['kf_openid'],
                'account' => $item['kf_account'],
                'status' => $item['status'],
                'accepted_case' => $item['accepted_case'],
            ];
        }
        return $online_list;
    }

    public static function getCustomerList() {
        $url = 'https://api.weixin.qq.com/cgi-bin/customservice/getkflist';
        $rsp = self::sendJsonRequest($url, [], HTTP_METHOD_GET);
        self::assertResultSuccess($rsp);
        $list = $rsp['kf_list'] ?? [];

        $customers = [];
        foreach ($list as $item) {
            $customers[] = [
                'id' => $item['kf_id'], //客服编号
                'open_id' => $item['kf_openid'], //客服openid，客服和用户之间的聊天是通过openid来区分的
                'account' => $item['kf_account'], //客服账号，格式为：账号前缀@公众号微信号
                'nick' => $item['kf_nick'] ?? '', //客服昵称，可能为空
                'wx' => $item['kf_wx'] ?? '', //如果客服账号已绑定了客服人员微信号， 则此处显示微信号
                'avatar' => $item['kf_headimgurl'] ?? '',
                'invite_status' => $item['status'] ?? 0,
                'invite_expire_time' => $item['invite_expire_time'] ?? 0,
                'invite_wx' => $item['invite_wx'] ?? '',
            ];
        }
        return $customers;
    }
}
