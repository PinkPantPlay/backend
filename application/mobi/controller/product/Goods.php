<?php

namespace app\mobi\controller\Product;

use think\Controller;
use think\Request;
use think\Config;

//需要引入公共控制器
use app\common\controller\Mobi AS MobiController;

class Goods extends MobiController
{
    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->UserModel = model('common/User/User');
        $this->TypeModel = model('common/Product/Type');
        $this->ProductModel = model('common/Product/Product');
        $this->CollectionModel = model('common/User/Collection');
    }

    /**
     * 商品详情
     */
    public function info()
    {
        //获取商品ID
        $proid = $this->request->post('proid',0);

        //条件 等于当前ID 商品还得是上架

        //查询商品是否存在  关联查询分类表
        $product = $this->ProductModel->with(['type'])->find($proid)->toArray();

        if(!$product)
        {
            $this->warning("商品不存在");
            exit;
        }

        //判断商品是否上架
        if(!$product['status'])
        {
            $this->warning('该商品已经下架了');
            exit;
        }

        //判断该商品是否有绑定客服手机号码
        if(empty($product['phone']))
        {
            $product['phone'] = Config::get("CONTACT"); 
        }

        $this->finish('返回商品数据',$product);
    }

    /**
     * 查询是否收藏了产品
     */
    public function collection()
    {
        $userid = $this->request->post('userid',0);
        $proid = $this->request->post('proid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //根据ID去查找商品是否存在
        $Product = $this->ProductModel->find($proid);

        //找不到
        if(!$Product)
        {
            $this->warning("商品不存在");
            exit;
        }

        //查询是否有收藏产品
        $where = [
            'userid'=>$userid,
            'proid'=>$proid
        ];

        $Collection = $this->CollectionModel->where($where)->find();

        if($Collection)
        {
            $this->finish('您已经收藏了该商品');
            exit;
        }else
        {
            $this->warning('未收藏该商品');
            exit;
        }
    }
    

    /**
     * 添加和取消收藏
     */
    public function colactive()
    {
        $userid = $this->request->post('userid',0);
        $proid = $this->request->post('proid',0);
        $active = $this->request->post('active',false);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //根据ID去查找商品是否存在
        $Product = $this->ProductModel->find($proid);

        //找不到
        if(!$Product)
        {
            $this->warning("商品不存在");
            exit;
        }

        //判断是插入还是删除
        if($active)
        {
            //插入 添加收藏
            $data = [
                'userid'=>$userid,
                'proid'=>$proid
            ];
            
            $result = $this->CollectionModel->validate('common/User/Collection')->save($data);

            if($result === FALSE)
            {
                $this->warning('添加收藏失败');
                exit;
            }else
            {
                $this->finish('添加收藏成功');
                exit;
            }
        }else
        {
            //删除 收藏
            $where = [
                'userid'=>$userid,
                'proid'=>$proid
            ];

            //删除收藏
            $result = $this->CollectionModel->where($where)->delete();

            if($result === FALSE)
            {
                $this->warning('取消收藏失败');
                exit;
            }else
            {
                $this->finish("取消收藏成功");
                exit;
            }
        }
    }


}
