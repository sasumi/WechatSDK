<?php

namespace LFPhp\WechatSdk\Pay;

use LFPhp\WechatSdk\Exception\PayException;

class Refund extends PayService {
    /**
     * 申请退款
     * @param array $param
     * @param string $param['transaction_id']  微信支付订单号
     * @param string $param['out_trade_no']  商户订单号
     * @param string $param['out_refund_no']  商户退款单号
     * @param string $param['reason']  退款原因
     * @param string $param['notify_url']  退款结果通知URL
     * @param int $param['amount']  退款金额
     * @param int $param['total']  订单金额
     * @param string $param['funds_account']  退款资金来源
     * 可选值：
     * UNSETTLED：未结算资金
     * AVAILABLE：可用余额
     * UNAVAILABLE：不可用余额
     * @return array
     * {
     *     "refund_id": "2006001091201407033233368018",
     *     "out_refund_no": "1217752501201407033233368018",
     *     "transaction_id": "1217752501201407033233368018",
     *     "out_trade_no": "1217752501201407033233368018",
     *     "channel": "ORIGINAL",
     *     "status": "SUCCESS",
     *     "create_time": "2019-08-26T10:39:04+08:00",
     }
     */
    public static function applyRefund(array $param) {
        $transaction_id = $param['transaction_id'] ?? null;
        $out_trade_no = $param['out_trade_no'] ?? null;
        $out_refund_no = $param['out_refund_no'];
        $reason = $param['reason'] ?? null;
        $notify_url = $param['notify_url'] ?? null;
        $amount = floatval($param['amount'] ?? 0);
        $total = floatval($param['total'] ?? 0);
        $currency = $param['currency'] ?? CURRENCY_CNY;
        $funds_account = $param['funds_account'] ?? null;

        if (!$amount) {
            throw new PayException('退款金额amount不能为空');
        }
        if (!$total) {
            throw new PayException('订单金额total不能为空');
        }
        if (!$out_refund_no) {
            throw new PayException('退款单号out_refund_no不能为空');
        }
        if (!$transaction_id && !$out_trade_no) {
            throw new PayException('transaction_id和out_trade_no不能同时为空');
        }

        $rsp = self::postJsonSuccess("/v3/refund/domestic/refunds", [
            'transaction_id' => $transaction_id,
            'out_trade_no' => $out_trade_no,
            'out_refund_no' => $out_refund_no,
            'reason' => $reason,
            'notify_url' => $notify_url,
            'funds_account' => $funds_account,
            'amount' => [
                'refund' => $amount,
                'total' => $total,
                'currency' => $currency,
            ]
        ]);
        return $rsp;
    }

    /**
     * 发起异常退款
     * 提交退款申请后，退款结果通知或查询退款确认状态为退款异常，可调用此接口发起异常退款处理。支持退款至用户、退款至交易商户银行账户两种处理方式。
     */
    public static function applyAbnormalRefund(array $param) {
        if (!$param['refund_id']) {
            throw new PayException('refund_id不能为空');
        }
        if (!$param['out_refund_no']) {
            throw new PayException('out_refund_no不能为空');
        }
        if (!$param['type']) {
            throw new PayException('异常退款处理方式type不能为空');
        }

        $rsp = self::postJsonSuccess("/v3/refund/domestic/refunds/{$param['refund_id']}/apply-abnormal-refund", [
            'out_refund_no' => $param['out_refund_no'], //【商户退款单号】商户申请退款时传入的商户系统内部退款单号。
            'type' => $param['type'],
            'bank_type' => $param['bank_type'] ?? null, //银行类型，值列表详见银行类型。仅支持招行、交通银行、农行、建行、工商、中行、平安、浦发、中信、光大、民生、兴业、广发、邮储、宁波银行的借记卡。若退款至用户此字段必填。
            'bank_account' => $param['bank_account'] ?? null, //收款银行卡号
            'real_name' => $param['real_name'] ?? null, //收款用户姓名
        ]);
        return $rsp;
    }
}
