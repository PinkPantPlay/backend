<?php


namespace app\admin\controller;

use think\Request;

class User extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->UserModel = model('common/User/User');

        $this->checkAuth();
    }

    /**
     * 会员列表
     */
    public function index(Request $request){
        // 条件查询
        $nickname = $request->get('nickname','');
        if ($nickname != NULL){
            $count = $this->UserModel->where('nickname', 'like', "%$nickname%")->count('id');
            $user = $this->UserModel->where('nickname', 'like', "%$nickname%")->select();
        }else{
            $count = $this->UserModel->count('id');
            $user = $this->UserModel->select();
        }
        foreach($user as $k=>$v){
            $gender = $v['gender'];
            if ($gender == 0){
                $gender = '保密';
            }elseif ($gender == 1){
                $gender = '男';
            }else{
                $gender = '女';
            }
            $user[$k]['gender'] = $gender;
        }
        $this->assign('user', $user);
        $this->assign('count', $count);
        return $this->fetch('user/index');
    }

    /**
     * 回收站
     */
    public function recycle(Request $request){
        $nickname = $request->get('nickname','');
        if ($nickname != NULL){
            $count = $this->UserModel::onlyTrashed()->where('nickname', 'like', "%$nickname%")->count('id');
            $user = $this->UserModel::onlyTrashed()->where('nickname', 'like', "%$nickname%")->select();
        }else{
            $count = $this->UserModel::onlyTrashed()->count('id');
            $user = $this->UserModel::onlyTrashed()->select();
        }
        foreach($user as $k=>$v){
            $gender = $v['gender'];
            if ($gender == 0){
                $gender = '保密';
            }elseif ($gender == 1){
                $gender = '男';
            }else{
                $gender = '女';
            }
            $user[$k]['gender'] = $gender;
        }
        $this->assign('user', $user);
        $this->assign('count', $count);
        return $this->fetch('user/recycle');
    }

    /**
     * 软删除一条数据
     */
    public function deleteone(Request $request){
        $id = $request->get('id');
        $user = $this->UserModel::get($id);
        $result = $user->delete();
        if ($result === FALSE){
            $this->warning('删除失败');
        }else{
            $this->finish('删除成功');
        }
    }

    /**
     * 软删除选中的数据
     */
    public function deleteall(Request $request){
        //检查权限
        //code
        $data = $request->param('data');
        $result = $this->UserModel->destroy($data);
        if ($result === FALSE){
            $this->warning('删除失败');
        }else{
            $this->finish('删除成功');
        }
    }

    /**
     * 真实删除一条数据
     */
    public function delone(Request $request){
        // 检查权限
        // code
        $id = $request->param('id');
        $user = $this->UserModel::onlyTrashed()->where('id', $id)->find();
        $result = $user->delete(true);
        if ($result === FALSE){
            $this->warning('删除失败');
        }else{
            $this->finish('删除成功');
        }
    }

    /**
     * 真实删除选中的数据
     */
    public function delall(Request $request){
        //检查权限
        //code
        $data = $request->param('data');
        $result = $this->UserModel::destroy($data,true);
        if ($result === FALSE){
            $this->warning('删除失败');
        }else{
            $this->finish('删除成功');
        }
    }

    /**
     * 回收站批量恢复数据
     */
    public function restoreAll(Request $request){
        //检查权限
        //code
        $data = $request->param('data');
//        var_dump($data);exit;
        $data = explode(",",$data);
        $user = $this->UserModel::onlyTrashed()->select($data);
        $resultCount = 0;
        foreach ($user as $v){
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
}