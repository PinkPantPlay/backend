<?php

namespace app\common\validate\order;

use think\Validate;

/**
 * 订单验证器
 */
class Order extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'code' => 'require',
        'userid' => 'require',
        'addrid' => 'require',
        'total' => 'require',
        'status' => 'number|in:0,1,2,3,-1',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'code.require' => '订单号信息未知',
        'userid.require' => '用户ID信息未知',
        'addrid.require' => '收货地址信息未知',
        'total.require' => '订单价格未知',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
    
}
