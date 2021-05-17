<?php

namespace app\common\model\User;

use think\Model;
use \think\Config; //引入配置类

//软删除模型
use traits\model\SoftDelete;

class Pay extends Model
{
    //软删除
    use SoftDelete;

    //设置表名
    protected $name = 'pay';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    //定义软删除的字段
    protected $deleteTime = 'deletetime';


    //对转账截图图片路径处理 get+字段名+Attr
    public function getThumbsAttr($value)
    {
        if(empty($value))
        {
            return '';
        }else
        {
            //CDN后台静态文件的地址
            $cdn = rtrim(Config::get("SITEURL"),"/");
            // $value = ltrim($value,"/");
            // return $cdn.'/'.$value;
            $arr = explode(",",$value);

            foreach($arr as $key=>$item)
            {
                $arr[$key] = $cdn.'/'.$item;
            }

            return $arr;
        }
    }
}
