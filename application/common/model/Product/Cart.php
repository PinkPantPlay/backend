<?php

namespace app\common\model\Product;

use think\Model;
use \think\Config; //引入配置类

class Cart extends Model
{
    //设置表名
    protected $name = 'cart';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    public function product()
    {
        return $this->belongsTo('app\common\model\Product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
