<?php


namespace app\admin\model\Order;


use think\Model;
use traits\model\SoftDelete;
use app\common\model\User\Address as address;

class Order extends Model
{
    //软删除
    use SoftDelete;

    //设置表名
    protected $name = 'order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    //定义软删除的字段
    protected $deleteTime = 'delete';

    public function address(){
        return $this->belongsTo('app\common\model\Common\Address', 'addrid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}