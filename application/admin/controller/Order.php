<?php


namespace app\admin\controller;

use think\Request;

class Order extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->UserModel = model('common/User/User');
        $this->ProductModel = model('common/Product/Product');
        $this->OrderProductModel = model('common/Order/Product');
        $this->AddressModel = model('common/User/Address');
        $this->OrderModel = new \app\admin\model\Order\Order();
    }

    /**
     * 订单列表
     */
    public function index(Request $request){
        // 条件查询
        $get = $request->get();
        $get = array_filter($get);
        $where = [];
        if (isset($get['start']) && isset($get['end'])){
            $start = strtotime($get['start']);
            $end = strtotime($get['end']);
            $where['createtime'] = array('between',"$start,$end");
        }
        if (isset($get['start'])){
            $start = strtotime($get['start']);
            $where['createtime'] = array('>',"$start");
        }
        if (isset($get['end'])){
            $end = strtotime($get['end']);
            $where['createtime'] = array('<',"$end");
        }
        if (isset($get['status'])){
            $status = intval($get['status']);
            if ($status == 4){
                $where['o.status'] = array('eq',-1);
            }elseif($status == 5) {
                $where['o.status'] = array('eq',0);
            }else{
                $where['o.status'] = array('eq',"$status");
            }
        }
        if (isset($get['code'])){
            $code = $get['code'];
            $where['code'] = array('like',"%$code%");
        }
        $order = $this->OrderModel::alias('o')
            ->join('user_address a','o.addrid=a.id')
            ->field('o.*,a.consignee,a.mobile,a.address')
            ->where($where)
            ->select();
        // 修改状态
        foreach($order as $k=>$v){
            $status = $v['status'];
            if($status == 0){
                $status = '待付款';
            }elseif ($status == 1){
                $status = '待发货';
            }elseif($status == 2){
                $status = '待收货';
            }elseif ($status == 3){
                $status = '待评价';
            }else{
                $status = '退款';
            }
            $order[$k]['status'] = $status;
        }
        $this->assign('order',$order);
        return $this->fetch('order/index');
    }

    /**
     * 回收站
     */
    public function recycle(){
        $order = $this->OrderModel::onlyTrashed()
            ->alias('o')
            ->join('user_address a','o.addrid=a.id')
            ->field('o.*,a.consignee,a.mobile,a.address')
            ->select();
        // 修改状态
        foreach($order as $k=>$v){
            $status = $v['status'];
            if($status == 0){
                $status = '待付款';
            }elseif ($status == 1){
                $status = '待发货';
            }elseif($status == 2){
                $status = '待收货';
            }elseif ($status == 3){
                $status = '待评价';
            }else{
                $status = '退款';
            }
            $order[$k]['status'] = $status;
        }
        $this->assign('order',$order);
        return $this->fetch('order/recycle');
    }
    /**
     * 订单详情
     */
    public function detail(Request $request){
        $data = $request->param();
        $w = $data['w'];
        $where = [
            'orderid' => $data['id']
        ];
        $pro = $this->OrderProductModel->with(['pro'])->where($where)->select();
        foreach($pro as $k=>$v){
            $name = $v->pro->name;
            $price = $v->pro->price;
            $pro[$k]['name'] = $name;
            $pro[$k]['price'] = $price;
        }
//        echo '<pre>';print_r($pro[0]);echo '</pre>';
        $this->assign('pro',$pro);
        $this->assign('w',$w);
        return $this->fetch('order/detail');
    }
    /**
     * 软删除一条数据
     */
    public function deleteone(Request $request){
        $id = $request->get('id');
        $order = $this->OrderModel::get($id);
        $result = $order->delete();
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
        $result = $this->OrderModel->destroy($data);
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
        $order = $this->OrderModel::onlyTrashed()->find($id);
        $result = $order->delete(true);
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
        $result = $this->OrderModel::destroy($data,true);
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
        $order = $this->OrderModel::onlyTrashed()->select($data);
        $resultCount = 0;
        foreach ($order as $v){
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