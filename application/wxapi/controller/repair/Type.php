<?php

namespace app\wxapi\controller\Repair;

use think\Controller;
use think\Request;

//需要引入公共控制器
use app\common\controller\WXAPI AS WXAPIController;

class Type extends WXAPIController
{
    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->TypeModel = model('common/Product/Type');
        $this->ProductModel = model('common/Product/Product');
    }

    /**
     * 查询所有的商品分类数据
     */
    public function list()
    {
        //关联模型
        $typelist = collection($this->TypeModel->with(['product'])->order("weight")->select())->toArray();

        if($typelist)
        {
            $this->finish("商品分类数据",$typelist);
            exit;
        }else
        {
            $this->warning('暂无商品分类数据');
            exit;
        }
    }
}
