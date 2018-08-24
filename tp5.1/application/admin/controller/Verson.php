<?php

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;

/**
 * 版本管理
 * class Login
 * @package app\admin\controller
 * @date 2017/02/10 13:59
 */
class Verson extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'SystemVerson';

    /**
     * 版本列表
     */
    public function index()
    {
        // 设置页面标题
        $this->title = '版本列表';
        // 实例化并显示
        return parent::_list($this->table);
    }

    /**
     * 添加版本
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑版本
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 删除版本
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

    /**
     * 表单基本操作
     */
    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            $data['create_name'] = session('user.realname');
            $data['update_at'] = time();
        }
    }
}