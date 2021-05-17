<?php

namespace app\common\model\User;

use think\Model;

//软删除模型
use traits\model\SoftDelete;

class User extends Model
{
    //软删除
    use SoftDelete;

    //设置表名
    protected $name = 'user';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    //定义软删除的字段
    protected $deleteTime = 'deletetime';
}
