<?php

namespace app\wxapi\controller\User;

use think\Controller;
use think\Request;

//需要引入公共控制器
use app\common\controller\WXAPI AS WXAPIController;

class Address extends WXAPIController
{
    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->UserModel = model('common/User/User');
        $this->AddressModel = model('common/User/Address');
        $this->RegionModel = model('common/Common/Region');
    }

    /**
     * 收货地址列表查询
     */
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

        //查询当前用户的收货地址  with 调用模型文件中 关联方法
        $address = $this->AddressModel->with(['province','city','district'])->where(['userid'=>$userid])->select();

        if($address)
        {
            $this->finish('查询数据成功',$address);
            exit;

        }else
        { 
            $this->warning('暂无收货地址记录');
            exit;
        }

    }

    /**
     * 设置默认收货地址
     */
    public function set()
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

        //获取收货地址ID
        $addrid = $this->request->post('addrid',0);

        //根据ID去查找用户是否存在
        $Address = $this->AddressModel->find($addrid);

        //找不到
        if(!$Address)
        {
            $this->warning("地址不存在,请重新选择");
            exit;
        }

        //判断当前的这条收货地址是不是你本人的吧
        if($Address['userid'] != $userid)
        {
            $this->warning('你改别人的地址干嘛，是不是有问题了');
            exit;
        }

        //要把当前的这一条设置为默认的收货地址 但是前提是需要把其他的设置为非默认
        //将当前这个人的所有地址记录全部设置为非默认的
        $this->AddressModel->where(['userid'=>$userid])->update(['status'=>0]);

        //设置当前这条为默认地址
        $result = $this->AddressModel->where(['userid'=>$userid,'id'=>$addrid])->update(['status'=>1]);

        if($result === FALSE)
        {
            //设置失败
            $this->warning('设置默认收货地址失败');
            exit;
        }else
        {
            $this->finish('设置默认收货地址成功');
            exit;
        }
    }

    /**
     * 返回收货地址的方法
     */
    public function info()
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

        //获取收货地址ID
        $addrid = $this->request->post('addrid',0);

        //根据ID去查找用户是否存在
        $Address = $this->AddressModel->with(['province','city','district'])->find($addrid);

        //找不到
        if(!$Address)
        {
            $this->warning("地址不存在,请重新选择");
            exit;
        }

        //判断当前的这条收货地址是不是你本人的吧
        if($Address['userid'] != $userid)
        {
            $this->warning('你改别人的地址干嘛，是不是有问题了');
            exit;
        }else
        {
            $this->finish('返回地址信息成功',$Address);
            exit;
        }
    }

    /**
     * 添加收货地址
     */
    public function add()
    {
        //获取所有的post数据
        $params = $this->request->post();

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

        //根据传递过来的code地区码参数来找出他的省市区的值
        $code = $this->request->post('region',0);

        //将对象转化为数组 
        $area = $this->RegionModel->where(['code'=>$code])->find();

        //当地区不存在的时候返回错误信息
        if(!$area)
        {
            $this->warning("所选查找地区不存在，请重新选择");
            exit;
        }

        //组装数据，插入到收货地址表里面
        $data = [
            'userid'=>$userid,
            'consignee'=>$params['consignee'],
            'address'=>$params['address'],
            'mobile'=>$params['mobile'],
            'status'=>$params['status'] ? 1 : 0,
        ];

        //区域路径进行转换
        $path = explode(',',$area['parentpath']);

        if(count($path) == 2)
        {
            list($data['province'], $data['city']) = $path;
        }else if(count($path) == 3)
        {
            list($data['province'], $data['city'], $data['district']) = $path;
        }

        //判断是否设置了默认收货地址,如果设置默认那就把他原有的默认地址全部改成非默认地址
        if($data['status'])
        {
            //将当前这个人的所有地址记录全部设置为非默认的
            $this->AddressModel->where(['userid'=>$userid])->update(['status'=>0]);
        }

        //插入到数据库里面
        $result = $this->AddressModel->validate("common/User/Address.add")->save($data);

        if($result === FALSE)
        {
            //插入失败
            $this->warning($this->AddressModel->getError());
            exit;
        }else
        {
            $this->finish('添加收货地址成功');
            exit;
        }
    }

    /**
     * 编辑收货地址
     */
    public function edit()
    {
        //获取所有的post数据
        $params = $this->request->post();

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

        //获取收货地址ID
        $addrid = $this->request->post('addrid',0);

        //根据ID去查找用户是否存在
        $Address = $this->AddressModel->with(['province','city','district'])->find($addrid);

        //找不到
        if(!$Address)
        {
            $this->warning("地址不存在,请重新选择");
            exit;
        }

        //判断当前的这条收货地址是不是你本人的吧
        if($Address['userid'] != $userid)
        {
            $this->warning('你改别人的地址干嘛，是不是有问题了');
            exit;
        }

        //根据传递过来的code地区码参数来找出他的省市区的值
        $code = $this->request->post('areaCode',0);

        //将对象转化为数组 
        $area = $this->RegionModel->where(['code'=>$code])->find();

        //当地区不存在的时候返回错误信息
        if(!$area)
        {
            $this->warning("所选查找地区不存在，请重新选择");
            exit;
        }

        //组装数据，插入到收货地址表里面
        $data = [
            'id'=>$addrid,
            'userid'=>$userid,
            'consignee'=>$params['name'],
            'address'=>$params['addressDetail'],
            'mobile'=>$params['tel'],
            'status'=>$params['isDefault'] ? 1 : 0,
        ];

        //区域路径进行转换
        $path = explode(',',$area['parentpath']);

        if(count($path) == 2)
        {
            list($data['province'], $data['city']) = $path;
        }else if(count($path) == 3)
        {
            list($data['province'], $data['city'], $data['district']) = $path;
        }

        //判断是否设置了默认收货地址,如果设置默认那就把他原有的默认地址全部改成非默认地址
        if($data['status'])
        {
            //将当前这个人的所有地址记录全部设置为非默认的
            $this->AddressModel->where(['userid'=>$userid])->update(['status'=>0]);
        }

        //更新到数据库里面
        $result = $this->AddressModel->validate("common/User/Address.edit")->isUpdate(true)->save($data);

        if($result === FALSE)
        {
            //插入失败
            $this->warning($this->AddressModel->getError());
            exit;
        }else
        {
            $this->finish('编辑收货地址成功');
            exit;
        }
    }
}
