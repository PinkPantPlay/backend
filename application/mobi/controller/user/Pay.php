<?php

namespace app\mobi\controller\User;

use think\Controller;
use think\Request;

//需要引入公共控制器
use app\common\controller\Mobi AS MobiController;

class Pay extends MobiController
{
    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->UserModel = model('common/User/User');
        $this->PayModel = model('common/User/Pay');
    }

    /**
     * 添加充值记录
     */
    public function add()
    {
        //获取用户ID
        $userid = $this->request->post('userid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }
        
        //获取价格
        $price = $this->request->post('price',0);

        if($price <= 0)
        {
            $this->warning('充值的价格不能小于0');
            exit;
        }

        
        //组装数据
        $data = [
            'price'=>$price,
            'content'=>$this->request->post('content',""),
            'userid'=>$userid,
        ];


        //多文件上传
        $thumbs = build_uploads('thumbs');

        if(!empty($thumbs))
        {
            $str = implode(',',$thumbs);
            $data['thumbs'] = $str;
        }

        //插入数据库
        $result = $this->PayModel->validate("common/User/Pay.add")->save($data);

        if($result === FALSE)
        {
            //添加充值记录失败
            $this->warning($this->PayModel->getError());
            exit;
        }else
        {
            $this->finish('添加充值记录成功，请等待管理员审核');
            exit;
        }
    }

    public function list()
    {
        //获取用户ID
        $userid = $this->request->post('userid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //查询所有的充值记录
        $paylist = $this->PayModel->where(['userid'=>$userid])->select();

        if($paylist)
        {
            $this->finish('查询充值记录成功',$paylist);
            exit;
        }else
        {
            $this->warning('暂无充值记录');
            exit;
        }
    }
}
