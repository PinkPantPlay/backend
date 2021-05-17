<?php


namespace app\admin\validate;


use think\Validate;

class Admin extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'phone'   => 'require|unique:admin|max:11|/^1[0-9]{10}/',
        'password' => 'require',
        'salt'   => 'require',
        'character' => 'number|in:0,1',
        'adminname'=> 'require|unique:admin',
        'status' => 'number|in:0,1'
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'phone.require' => '手机号码必填',
        'phone.unique'  => '手机号码必须是唯一的，该手机号码已存在',
        'password.require'  => '密码必填',
        'salt.require'      => '密码盐必填',
        'adminname.require'      => '登录名必填',
        'adminname.unique' => '登录名唯一，该登录名已存在'
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        //添加
        'add'  => ['phone','salt','password','adminname'],
        //编辑
        'edit' => ['status','character']

    ];
}