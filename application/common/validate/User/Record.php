<?php

namespace app\common\validate\user;

use think\Validate;

/**
 * 消费记录验证器
 */
class Record extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'userid'   => 'require',
        'price' => 'require|gt:0',
        'content'   => 'require',
        'status' => 'in:1,2'
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'userid.require'      => '用户信息未知',
        'price.require'  => '消费金额必填',
        'price.gt'  => '消费金额必须大于0',
        'content.require'      => '消费内容请填写',
        
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
    
}
