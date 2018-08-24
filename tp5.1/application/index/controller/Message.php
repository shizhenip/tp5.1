<?php

namespace app\index\controller;

use think\Controller;

/**
 * 留言
 * Class LeaveWord
 * @package app\index\controller
 * @date 2017/12/01 18:12
 */
class Message extends Controller
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table = '';

    /**
     * 填写留言
     */
    public function index()
    {
        return view();
    }
}