<?php

namespace app\common\model\Product;

use think\Model;

class Repair extends Model
{
    //设置表名
    protected $name = 'product_repair';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
}
