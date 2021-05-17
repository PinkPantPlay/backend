<?php

namespace app\common\model\User;

use think\Model;

class Collection extends Model
{
    //设置表名
    protected $name = 'user_collection';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    //定义软删除的字段
    protected $deleteTime = false;
}
