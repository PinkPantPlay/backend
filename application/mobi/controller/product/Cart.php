<?php

namespace app\mobi\controller\Product;

use think\Controller;
use think\Request;
use think\Config;

//需要引入公共控制器
use app\common\controller\Mobi AS MobiController;

class Cart extends MobiController
{
    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->UserModel = model('common/User/User');
        $this->TypeModel = model('common/Product/Type');
        $this->ProductModel = model('common/Product/Product');
        $this->CartModel = model('common/Product/Cart');
        $this->AddressModel = model('common/User/Address');
    }

    /**
     * 添加到购物车里面去
     */
    public function add()
    {
        $userid = $this->request->post('userid',0);
        $proid = $this->request->post('proid',0);
        $nums = $this->request->post('nums',0);

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

        //判断商品是否有下架
        if(!$Product['status'])
        {
            $this->warning('商品已下架');
            exit;
        }

        if($nums <= 0)
        {
            $this->warning('商品数量不能小于0，请重新选择');
            exit;
        }

        //判断所选商品库存是否充足
        if($Product['stock'] < $nums)
        {
            $this->warning('库存不足');
            exit;
        }

        //先找购物记录是否存在
        $where = [
            'userid'=>$userid,
            'proid'=>$proid,
        ];

        $Cart = $this->CartModel->where($where)->find();

        if($Cart)
        {
            //说明存在商品记录，需要更新
            $data = [
                'userid'=>$userid,
                'proid'=>$proid,
                'nums'=>$nums,
                'nums'=>bcadd($nums,$Cart['nums']), //需要把原来的数据添加上
                'price'=>$Product['price'],
                'id'=>$Cart['id']
            ];

            //商品合计
            $data['total'] = bcmul($data['nums'], $data['price']);

            //更新语句
            $result = $this->CartModel->validate('common/Product/Cart')->isUpdate(true)->save($data);
        }else
        {
            //不存在购物记录需要添加购物车
            //添加到购物车去
            $data = [
                'userid'=>$userid,
                'proid'=>$proid,
                'nums'=>$nums,
                'price'=>$Product['price']
            ];

            //商品合计
            $data['total'] = bcmul($nums, $Product['price']);

            //插入数据库
            $result = $this->CartModel->validate('common/Product/Cart')->save($data);
        }

        if($result === FALSE)
        {
            $this->warning($this->CartModel->getError());
            exit;
        }else
        {
            //查询出购物车总数量
            $count = $this->CartModel->where(['userid'=>$userid])->sum('nums');

            $this->finish('添加购物车成功',['count'=>$count]);
            exit;
        }        
    }

    /**
     * 查询购物车数量
     */
    public function count()
    {
        $userid = $this->request->post('userid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //查询出购物车总数量
        $count = $this->CartModel->where(['userid'=>$userid])->sum('nums');

        $this->finish("返回购物车数量",['count'=>$count]);
        exit;
    }

    /**
     * 查询购物车列表
     */
    public function index()
    {
        $userid = $this->request->post('userid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //查询购物车数据
        $cartlist = $this->CartModel->with(['product'])->where(['userid'=>$userid])->select();

        if($cartlist)
        {
            $this->finish("返回购物车列表成功",$cartlist);
            exit;
        }else
        {
            $this->warning("暂无购物车数据");
            exit;
        }
    }

    /**
     * 更新购物车记录
     */
    public function edit()
    {
        $userid = $this->request->post('userid',0);
        $proid = $this->request->post('proid',0);
        $cartid = $this->request->post('cartid',0);
        $nums = $this->request->post('nums',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //购物车记录是否存在
        $Cart = $this->CartModel->find($cartid);

        if(!$Cart)
        {
            $this->warning('购物车记录不存在');
            exit;
        }

        //判断当前购物车记录是否属于这个人
        if($Cart['userid'] != $userid)
        {
            $this->warning('不是你的购物车记录');
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

        //判断商品是否有下架
        if(!$Product['status'])
        {
            $this->warning('商品已下架');
            exit;
        }

        if($nums <= 0)
        {
            $this->warning('商品数量不能小于0，请重新选择');
            exit;
        }

        //判断所选商品库存是否充足
        if($Product['stock'] < $nums)
        {
            $this->warning('库存不足');
            exit;
        }

        //更新购物车记录
        $data = [
            'nums'=>$nums,
            'id'=>$cartid,
        ];

        $total = bcmul($data['nums'], $Cart['price']);

        if($total < 0)
        {
            $this->warning('商品小计的金额不能小于0');
            exit;
        }

        $data['total'] = $total;

        //更新数据库
        $result = $this->CartModel->isUpdate(true)->save($data);

        //更新购物车失败
        if($result === FALSE)
        {
            $this->warning('更新购物车失败');
            exit;
        }else
        {
            $this->finish('更新购物车成功');
            exit;
        }
    }

    /**
     * 删除购物车记录
     */
    public function del()
    {
        $userid = $this->request->post('userid',0);
        $cartid = $this->request->post('cartid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //购物车记录是否存在
        $Cart = $this->CartModel->find($cartid);

        if(!$Cart)
        {
            $this->warning('购物车记录不存在');
            exit;
        }

        //判断当前购物车记录是否属于这个人
        if($Cart['userid'] != $userid)
        {
            $this->warning('不是你的购物车记录');
            exit;
        }

        //更新数据库
        $result = $this->CartModel->where(['id'=>$cartid])->delete();

        if($result === FALSE)
        {
            $this->warning('删除购物车失败');
            exit;
        }else
        {
            $this->finish('删除购物车成功');
            exit;
        }
    }

    /**
     * 确认订单的购物车列表查询
     */
    public function list()
    {
        $userid = $this->request->post('userid',0);
        $cartid = $this->request->post('cartid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //获取用户的默认收货地址
        $address = $this->AddressModel->with(['province','city','district'])->where(['userid'=>$userid,'status'=>'1'])->find();

        if(!$address)
        {
            $address = $this->AddressModel->with(['province','city','district'])->where(['userid'=>$userid])->find();
        }


        $where = [
            'id'=>['in',$cartid]
        ];

        //购物车记录是否存在
        $Cart = $this->CartModel->with(['product'])->select($where);

        //统计购物车数量的总金额
        $total = $this->CartModel->where($where)->sum('total');

        if(!$Cart)
        {
            $this->warning('购物车记录不存在');
            exit;
        }

        if($total < 0)
        {
            $this->warning('当前订单的价格不能够小于0');
            exit;
        }

        foreach($Cart as $item)
        {
            //只要购物车记录中有一条不相等的会员ID
            if($item['userid'] != $userid)
            {
                $this->warning("购物车记录中有存在别人记录");
                break;
            }
        }

        //返回数组
        $result = [
            'cartlist'=>$Cart,
            'address'=>$address,
            'total'=>$total
        ];

        $this->finish('返回购物车记录成功',$result);
        exit;
    }

    /**
     * 获取它所选择的收货地址
     */
    public function select()
    {
        $userid = $this->request->post('userid',0);
        $addrid = $this->request->post('addrid',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        $where = [
            'userid'=>$userid,
            'id'=>$addrid
        ];

        //查询收货地址
        $address = $this->AddressModel->where($where)->find();

        if($address)
        {
            $this->finish("查询收货地址成功",$address);
            exit;
        }else
        {
            $this->warning('暂未找到该收货地址');
            exit;
        }
    }

    /**
     * 验证支付密码是否正确
     */
    public function pay()
    {
        $userid = $this->request->post('userid',0);
        //支付密码
        $paypass = $this->request->post('paypass',0);

        //根据ID去查找用户是否存在
        $User = $this->UserModel->find($userid);

        //找不到
        if(!$User)
        {
            $this->warning("用户不存在");
            exit;
        }

        //支付密码盐
        $salt = $User['paysalt'];

        //加密后的结果
        $paypass = md5($paypass.$salt);

        if($User['paypass'] != $paypass)
        {
            $this->warning('支付密码错误，请重新输入');
            exit;
        }else
        {
            $this->finish('支付密码正确');
            exit;
        }
    }


}
