<?php

namespace app\admin\controller;

use controller\BasicAdmin;

/**
 * 短信管理
 * Class Mail
 * @package app\admin\controller
 * @date 2017/02/15 18:05
 */
class Sms extends BasicAdmin
{
    public $table = '';

    public function index()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $smsId = 'SMS_140700218';
            $phone = $post['phone'];
            if (!isPhone($phone)) $this->error('手机号错误！');
            return sendSms($smsId, $phone);
        }
        $this->assign('title', '发送短信');
        return view();
    }
}