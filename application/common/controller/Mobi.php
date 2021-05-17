<?php

namespace app\common\controller;

use think\Controller;
use think\Request;

/**
 * 公共继承的控制器 把每个控制器公共的方法写在这个里面
 */
class Mobi extends Controller
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
}
