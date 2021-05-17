<?php

namespace app\mobi\controller\Product;

use think\Controller;
use think\Request;

//需要引入公共控制器
use app\common\controller\Mobi AS MobiController;

class Type extends MobiController
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
    public function index()
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

    /**
     * 商品列表页面的分类查询
     */
    public function all()
    {
        $typelist = $this->TypeModel->order("weight")->select();

        if($typelist)
        {
            $this->finish("返回商品分类数据", $typelist);
            exit;
        }else
        {
            $this->warning("暂无商品分类数据");
            exit;
        }
    }

    /**
     * 查询商品列表
     */
    public function list()
    {
        $typeid = $this->request->post('typeid', 0);

        //属性参数
        $flag = $this->request->post('flag', 'all');

        //排序参数
        $order = $this->request->post('order', 'createtime');

        //关键词
        $keywords = $this->request->post('keywords', '');

        //页码值
        $page = $this->request->post('page', 1);

        //每页显示多少条
        $limit = 10;

        //判断有没有这个商品分类
        $Type = $this->TypeModel->find($typeid);

        if(!$Type)
        {
            $this->warning("商品分类不存在");
            exit;
        }

        //偏移量
        $start = ($page-1)*$limit;


        //组装条件
        $where = [
            'typeid'=>$typeid,
        ];

        if($flag != "all")
        {
            $where['flag'] = $flag;
        }

        //对排序进行判断
        $OrderArr = ['createtime','price','stock'];

        //判断你所排序的字段是否在 允许的数组内  如果不存在就给个默认值
        if(!in_array($order,$OrderArr))
        {
            $order = "createtime";
        }

        //关键词查询判断
        if(!empty($keywords))
        {
            $where['name'] = ['like',"%$keywords%"];
        }

        //查询商品列表
        $prolist = $this->ProductModel->where($where)->order($order,"desc")->limit($start,$limit)->select();

        if($prolist)
        {
            $this->finish("返回商品列表", $prolist);
            exit;
        }else
        {
            $this->warning("暂无商品数据");
            exit;
        }
    }
}
