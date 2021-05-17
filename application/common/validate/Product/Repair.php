<?php

namespace app\common\validate\product;

use think\Validate;

/**
 * 报修验证器
 */
class Repair extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'userid'   => 'require',
        'typeid'   => 'require',
        'phone'   => 'require',
        'content'   => 'require',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'userid.require'  => '用户ID信息未知',
        'typeid.require'  => '商品分类ID信息未知',
        'phone.require'  => '报修手机号码信息未知',
        'content.require'  => '报修内容必填',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
    
}
