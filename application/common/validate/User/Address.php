<?php

namespace app\common\validate\user;

use think\Validate;

/**
 * 用户收货地址验证器
 */
class Address extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'userid' => 'require',
        'consignee' => 'require',
        'province' => 'require',
        'city' => 'require',
        'district' => 'require',
        'address' => 'require',
        'mobile'   => 'require|max:11|/^1[0-9]{10}/',
        'status' => 'number|in:0,1'
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'userid.require' => '用户ID信息未知',
        'consignee.require' => '收货人必填',
        'province.require' => '省份必填',
        'city.require' => '城市必填',
        'district.require' => '区域必填',
        'address.require' => '详细地址必填',
        'mobile.require' => '手机号码必填',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
        //添加
        'add'  => ['userid','consignee','province','city','district','address','mobile'],

        //添加
        'edit'  => ['userid','consignee','province','city','district','address','mobile'],
    ];
    
}
