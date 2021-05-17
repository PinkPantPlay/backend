<?php

namespace app\common\model\User;

use think\Model;

class Address extends Model
{
    //设置表名
    protected $name = 'user_address';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    //定义软删除的字段
    protected $deleteTime = false;

    //关联模型 是让当前的模型(Address) 去关联别的模型(Region地区)
    public function province()
    {
        //关联模型
        //当前表的 province字段 对应 region表的code的字段
        //province == code LEFT JOIN 查询一条
        return $this->belongsTo('app\common\model\Common\Region', 'province', 'code', [], 'LEFT')->setEagerlyType(0);
    }

    public function city()
    {
        return $this->belongsTo('app\common\model\Common\Region', 'city', 'code', [], 'LEFT')->setEagerlyType(0);
    }

    public function district()
    {
        return $this->belongsTo('app\common\model\Common\Region', 'district', 'code', [], 'LEFT')->setEagerlyType(0);
    }
}
