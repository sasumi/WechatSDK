<?php

namespace LFPhp\WechatSdk\Service;

use LFPhp\WechatSdk\Base\AuthorizedService;

class User extends AuthorizedService {

    /**
     * 获取用户列表
     * @param string $next_open_id 下一次拉取的起始位置
     * @see https://developers.weixin.qq.com/doc/service/api/usermanage/userinfo/api_getfans.html
     * @return array[]
     */
    public static function getList($next_open_id = null) {
        $url = "https://api.weixin.qq.com/cgi-bin/user/get";
        $data = self::getJsonSuccess($url, [
            'next_openid'  => $next_open_id,
        ]);
        return [
            'total'        => $data['total'], //关注该公众账号的总用户数
            'count'        => $data['count'],  //拉取的OPENID个数，最大值为10000
            'open_id_list'         => $data['data'],  //OpenId列表数据
            'next_openid'  => $data['next_openid'], //拉取列表的最后一个用户的OPENID
        ];
    }

    public static function getBlackList($begin_openid = null) {
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/members/getblacklist';
        $data = self::postJsonSuccess($url, [
            'begin_openid' => $begin_openid,
        ]);
        return [
            'total'        => $data['total'], //用户总数
            'count'        => $data['count'],  //本次返回的用户数
            'data'         => $data['data'],  //列表数据
            'next_openid'  => $data['next_openid'], //本次列表后一位openid
        ];
    }

    /**
     * 批量获取用户基本信息
     * 注意：最多一次拉取100条，超出会分块多次拉取
     * @see https://developers.weixin.qq.com/doc/service/api/usermanage/userinfo/api_batchuserinfo.html
     * @param string[] $open_id_list
     * @return array[]
     */
    public static function batchGetInfo(array $open_id_list) {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget';
        $chunks = array_chunk($open_id_list, 100);
        $user_list = [];
        foreach ($chunks as $openid_batch) {
            $list = [];
            foreach ($openid_batch as $open_id) {
                $list[] = [
                    'openid'       => $open_id,
                    'lang'         => 'zh_CN',
                ];
            }
            $tmp = self::postJsonSuccess($url, [
                'user_list'    => $list,
            ]);
            $user_list = array_merge($user_list, $tmp['user_info_list']);
        }
        return $user_list;
    }

    /**
     * 获取用户基本信息
     * @see https://developers.weixin.qq.com/doc/service/api/usermanage/userinfo/api_userinfo.html
     */
    public static function getInfo($open_id) {
        $url = "https://open.weixin.qq.com//cgi-bin/user/info";
        return self::getJsonSuccess($url, [
            'openid'       => $open_id,
        ]);
    }
}
