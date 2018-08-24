<?php

namespace app\blog\controller;

use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use think\Db;

/**
 * 用户管理控制器
 * Class User
 * @package app\blog\controller
 * @date 2018/07/15 18:12
 */
class User extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'BlogUser';

    /**
     * 用户列表
     */
    public function index()
    {
        // 设置页面标题
        $this->title = '用户管理';
        // 获取到所有GET参数
        $get = $this->request->get();
        // 实例Query对象
        $db = Db::name($this->table)->where('is_deleted', 0)->order('id desc');
        // 应用搜索条件
        foreach (['username', 'phone', 'mail'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%" . trim($get[$key]) . "%");
        }
        // 实例化并显示
        return parent::_list($db);
    }

    /**
     * 用户添加
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 用户编辑
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 用户密码修改
     */
    public function pass()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if ($post['password'] !== $post['repassword']) {
                $this->error('重复密码错误！');
            }
            if (DataService::save($this->table, ['id' => $post['id'], 'password' => password($post['password'])], 'id')) {
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            }
            $this->error('密码修改失败，请稍候再试！');
        }
        if ($this->request->isGet()) {
            return $this->_form($this->table, 'pass');
        }
    }

    /**
     * 删除用户
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('博客管理', session('user.username') . '删除了用户');
            $this->success("用户删除成功！", '');
        }
        $this->error("用户删除失败，请稍候再试！");
    }

    /**
     * 用户禁用
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            LogService::write('博客管理', session('user.username') . '禁用了用户');
            $this->success("用户禁用成功！", '');
        }
        $this->error("用户禁用失败，请稍候再试！");
    }

    /**
     * 用户启用
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            LogService::write('博客管理', session('user.username') . '启用了用户');
            $this->success("用户启用成功！", '');
        }
        $this->error("用户启用失败，请稍候再试！");
    }

    /**
     * 表单数据默认处理
     * @param array $data
     */
    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            if (!isPhone($data['phone'])) $this->error('手机号错误！');
            if (isset($data['id'])) {
                unset($data['phone']);
            } else {
                $data['password'] = password(123456);//默认密码
                $count = Db::name($this->table)->where('phone', $data['phone'])->count();
                if ($count >= 1) $this->error('手机号已经存在，请使用其它手机号！');
            }
        }
    }

}