<?php

namespace app\common\validate\product;

use think\Validate;

/**
 * 购物车验证器
 */
class Cart extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'userid'   => 'require',
        'proid'   => 'require',
        'nums'   => 'require|gt:0',
        'total'   => 'require|egt:0',
        'price'   => 'require|egt:0',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'userid.require'  => '用户ID信息不存在',
        'proid.require'  => '商品ID信息不存在',
        'nums.require'  => '请填写商品数量',
        'total.require'  => '总价未知',
        'price.require'  => '单价未知',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
    
}
