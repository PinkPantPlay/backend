<?php

namespace app\common\model\Product;

use think\Model;

class Type extends Model
{
    //设置表名
    protected $name = 'product_type';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 查询分类商品
    public function product()
    {
      return $this->hasMany('app\common\model\Product\Product','typeid','id',[],'LEFT')->where(['flag'=>'hot','status'=>1]);
    }
}
