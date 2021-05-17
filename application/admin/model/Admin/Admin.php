<?php


namespace app\admin\model\Admin;


use think\Model;
use traits\model\SoftDelete;

class Admin extends Model
{
    //软删除
    use SoftDelete;

    //设置表名
    protected $name = 'admin';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    //定义软删除的字段
    protected $deleteTime = 'deletetime';
}