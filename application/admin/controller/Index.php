<?php
namespace app\admin\controller;

use \think\Controller;

class Index extends Controller
{
    /**
     * 默认模板界面
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 后面控制面板
     */
    public function main()
    {
        $order = db('order')->count('id');
        $user = db('user')->count('id');
        $pay = db('pay')->count('id');
        $product = db('product')->count('id');
        $product_type = db('product_type')->count('id');
        $this->assign('order',$order);
        $this->assign('user',$user);
        $this->assign('pay',$pay);
        $this->assign('product',$product);
        $this->assign('product_type',$product_type);
        return $this->fetch();
    }


}
