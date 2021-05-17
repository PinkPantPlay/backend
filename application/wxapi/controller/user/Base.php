<?php
namespace app\wxapi\controller\User;

//需要引入公共控制器
use app\common\controller\WXAPI AS WXAPIController;

class Base extends WXAPIController
{

    public function __construct()
    {
        parent::__construct();

        //获取模型
        $this->UserModel = model('common/User/User');
    }

    public function login()
    {
        $code = $this->request->post('code','');
        $phone = $this->request->post('phone','');
        $paypass = $this->request->post('paypass','');

        if(empty($code))
        {
            //返回错误信息
            $this->warning('登录凭证获取失败，请重新授权登录');
            exit;
        }

        //请求微信的接口进行openid 换取
        $result = $this->code2Session($code);

        if(!$result)
        {
            $this->warning('授权微信服务器失败');
            exit;
        }

        //微信的微信凭证 openid
        $openid = isset($result['openid']) ? $result['openid'] : '';

        if(empty($openid))
        {
            $this->warning('微信授权凭证失败');
            exit;
        }

        //获取用户信息
        $User = $this->UserModel->where(['openid'=>$openid])->find();

        //如果有就是存在
        if($User)
        {
            $data = [
                'id'=>$User['id'],
                'nickname'=>$User['nickname'],
                'phone'=>$User['phone'],
                'avatar'=>$User['avatar'],
                'gender'=>$User['gender'],
            ];

            //密码正确,登录成功
            $this->finish('授权登录成功', $data);
            exit;

        }



        //它现在是在完善信息界面
        if(!empty($phone) && !empty($paypass))
        {
            //根据手机号和支付密码
            $User = $this->UserModel->where(['phone'=>$phone])->find();

            //如果要是找到这个人 就执行更新语言
            if($User)
            {
                //验证支付密码是否正确
                $paysalt = $User['paysalt'];
                $repass = md5($paypass.$paysalt);

                if($User['paypass'] != $repass)
                {
                    $this->warning('您所绑定账号的支付密码错误,请重新输入');
                    return false;
                }

                //更新openid
                $data = [
                    'openid'=>$openid,
                    'id'=>$User['id']
                ];

                $result = $this->UserModel->isUpdate(true)->save($data);

                if($result === FALSE)
                {
                    $this->warning($this->UserModel->getError());
                    exit;
                }else
                {
                    $data = [
                        'id'=>$User['id'],
                        'nickname'=>$User['nickname'],
                        'phone'=>$User['phone'],
                        'avatar'=>$User['avatar'],
                        'gender'=>$User['gender'],
                        'openid'=>$openid
                    ];
        
                    //密码正确,登录成功
                    $this->finish('完善资料成功', $data);
                    exit;
                }

            }else
            {
                //插入语句

                $paypass = $this->request->post('paypass','');

                $paysalt = build_ranstr(10);
        
                //加密的密码  密码+盐
                $repass = md5($paypass.$paysalt);

                //组装数据
                $data = [
                    'openid'=>$openid,
                    'nickname'=>$this->request->post('nickname',""),
                    'gender'=>$this->request->post('gender',0),
                    'paysalt'=>$paysalt,
                    'paypass'=>$repass,
                    'phone'=>$phone
                ];

                $result = $this->UserModel->validate('common/User/User.wxadd')->save($data);

                if($result === FALSE)
                {
                    $this->warning($this->UserModel->getError());
                    exit;
                }else
                {
                    //返回数据
                    $output = [
                        'id'=>$this->UserModel->id,
                        'nickname'=>$data['nickname'],
                        'phone'=>$data['phone'],
                        'gender'=>$data['gender'],
                        'openid'=>$openid
                    ];
        
                    //密码正确,登录成功
                    $this->finish('完善资料成功', $output);
                    exit;
                }


            }

        }else
        {
            //不存在,没注册过
            $this->warning('为了提供更好的服务，请完善您的信息',null,2);
            exit;
        }




    }


    /**
     * 修改用户信息方法
     */
    public function profile()
    {
        //获取用户信息
        $userid = $this->request->post('userid', 0);
        $nickname = $this->request->post('nickname', '');
        $gender = $this->request->post('gender', 0);

        //先判断用户是否存在
        $User = $this->UserModel->find($userid);

        if(!$User)
        {
            $this->warning('用户不存在');
            exit;
        }

        //组装
        $data = [
            'nickname'=>$nickname,
            'gender'=>$gender,
            'id'=>$userid
        ];

        //更新数据
        $result = $this->UserModel->isUpdate(true)->save($data);

        if($result === FALSE)
        {
            //更新失败
            $this->warning('更新基本资料失败');
            exit;
        }else
        {
            //更新资料成功 查询最新的用户数据
            $User = $this->UserModel->find($userid);

            $data = [
                'id'=>$User['id'],
                'nickname'=>$User['nickname'],
                'phone'=>$User['phone'],
                'avatar'=>$User['avatar'],
                'gender'=>$User['gender'],
            ];

            //密码正确,登录成功
            $this->finish('更新资料成功', $data);
            exit;
        }
    }


}
