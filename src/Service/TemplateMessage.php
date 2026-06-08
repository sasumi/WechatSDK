<?php

namespace LFPhp\WechatSdk\Service;

use LFPhp\WechatSdk\Base\AuthorizedService;

class TemplateMessage extends AuthorizedService {
    /**
     * 发送模板消息
     * @param string $to_user 接收者openid
     * @param string $template_id 模板ID
     * @param array $data 模板数据，格式为 ['keyword1' => ['value' => '内容', 'color' => '#173177'], ...]
     * @param string|null $url 模板跳转链接（可选）
     * @return string 模板消息ID，微信接口返回的唯一标识符，可以用于后续查询和排查问题
     */
    public static function sendTemplateMessage($to_user, $template_id, $data, $url = null, $client_msg_id = null) {
        $api_url = "https://api.weixin.qq.com/cgi-bin/message/template/send";
        $payload = [
            'touser' => $to_user,
            'template_id' => $template_id,
            'data' => $data,
            'url' => $url,
            'client_msg_id' => $client_msg_id ?: uniqid('', true), //生成唯一的消息ID，方便后续查询和排查问题
        ];
        $rsp = self::postJsonSuccess($api_url, $payload);
        return $rsp['msgid'];
    }

    /**
     * 删除模板消息
     * @param string $template_id 模板ID
     */
    public static function deleteTemplate($template_id) {
        $api_url = "https://api.weixin.qq.com/cgi-bin/template/del_private_template";
        self::postJsonSuccess($api_url, [
            'template_id' => $template_id,
        ]);
    }

    /**
     * 查询被拦截的模板消息记录
     * @param string $tmpl_msg_id 模板消息ID
     * @param int $largest_id 上次查询返回的最大消息ID，第一次查询时传0
     * @param int $limit 每次查询返回的记录数，最大100条
     * @return array 包含被拦截的消息列表和最新的最大消息ID，格式为 [[$msg1, $msg2, ...], $last_msg_id]
     * @throws \InvalidArgumentException 如果limit参数不合法
     */
    public static function queryBlockTemplateMessage($tmpl_msg_id, $largest_id = 0, $limit = 20) {
        $api_url = "https://api.weixin.qq.com/wxa/sec/queryblocktmplmsg";
        if ($limit < 1 || $limit > 100) {
            throw new \InvalidArgumentException('limit must be between 1 and 100');
        }
        $payload = [
            'tmpl_msg_id' => $tmpl_msg_id,
            'largest_id' => $largest_id,
            'limit' => $limit,
        ];
        $rsp = self::postJsonSuccess($api_url, $payload);
        $msg_list = [];
        $last_msg_id = $rsp['msginfo']['id'];
        foreach ($rsp['msginfo'] as $item) {
            $msg_list[] = [
                'open_id' => $item['openid'],
                'tmpl_msg_id' => $item['tmpl_msg_id'],
                'title' => $item['title'],
                'content' => $item['content'],
                'send_timestamp' => $item['send_timestamp'],
            ];
        }
        return [$msg_list, $last_msg_id];
    }
}
