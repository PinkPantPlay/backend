<?php

namespace app\wxapi\controller\Repair;

use think\Controller;
use think\Request;

//需要引入公共控制器
use app\common\controller\WXAPI AS WXAPIController;

class Product extends WXAPIController
{
    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->TypeModel = model('common/Product/Type');
        $this->ProductModel = model('common/Product/Product');
        $this->UserModel = model('common/User/User');
        $this->RepairModel = model('common/Product/Repair');
    }


    /**
     * 添加报修方法
     */
    public function add()
    {
        $userid = $this->request->post('userid',0);
        $typeid = $this->request->post('typeid',0);
        $phone = $this->request->post('phone','');
        $content = $this->request->post('content','');

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        $Type = $this->TypeModel->find($typeid);

        if(!$Type)
        {
            $this->warning("该报修的商品类型不存在");
            exit;
        }

        //组装数据
        $data = [
            'userid'=>$userid,
            'typeid'=>$typeid,
            'phone'=>$phone,
            'content'=>$content,
            'status'=>0
        ];

        //插入数据库
        $result = $this->RepairModel->validate('common/Product/Repair')->save($data);

        if($result === FALSE)
        {
            //插入失败
            $this->warning($this->RepairModel->getError());
            exit;
        }else
        {
            //成功需要返回插入ID回去
            $data = ['repairid'=>$this->RepairModel->id];
            $this->finish('添加报修成功',$data);
            exit;
        }
    }


    /**
     * 上传报修图片
     */
    public function thumbs()
    {
        $repairid = $this->request->post('repairid',0);

        // var_dump($_REQUEST);

        // var_dump($repairid);
        // exit;
        
    }


}
