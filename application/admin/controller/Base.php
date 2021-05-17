<?php


namespace app\admin\controller;


use app\common\controller\Mobi;

class Base extends Mobi
{
    public function __construct()
    {
        parent::__construct();
        $this->checkAuth();
    }

    protected function checkAuth()
    {
        //
    }
}