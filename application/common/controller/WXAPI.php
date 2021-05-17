<?php

namespace app\common\controller;

use think\Controller;
use think\Request;
use \think\Config; //引入配置类

/**
 * 公共继承的控制器 把每个控制器公共的方法写在这个里面
 */
class WXAPI extends Controller
{
    //构造函数
    public function __construct()
    {
        //继承父类
        parent::__construct();
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   返回的数据
     * @param mixed  $code   返回的状态码
     * @return void
     */
    public function finish($msg = '未知消息', $data = null, $code = 1)
    {
        return $this->back($msg, $data, $code);
    }

    /**
     * 操作失败跳转的快捷方法
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   返回的数据
     * @param mixed  $code   返回的状态码
     * @return void
     */
    public function warning($msg = '未知错误', $data = null, $code = 0)
    {
        return $this->back($msg, $data, $code);
    }

    /**
     * 返回数据的方法
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   返回的数据
     * @param mixed  $code   返回的状态码
     * @return void
     */
    public function back($msg = '未知消息', $data = null, $code = 1)
    {

        if(empty($msg))
        {
            $msg = '未知消息';
        }

        $result = [
            'msg'=>$msg, //提示信息
            'data'=>$data,  //返回的数据
            'code'=>$code  //状态码
        ];

        //返回接口数据了
        echo json_encode($result);
        exit;
    }


    /**
     * 获取小程序用户的唯一凭证 根据登录凭证code来获取 openid、session_key、unionid
     * https://developers.weixin.qq.com/miniprogram/dev/api/code2Session.html
     * @param  string|array $js_code 验证器名或者验证规则数组
     */
    protected function code2Session($js_code = null)
    {
        if($js_code)
        {
            $appid = Config::get('APPID');
            $secret = Config::get('AppSecret');


            //封装的请求地址
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$js_code&grant_type=authorization_code";

            $result = $this->https_request($url);

            $resultArr = json_decode($result,true);

            return $resultArr;
        }else{
            return false;
        }
    }

    //http请求
    protected function https_request($url,$data = null)
    {
        if(function_exists('curl_init')){
        $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            if (!empty($data)){
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        }else{
            return false;
        }
    }



}
