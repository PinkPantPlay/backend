<?php

namespace app\common\model\User;

use think\Model;
use \think\Config; //引入配置类

class Record extends Model
{

    //设置表名
    protected $name = 'user_record';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
}
