<?php

namespace app\common\validate\product;

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
        'name' => 'require',
        'price' => 'require',
        'market' => 'require',
        'cover' => 'require',
        'thumbs' => 'require',
        'typeid' => 'require',
        'status' => 'number|in:0,1',
        'phone'   => 'max:11|/^1[0-9]{10}/',
        'stock'   => 'number',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'name' => '商品名称必填',
        'price' => '折扣价必填',
        'market' => '市场价必填',
        'cover' => '请选择商品封面图',
        'thumbs' => '请选择商品图集',
        'typeid' => '请选择商品分类',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
    
}
