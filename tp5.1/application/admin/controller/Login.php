<?php

namespace app\admin\controller;

use service\LogService;
use service\NodeService;
use think\Controller;
use think\Db;

/**
 * 系统登录控制器
 * class Login
 * @package app\admin\controller
 * @date 2017/02/10 13:59
 */
class Login extends Controller
{
    /**
     * 默认检查用户登录状态
     * @var bool
     */
    public $checkLogin = false;

    /**
     * 默认检查节点访问权限
     * @var bool
     */
    public $checkAuth = false;

    /**
     * 控制器基础方法
     */
    public function initialize()
    {
        if (session('user') && $this->request->action() !== 'out') {
            $this->redirect('@admin');
        }
    }

    /**
     * 用户登录
     * @return string
     */
    public function index()
    {
        if ($this->request->isGet()) {
            $this->assign('title', '用户登录');
            return $this->fetch();
        } else {
            $username = $this->request->post('username', '', 'trim');
            $password = $this->request->post('password', '', 'trim');
            $GtSdk = new \Geetestlib(config('API.gee_id'), config('API.gee_key'));
            $web = session('web');
            if (session('gtserver') == 1) {
                $result = $GtSdk->success_validate(input('param.geetest_challenge'), input('param.geetest_validate'), input('param.geetest_seccode'), $web);
                if (!$result) {
                    $this->error('请先拖动验证码到相应位置');
                }
            } else {
                if (!$GtSdk->fail_validate(input('param.geetest_challenge'), input('param.geetest_validate'), input('param.geetest_seccode'))) {
                    $this->error('请先拖动验证码到相应位置');
                }
            }
            (empty($username) || strlen($username) < 2) && $this->error('登录账号长度不能少于2位有效字符!');
            (empty($password) || strlen($password) < 5) && $this->error('登录密码长度不能少于5位有效字符!');
            $user = Db::name('SystemUser')->where(self::testUser($username), $username)->where('is_deleted', 0)->where('status', 1)->find();
            empty($user) && $this->error('登录账号不存在，请重新输入!');
            ($user['password'] !== password($password)) && $this->error('登录密码与账号不匹配，请重新输入!');
            Db::name('SystemUser')->where('id', $user['id'])->inc('login_num')->update(['login_at' => time()]);
            session('user', $user);
            cookie('user', $user);
            !empty($user['authorize']) && NodeService::applyAuthNode();
            LogService::write('系统管理', '用户登录系统成功');
            $this->success('登录成功，正在进入系统...', '@admin');
        }
    }

    /**
     * 验证码
     */
    public function checkVerify()
    {
        $verify = new \Verify();
        $verify->imageH = 32;
        $verify->imageW = 100;
        $verify->codeSet = '0123456789';
        $verify->length = 4;
        $verify->useNoise = false;
        $verify->fontSize = 14;
        $verify->entry();
    }

    /**
     * 拖动验证码
     */
    public function getverify()
    {
        $GtSdk = new \Geetestlib(config('API.gee_id'), config('API.gee_key'));
        $web = 'web';
        $status = $GtSdk->pre_process($web);
        session('gtserver', $status);
        session('web', $web);
        echo $GtSdk->get_response_str();
    }

    /**
     * 退出登录
     */
    public function out()
    {
        LogService::write('系统管理', '用户退出系统成功');
        session('user', null);
        cookie('user', null);
        $this->success('退出登录成功！', '/login.html');
    }


    /**
     * 检查用户登录名格式
     * @param string $username 需要验证的用户名
     * @return string
     */
    protected static function testUser($username)
    {
        if (preg_match("/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i", $username)) {
            return $field = 'mail';
        } elseif (preg_match("/1[3458]{1}\d{9}$/", $username)) {
            return $field = 'phone';
        } else {
            return $field = 'username';
        }
    }
}
