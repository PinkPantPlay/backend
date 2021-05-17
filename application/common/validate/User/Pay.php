<?php

namespace app\common\validate\user;

use think\Validate;

/**
 * 充值验证器
 */
class Pay extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'price' => 'require|gt:0',
        'thumbs'   => 'require',
        'userid'   => 'require',
        'status' => 'in:0,1,-1'
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'price.require'  => '充值金额必填',
        'price.gt'  => '充值金额必须大于0',
        'thumbs.require'      => '转账截图凭证需上传',
        'userid.require'      => '用户信息未知',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
        //添加
        'add'  => ['price','userid','thumbs'],
    ];
    
}
