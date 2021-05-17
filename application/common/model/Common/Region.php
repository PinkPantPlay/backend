<?php

namespace app\common\model\Common;

use think\Model;

class Region extends Model
{
    //设置表名
    protected $name = 'region';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    //定义软删除的字段
    protected $deleteTime = false;
}
