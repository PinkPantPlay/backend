<?php

namespace app\common\validate\user;

use think\Validate;

/**
 * 用户收藏验证器
 */
class Collection extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'userid' => 'require',
        'proid' => 'require',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'userid.require' => '用户ID信息未知',
        'proid.require' => '商品ID信息未知',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
    
}
