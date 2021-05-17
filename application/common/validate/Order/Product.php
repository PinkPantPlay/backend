<?php

namespace app\common\validate\order;

use think\Validate;

/**
 * 商品验证器
 */
class Product extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'orderid' => 'require',
        'proid' => 'require',
        'nums' => 'require',
        'total' => 'require',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'orderid.require' => '订单ID信息未知',
        'proid.require' => '商品ID信息未知',
        'nums.require' => '商品数量未知',
        'total.require' => '商品合计未知',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
    
}
