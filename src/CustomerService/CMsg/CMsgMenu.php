<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgMenu extends CMsgAbstract {
    /** @var string 菜单消息头部内容 */
    public $head_content;

    /** @var string 菜单消息尾部内容 */
    public $tail_content;

    /** 
     * @var array 菜单项列表
     * 结构如下：
     * [
     *    [
     *        'id' => '菜单项ID',
     *        'content' => '菜单项内容',
     *    ],
     * ]
     */
    public $list = [];

    public function getType() {
        return 'msgmenu';
    }

    public function getData() {
        return [
            'head_content' => $this->head_content,
            'tail_content' => $this->tail_content,
            'list' => $this->list,
        ];
    }
}
