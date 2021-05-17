<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if (!function_exists('build_ranstr')) {
    /**
     * 获得随机字符串
     * @param $len             需要的长度
     * @param $special        是否需要特殊符号
     * @return string       返回随机字符串
     */
    function build_ranstr($len = 8, $special=false)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
    
        if($special){
            $chars = array_merge($chars, array(
                "!", "@", "#", "$", "?", "|", "{", "/", ":", ";",
                "%", "^", "&", "*", "(", ")", "-", "_", "[", "]",
                "}", "<", ">", "~", "+", "=", ",", "."
            ));
        }
    
        $charsLen = count($chars) - 1;
        shuffle($chars);                            //打乱数组顺序
        $str = '';
        for($i=0; $i<$len; $i++)
        {
            $str .= $chars[mt_rand(0, $charsLen)];    //随机取出一位
        }
        return $str;
    }
}


if (!function_exists('build_upload')) {
    /**
     * 单文件上传
     * @param $name         图片的名字
     * @return string       返回随机字符串
     */
    function build_upload($name = 'images')
    {
        // 获取表单上传文件
        $file = request()->file($name);

        if($file)
        {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info)
            {
                //将上传成功的图片添加到数组里面
                return "uploads/".$info->getSaveName();
            }else{
                return false;
            }
        }
        
    }
}



if (!function_exists('build_uploads')) {
    /**
     * 多文件上传
     * @param $name         图片的名字
     * @return string       返回随机字符串
     */
    function build_uploads($name = 'images')
    {
        // 获取表单上传文件
        $files = request()->file($name);

        //结果的数组
        $result = [];

        foreach($files as $file)
        {
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info)
            {
                //将上传成功的图片添加到数组里面
                $result[] = "uploads/".$info->getSaveName();
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
                exit;
            }
        }

        return $result;
    }
}
