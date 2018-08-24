<?php

namespace app\index\controller;

use think\Controller;

/**
 * 关于
 * Class About
 * @package app\index\controller
 * @date 2017/12/01 18:12
 */
class About extends Controller
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table = '';

    /**
     * 关于页面
     */
    public function index()
    {
        return view();
    }
}