<?php

namespace app\mobi\controller\Product;

use app\common\model\Product\Product;
use think\Controller;
use think\Request;
use think\Config;

//需要引入公共控制器
use app\common\controller\Mobi AS MobiController;

class Order extends MobiController
{
    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->UserModel = model('common/User/User');
        $this->UserRecordModel = model('common/User/Record');
        $this->AddressModel = model('common/User/Address');
        $this->CartModel = model('common/Product/Cart');
        $this->ProductModel = model('common/Product/Product');


        $this->OrderModel = model('common/Order/Order');
        $this->OrderProductModel = model('common/Order/Product');
    }

    /**
     * 下订单
     */
    public function add()
    {
        $userid = $this->request->post('userid',0);
        $cartid = $this->request->post('cartid',0);
        $addrid = $this->request->post('addrid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //判断是否存在收货地址
        $where = [
            'id'=>$addrid,
            'userid'=>$userid
        ];

        $address = $this->AddressModel->where($where)->find();

        //判断收货地址是否存在
        if(!$address)
        {
            $this->warning('收货地址不存在,请重新选择');
            exit;
        }

        //组装where条件 查询 订单记录
        $where = [
            'id'=>['in',$cartid]
        ];

        //订单记录是否存在
        $Cart = $this->CartModel->with(['product'])->select($where);

        //统计订单数量的总金额
        $total = $this->CartModel->where($where)->sum('total');

        if(!$Cart)
        {
            $this->warning('订单记录不存在');
            exit;
        }

        if($total < 0)
        {
            $this->warning('当前订单的价格不能够小于0');
            exit;
        }

        foreach($Cart as $item)
        {
            //只要订单记录中有一条不相等的会员ID
            if($item['userid'] != $userid)
            {
                $this->warning("订单记录中有存在别人记录");
                break;
            }

            //判断商品库存是否充足
            $UpdateStock = bcsub($item['product']['stock'],$item['nums']);

            if($UpdateStock < 0)
            {
                //说明库存不足
                $this->warning($item['product']['name'].' 商品库存不足');
                exit;
            }
        }


        //下订单 
        /*
            事务 订单事务   事务回滚  提交事务后 语句才能被执行
                1、判断钱是否够扣
                2、商品库存是否充足
                3、生成订单，订单商品表
                4、生成消费记录
                5、扣钱(更新用户表的字段price)
                6、减库存(更新product)
                7、删除订单的记录
        */

        $UpdatePrice = bcsub($User['money'],$total);

        //1、判断钱是否够扣
        if($UpdatePrice < 0)
        {
            $this->warning('余额不足请先充值');
            exit;
        }

        //创建事务
        $this->OrderModel->startTrans(); //开启订单表事务
        $this->OrderProductModel->startTrans(); //开启订单表事务
        $this->UserModel->startTrans(); //开启订单表事务
        $this->UserRecordModel->startTrans(); //开启订单表事务
        $this->CartModel->startTrans(); //开启订单表事务
        $this->ProductModel->startTrans(); //开启订单表事务

        //3、生成订单，订单商品表
        $OrderData = [
            'code'=>"SH".build_ranstr("10"), //订单号
            'userid'=>$userid,
            'addrid'=>$addrid,
            'total'=>$total,
            'content'=>$this->request->post('content',''),
            'status'=>1
        ];

        //插入订单表

        $OrderStatus = $this->OrderModel->validate('common/Order/Order')->save($OrderData);

        if($OrderStatus === FALSE)
        {
            $this->warning('订单生成失败');
            exit;
        }

        //1个订单 2个商品  订单表：1 订单商品表：2

        //组装订单商品表数据
        $OrderProData = [];

        foreach($Cart as $item)
        {
            $OrderProData[] = [
                'proid'=>$item['proid'],
                'nums'=>$item['nums'],
                'total'=>$item['total'],
                'orderid'=>$this->OrderModel->id //插入的新增ID
            ];
        }

        //插入订单商品数据
        $OrderProductStatus = $this->OrderProductModel->validate('common/Order/Product')->saveAll($OrderProData);

        if($OrderProductStatus === FALSE)
        {
            //进行回滚
            $this->OrderModel->rollback();
            // $this->warning("订单商品添加失败");
            $this->warning($this->OrderProductModel->getError());
            exit;
        }

        //组装数据
        $UserRecord = [
            'userid'=>$userid,
            'price'=>$total,
            'content'=>"您在".date("Y-m-d")."时间，消费金额为 $total ,该订单号为：".$OrderData['code'],
            'status'=>'1'
        ];

        //插入
        $RecordStatus = $this->UserRecordModel->validate('common/User/Record')->save($UserRecord);

        //消费记录没插入进去
        if($RecordStatus === FALSE)
        {
            //回滚
            $this->OrderProductModel->rollback();
            $this->OrderModel->rollback();
            $this->warning("消费记录添加失败");
            exit;
        }

        //更新用户余额
        $UserData = [
            'money'=>$UpdatePrice
        ];

        $UserStatus = $this->UserModel->where(['id'=>$userid])->update($UserData);

        if($UserStatus === FALSE)
        {
            //回滚
            $this->UserRecordModel->rollback();
            $this->OrderProductModel->rollback();
            $this->OrderModel->rollback();
            $this->warning("用户余额更新失败");
            exit;
        }

        //更新商品表库存
        $ProductData = [];

        foreach($Cart as $item)
        {
            //商品库存 - 购买数量
            $UpdateStock = bcsub($item['product']['stock'],$item['nums']);
            $ProductData[] = [
                'id'=>$item['proid'],
                'stock'=>$UpdateStock
            ];
        }

        //更新商品库存
        $ProductStatus = $this->ProductModel->isUpdate(true)->saveAll($ProductData);

        if($ProductStatus === FALSE)
        {
            //回滚
            $this->UserModel->rollback();
            $this->UserRecordModel->rollback();
            $this->OrderProductModel->rollback();
            $this->OrderModel->rollback();
            $this->warning("用户余额更新失败");
            exit;
        }

        //删除订单相应的记录
        $CartWhere = [
            'id'=>['in',$cartid]
        ];

        $CartStatus = $this->CartModel->where($CartWhere)->delete();

        if($CartStatus === FALSE)
        {
            //回滚
            $this->ProductModel->rollback();
            $this->UserModel->rollback();
            $this->UserRecordModel->rollback();
            $this->OrderProductModel->rollback();
            $this->OrderModel->rollback();
            $this->warning("用户余额更新失败");
            exit;
        }


        //如果所有的语句都能够顺利执行的话，那么就可以提交事务了
        $this->OrderModel->commit();
        $this->OrderProductModel->commit();
        $this->UserRecordModel->commit();
        $this->UserModel->commit();
        $this->ProductModel->commit();
        $this->CartModel->commit();
        $this->finish('支付成功，订单已生成，等待商家发货');
        exit;
    }

    /**
     * 查询订单列表
     */
    public function list(){
        $userid = $this->request->post('userid',0);
        $status = $this->request->post('status',10);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }
        //查询订单数据
        $orderlist = $this->OrderModel->where(['userid'=>$userid,'status'=>$status,'deletetime'=>NULL])->select();
        $orderid = [];
        foreach ($orderlist as $v){
            $orderid[] = $v['id'];
        }
        $orderpro = db('order_product')->where('orderid','in',$orderid)->select();
        $i = 0;
        $count = 0;
        foreach ($orderlist as $k=>$v){
            foreach ($orderpro as $v1){
                if ($v['id'] == $v1['orderid']){
                    $orderlist[$k]['pro'.$i]=$v1;
                    $count += $v1['nums'];
                    $i++;
                    $img = db('product')->field('cover')->where('id',$v1['proid'])->find();
                    $orderlist[$k]['img']=$img['cover'];
                }
                $orderlist[$k]['count'] = $count;
            }
            $i = 0;
            $count = 0;
        }

        if($orderlist)
        {
            $this->finish("返回订单列表成功",$orderlist);
            exit;
        }else
        {
            $this->warning("暂无数据");
            exit;
        }
    }
    /**
     * 删除订单
     */
    public function del(){
        $userid = $this->request->post('userid',0);
        $orderid = $this->request->post('ordersid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }
        $order = $this->OrderModel->where('id',$orderid)->find();
        $status = $order['status'];
        $res = $this->OrderModel::destroy($orderid);
        if($res)
        {
            $this->finish("成功","$status");
            exit;
        }else
        {
            $this->warning("失败");
            exit;
        }
    }
    /**
     * 查询订单商品
     */
    public function proList(){
        $userid = $this->request->post('userid',0);
        $orderid = $this->request->post('orderid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }
        $proid = [];
        $orderpro = db('order_product')->where('orderid',$orderid)->select();
        foreach($orderpro as $v){
            $proid[] = $v['proid'];
        }
        $pro = db('product')->where('id','in',$proid)->select();
        foreach($pro as $k=>$v){
            foreach($orderpro as $v1){
                if ($v['id'] == $v1['proid']){
                    $pro[$k]['nums'] = $v1['nums'];
                }
            }
        }
        if ($pro){
            $this->finish('成功',$pro);
        }else{
            $this->warning('请求数据有误');
        }
    }
    /**
     * 查询订单详情
     */
    public function details(){
        $userid = $this->request->post('userid',0);
        $orderid = $this->request->post('orderid',0);
        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }
        $details = db('order')->where('id',"$orderid")->find();
        $createtime = $details['createtime'];
        $createtime = date('Y-m-d H:i:s',$createtime);
        $details['createtime'] = $createtime;
        $status = $details['status'];
        if ($status == 0){
            $status = '待付款';
        }elseif($status == 1){
            $status = '待发货';
        }elseif($status == 2){
            $status = '待收货';
        }elseif($status == 3){
            $status = '待评价';
        }else{
            $status = '售后';
        }
        $details['status'] = $status;
//        var_dump($details);die;
        if ($details){
            $this->finish('成功',$details);
        }else{
            $this->warning('请求数据有误');
        }
    }
//    查询订单收件信息
    public function address(){
        $userid = $this->request->post('userid',0);
        $orderid = $this->request->post('orderid',0);
        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }
        $order = $this->OrderModel->where('id',$orderid)->find();
        $addrid = $order['addrid'];
        $address = $this->AddressModel->with(['province','city','district'])->where(['id'=>$addrid])->find();
        if ($address){
            $this->finish('成功',$address);
        }else{
            $this->warning('请求数据有误');
        }
    }
}
