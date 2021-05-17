<?php

namespace app\common\validate\user;

use think\Validate;

/**
 * 用户验证器
 */
class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'phone'   => 'require|unique:user|max:11|/^1[0-9]{10}/',
        'password' => 'require',
        'salt'   => 'require',
        'gender' => 'number|in:0,1,2',
        'openid'=> 'unique:user'
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'phone.require' => '手机号码必填',
        'phone.unique'  => '手机号码必须是唯一的，该手机号码已存在',
        'password.require'  => '密码必填',
        'salt.require'      => '密码盐必填',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
        //添加
        'add'  => ['phone','salt','password'],

        //h5手机端修改基本资料
        'profile' => ['phone','nickname','gender'],

        //微信小程序的添加
        'wxadd' => ['openid','paysalt','paypass','phone']
    ];
    
}
