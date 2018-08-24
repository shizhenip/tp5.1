<?php

namespace app\admin\controller;

use controller\BasicAdmin;

/**
 * 发送邮件
 * Class Mail
 * @package app\admin\controller
 * @date 2017/02/15 18:05
 */
class Mail extends BasicAdmin
{
    public $table = '';

    public function index()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            return sendEmail($post);
        }
        $this->assign('title', '发送邮件');
        return view();
    }
}