<?php

namespace app\common\model\Product;

use think\Model;
use \think\Config; //引入配置类

class Product extends Model
{
    //设置表名
    protected $name = 'product';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    //关联模型
    public function type()
    {
        return $this->belongsTo('app\common\model\Product\Type', 'typeid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //对图集路径处理 get+字段名+Attr
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
