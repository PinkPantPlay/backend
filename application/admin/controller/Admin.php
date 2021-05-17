<?php


namespace app\admin\controller;


use think\Controller;
use think\Request;
use think\db;

class Admin extends Base
{
    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->AdminModel = model('admin/Admin/Admin');
    }

    public function index(Request $request){
        $where = $request->param('adminname');
        if (!empty($where)){
            $admin = db('admin')->field('id,adminname,phone,character,createtime,status,deletetime')->where('deletetime','NULL')->where('adminname','like',"%$where%")->select();
        }else{
            $admin = db('admin')->field('id,adminname,phone,character,createtime,status,deletetime')->where('deletetime','NULL')->select();
        }
        foreach ($admin as $k=>$v){
            $createtime = $v['createtime'];
            $createtime = date('Y-m-d H:i:s',$createtime);
            $admin[$k]['createtime'] = $createtime;
        }
        $count = $this->AdminModel->count('id');
        $this->assign('admin',$admin);
        $this->assign('count',$count);
        return $this->fetch('admin/index');
    }
    public function recycle(request $request){
        $where = $request->param('adminname');
        if (!empty($where)){
            $admin = $this->AdminModel::onlyTrashed()->field('id,adminname,phone,character,createtime,status,deletetime')->where('adminname','like',"%$where%")->select();
        }else{
            $admin = $this->AdminModel::onlyTrashed()->field('id,adminname,phone,character,createtime,status,deletetime')->select();
        }
        foreach ($admin as $k=>$v){
            $deletetime = $v['deletetime'];
            $deletetime = date('Y-m-d H:i:s',$deletetime);
            $admin[$k]['deletetime'] = $deletetime;
        }
        $this->assign('admin',$admin);
        return $this->fetch('admin/recycle');
    }

    /**
     * 添加管理员
     * 仅超级管理员可进行添加操作
     */
    public function add(){

//        判断当前用户是否为超级管理员
        //code

        return $this->fetch('admin/add');
    }
    public function adminadd(Request $request){
        $post = $request->param();

//        判断当前用户是否为超级管理员
        //code

        // 组装数据
        $password = $post['password'];
        //密码盐 一串随机的字符串
        $salt = build_ranstr(10);
        $repass = md5($password.$salt);
        $adminname = $this->AdminModel->where('adminname',$post['adminname'])->find();
        if ($adminname){
            $this->warning('登录名已经存在，请重新输入');
            exit;
        }
        $data = [
            'adminname' => $post['adminname'],
            'phone' => $post['phone'],
            'character' => $post['character'],
            'createtime' => time(),
            'status' => 0,
            'password' => $repass,
            'salt' => $salt
        ];
//        echo '<pre>';print_r($data);echo '</pre>';die;
        $result = $this->AdminModel->validate('admin/Admin.add')->insert($data);
        if ($result == false){
            $this->warning($this->AdminModel->getError());
        }else{
            $this->finish('添加成功');
        }
    }

    /**
     * 修改管理员状态
     * 仅超级管理员可修改此状态
     */
    public function status(Request $request){
        $data = $request->param();

        // 检查当前用户是否有操作权限
        // code

        $result = $this->AdminModel->isUpdate(true)->save($data);
        if ($result === FALSE){
            $this->warning('修改失败');
        }else{
            $this->finish('修改成功');
        }
    }

    /**
     * 删除管理员记录
     * 仅超级管理员可删除
     */
    public function deleteone(Request $request){

        // 检查用户操作权限
        // code

        $id = $request->param('id');
        $admin = $this->AdminModel::get($id);
        $result = $admin->delete();
        if ($result === FALSE){
            $this->warning('删除失败');
        }else{
            $this->finish('删除成功');
        }
    }
    public function delone(Request $request){
        // 检查权限
        // code
        $id = $request->param('id');
        $admin = $this->AdminModel::onlyTrashed()->where('id', $id)->find();
        $result = $admin->delete(true);
        if ($result === FALSE){
            $this->warning('删除失败');
        }else{
            $this->finish('删除成功');
        }
    }
    public function deleteall(Request $request){
        //检查权限
        //code
        $data = $request->param('data');
        $result = $this->AdminModel->destroy($data);
        if ($result === FALSE){
            $this->warning('删除失败');
        }else{
            $this->finish('删除成功');
        }
    }
    public function delall(Request $request){
        //检查权限
        //code
        $data = $request->param('data');
        $result = $this->AdminModel::destroy($data,true);
        if ($result === FALSE){
            $this->warning('删除失败');
        }else{
            $this->finish('删除成功');
        }
    }
    public function restoreAll(Request $request){
        //检查权限
        //code
        $data = $request->param('data');
//        var_dump($data);exit;
        $data = explode(",",$data);
        $admin = $this->AdminModel::onlyTrashed()->select($data);
        $resultCount = 0;
        foreach ($admin as $v){
            $result = $v->restore();
            $resultCount += $result;
        }
        if ($resultCount === sizeof($data)){
            $this->finish('恢复成功');
        }else{
            $f = $data-$resultCount;
            $this->warning('共有'.$f.'条数据恢复失败');
        }
    }

    /**
     * 管理员编辑
     */
    public function edit(Request $request){
        //检查权限
        //code
        $id = $request->param('id');
        $admin = $this->AdminModel->where('id',$id)->field('id,adminname,character,status')->find();
        $this->assign('admin',$admin);
        return $this->fetch('admin/edit');
    }
    public function adminedit(Request $request){
        //检查权限
        //code
        $post = $request->param();
        $data = [];
        $data['character'] = $post['character'];
        $data['status'] = $post['status'];
        $data['id'] = $post['id'];
        $result = $this->AdminModel->validate('admin/Admin.edit')->isUpdate(true)->save($data);
        if ($result === FALSE){
            $this->warning('管理员信息编辑失败');
        }else{
            $this->finish('更新成功');
        }
    }
}