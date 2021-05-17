<?php

namespace app\common\validate\product;

use think\Validate;

/**
 * 商品分类验证器
 */
class Type extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name'   => 'require',
        'weight'   => 'require',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'name.require'  => '分类名称必填',
        'weight.require'  => '权重属性必填',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
    
}
