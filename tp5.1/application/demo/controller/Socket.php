<?php

namespace app\demo\controller;

use controller\BasicAdmin;
use think\Db;

/**
 * 测试socket
 * Class Socket
 * @package app\demo\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/07/10 18:13
 */
class Socket extends BasicAdmin
{
    /**
     * socket客户端
     */
    public function index()
    {
        $timer = Db::name('demo_timer')->where('is_past', 1)->where('uid', session('user.id'))->find();
        return view('', ['title' => '测试socket', 'timer' => json_encode($timer, JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * 添加定时任务
     */
    public function addTimer()
    {
        if ($this->request->isPost()) {
            $post = $this->request->Post();
            $str = '1234567890';
            $randStr = str_shuffle($str);//打乱字符串
            $rands = substr($randStr, 0, 5);
            $post['timer'] = (int)strtotime($post['timer']) - time();
            $post['cust_id'] = (int)$rands;
            $post['uid'] = session('user.id');
            $post['type'] = 'addTimer';
            Db::name('demo_timer')->insert($post);
            return json($post);
        }
        return json(['code' => 2, 'msg' => '请不要GET打开！']);
    }

    /**
     * 绑定 $client_id
     */
    public function ajaxBind()
    {
        if ($this->request->isPost()) {
            $post = $this->request->Post();
            $find = db('bind_client')->where('uid', $post['uid'])->find();
            if (!empty($find)) {
                $result = db('bind_client')->where('uid', $find['uid'])->update(['client_id' => $post['client_id']]);
            } else {
                $result = db('bind_client')->insert(['client_id' => $post['client_id'], 'uid' => $post['uid']]);
            }
            return json(['code' => $result, 'msg' => $result > 0 ? '修改成功！' : '修改失败！']);
        }
        return json(['code' => 2, 'msg' => '请不要GET打开！']);
    }

}