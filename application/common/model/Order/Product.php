<?php

namespace app\common\model\Order;

use think\Model;
use \think\Config; //引入配置类

/**
 * 订单商品模型
 */
class Product extends Model
{
    //设置表名
    protected $name = 'order_product';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    public function pro(){
        return $this->belongsTo('app\common\model\Product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
