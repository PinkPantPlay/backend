<?php

namespace app\mobi\controller\User;

use think\Controller;
use think\Request;

//需要引入公共控制器
use app\common\controller\Mobi AS MobiController;

class Base extends MobiController
{
    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->UserModel = model('common/User/User');
        $this->CollectionModel = model('common/User/Collection');
        $this->ProductModel = model('common/Product/Product');
    }

    /**
     * 用户注册
     */
    public function register()
    {
        //接收vue传递过来的参数
        $phone = $this->request->post('phone','');

        //查询
        $User = $this->UserModel->where(['phone'=>$phone])->find();

        //如果查询到了数据 就说明已经注册了
        if($User)
        {
            $this->warning('该手机号码已注册，请直接登录');
            exit;
        }

        //如果不存在就插入数据库
        $password = $this->request->post('password','');

        //密码盐 一串随机的字符串
        $salt = build_ranstr(10);

        //加密的密码  密码+盐
        $repass = md5($password.$salt);

        //组装数据
        $data = [
            'phone'=>$phone,
            'password'=>$repass,
            'salt'=>$salt,
        ];

        //插入到数据库 save方法 既可以插入也可以更新
        $result = $this->UserModel->validate('common/User/User.add')->save($data);

        if($result === FALSE)
        {
            //插入数据失败
            $this->warning($this->UserModel->getError());
            exit;
        }else
        {
            //插入成功
            $this->finish('注册成功，请跳转到登录界面');
            exit;
        }
    }

    /**
     * 用户登录
     */
    public function login()
    {
        //接收vue传递过来的参数
        $phone = $this->request->post('phone','');

        //查询
        $User = $this->UserModel->where(['phone'=>$phone])->find();

        if(!$User)
        {
            $this->warning("手机号码错误，未找到该用户");
            return false;
        }

        //验证密码
        $password = $this->request->post('password','');

        //获取密码盐
        $salt = $User['salt'];

        //将密码和密码盐加密
        $repass = md5($password.$salt);

        if($repass == $User['password'])
        {
            $data = [
                'id'=>$User['id'],
                'nickname'=>$User['nickname'],
                'phone'=>$User['phone'],
                'avatar'=>$User['avatar'],
                'gender'=>$User['gender'],
            ];
            //密码正确,登录成功
            $this->finish('登录成功，跳转到会员中心', $data);
            exit;
        }else
        {
            //密码错误
            $this->warning("密码错误");
            exit;
        }
    }

    /**
     * 验证用户信息是否有效
     */
    public function auth()
    {
        //获取用户id
        $id = $this->request->post('id',0);
        $phone = $this->request->post('phone','');

        //根据ID和手机号码来判断这个人是否存在
        $where = [
            'id'=>$id,
            'phone'=>$phone
        ];
        
        //获取用户信息
        $User = $this->UserModel->where($where)->find();

        if($User)
        {
            //这个人存在的时候，将这个人最新的信息返回回去

            $data = [
                'id'=>$User['id'],
                'nickname'=>$User['nickname'],
                'phone'=>$User['phone'],
                'avatar'=>$User['avatar'],
                'gender'=>$User['gender'],
            ];
            
            $this->finish('验证成功', $data);
            exit;
        }else
        {
            //非法登录
            $this->warning('非法登录');
            exit;
        }
    }

    /**
     * 修改个人资料
     */
    public function profile()
    {
        //接收vue传递过来的参数
        $phone = $this->request->post('phone','');
        $id = $this->request->post('id',0);

        $where = [
            'phone'=>$phone,
            'id'=>['<>',$id]
        ];

        //查询是否有重复的手机号 但是不能包括自己
        $User = $this->UserModel->where($where)->find();

        //如果查询到了数据 就说明已经注册了
        if($User)
        {
            $this->warning('该手机号码已存在');
            exit;
        }

        $params = $this->request->post();

        //组装数据
        $data = [
            'nickname'=>$params['nickname'],
            'phone'=>$phone,
            'gender'=>$params['gender'],
            'id'=>$id
        ];
        $avatar = build_uploads('avatar');
        if(!empty($avatar))
        {
            $str = implode(',',$avatar);
            $data['avatar'] = $str;
        }

        //更新数据库
        $result = $this->UserModel->validate('common/User/User.profile')->isUpdate(true)->save($data);

        if($result === FALSE)
        {
            //更新失败
            $this->warning($this->UserModel->getError());
            exit;
        }else
        {
            $User = $this->UserModel->find($id);

            $data = [
                'id'=>$User['id'],
                'nickname'=>$User['nickname'],
                'phone'=>$User['phone'],
                'avatar'=>$User['avatar'],
                'gender'=>$User['gender'],
            ];


            //更新成功
            $this->finish("更新个人资料成功", $data);
            exit;
        }
    }
//    修改密码
    public function changepwd()
    {
//        接收vue传递过来的参数
        $id = $this->request->post('id',0);
        $oldpwd = $this->request->post('oldpwd','');
//        $params = $this->request->post();
//        var_dump($params);exit;
//        echo $oldpwd;exit;
        $password = $this->request->post('password','');
        $User = $this->UserModel->where('id',$id)->find();
        //获取密码盐
        $salt = $User['salt'];
//        echo $salt;exit;
        $repass = md5($oldpwd.$salt);
//        echo $repass;exit;
//        检查旧密码是否正确
        if ($User['password'] != $repass){
            $this->warning('原密码输入有误');
            exit;
        }

        //密码盐 一串随机的字符串
        $salt = build_ranstr(10);

        //将密码和密码盐加密
        $repass = md5($password.$salt);

        //组装数据
        $data = [
            'password'=>$repass,
            'salt'=>$salt,
            'id'=>$id
        ];
        //更新数据库
        $result = $this->UserModel->validate('common/User/User.changepwd')->isUpdate(true)->save($data);
        if ($result === FALSE){
            $this->warning('更新密码失败');
        }else{
            $this->finish('更新密码成功');
        }
    }
    //    修改支付密码
    public function paymentpwd()
    {
//        接收vue传递过来的参数
        $id = $this->request->post('id',0);
        $oldpwd = $this->request->post('oldpwd','');
//        $params = $this->request->post();
//        var_dump($params);exit;
//        echo $oldpwd;exit;
        $paypass = $this->request->post('paypass','');
        $User = $this->UserModel->where('id',$id)->find();
        //获取密码盐
        $paysalt = $User['paysalt'];
//        echo $paysalt;exit;
        $repass = md5($oldpwd.$paysalt);
//        echo $repass;exit;
//        检查旧密码是否正确
        if ($User['paypass'] != $repass){
            $this->warning('原密码输入有误');
            exit;
        }

        //密码盐 一串随机的字符串
        $paysalt = build_ranstr(10);

        //将密码和密码盐加密
        $repass = md5($paypass.$paysalt);

        //组装数据
        $data = [
            'paypass'=>$repass,
            'paysalt'=>$paysalt,
            'id'=>$id
        ];
        //更新数据库
//        $result = $this->UserModel->isUpdate(true)->save($data);

        $result = $this->UserModel->validate('common/User/User.paymentpwd')->isUpdate(true)->save($data);
//        echo $result;exit;
        if ($result === FALSE){
            $this->warning('更新支付密码失败');
        }else{
            $this->finish('更新支付密码成功');
        }
    }
//    收藏列表
    public function favlist(){
        $userid = $this->request->post('userid',0);
        $collection = db('user_collection')->alias('c')->join('product p','c.proid=p.id')
            ->where('c.userid',$userid)
            ->select();
        if($collection){
            return $this->finish('查询成功',$collection);
        }else{
            return $this->warning('数据查询有误');
        }
    }

}
