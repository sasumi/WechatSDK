<?php

namespace LFPhp\WechatSdk\CustomerService;

use LFPhp\WechatSdk\Base\AuthorizedService;
use LFPhp\WechatSdk\CustomerService\CMsg\CMsgAbstract;

use const LFPhp\Func\HTTP_METHOD_POST;

class CustomerMsg extends AuthorizedService {
    /** 消息来源：客服 */
    const MSG_FROM_CUSTOMER = 'customer';
    
    /** 消息来源：用户 */
    const MSG_FROM_USER = 'user';

    /**
     * @param string $openid 粉丝的openid
     * @param CMsgAbstract $msg 消息对象
     * @return array
     */
    public static function send($openid, CMsgAbstract $msg) {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';
        $rsp = self::sendJsonRequest($url, [
            'touser' => $openid,
            'msgtype' => $msg->getType(),
            $msg->getType() => $msg->getData(),
        ], HTTP_METHOD_POST);
        self::assertResultSuccess($rsp);
        return $rsp;
    }

    /**
     * 查询客服聊天记录
     * @param int $starttime 查询开始时间，UNIX时间戳
     * @param int $endtime 查询结束时间，UNIX时间戳，每次查询不能跨日查询
     * @param int|null $msgid 消息ID，若指定则从该ID开始查询，否则从最新消息开始查询
     * @param int $limit 每次查询的条数，最大10000条
     * @return array 消息列表
     */
    public static function getMsgList($starttime, $endtime, $msgid = null, $limit = 10000) {
        $url = 'https://api.weixin.qq.com/cgi-bin/customservice/msgrecord/getmsglist';
        $rsp = self::sendJsonRequest($url, [
            'starttime' => $starttime,
            'endtime' => $endtime,
            'msgid' => $msgid,
            'number' => $limit,
        ], HTTP_METHOD_POST);
        self::assertResultSuccess($rsp);
        $list = [];
        foreach ($rsp['msglist'] ?? [] as $msg) {
            $list[] = [
                'id' => $msg['msgid'],
                'from' => $msg['openid'] == 2002 ? self::MSG_FROM_CUSTOMER : self::MSG_FROM_USER,
                'time' => $msg['time'],
                'text' => $msg['text'],
            ];
        }
        return $list;
    }
}
