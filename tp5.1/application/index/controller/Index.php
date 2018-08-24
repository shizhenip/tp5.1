<?php

namespace app\index\controller;

use think\Controller;
use think\Db;

/**
 * 首页
 * Class Index
 * @package app\index\controller
 * @date 2018/07/01 18:12
 */
class Index extends Controller
{

    public function index()
    {
        $noticeList = Db::name('blog_notice')->where('status', 1)->order('create_at desc')->find();
        $articleList = Db::name('blog_article')->where('status', 1)->order('create_at desc')->limit(2)->select();
        $this->assign([
            'noticeList' => $noticeList,
            'articleList' => $articleList,
        ]);
        return view();
    }
}