<?php

namespace app\blog\controller;

use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use think\Db;

/**
 * 公告管理控制器
 * Class Article
 * @package app\blog\controller
 * @date 2018/07/15 18:12
 */
class Notice extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'BlogNotice';

    /**
     * 公告列表
     */
    public function index()
    {
        // 设置页面标题
        $this->title = '公告列表';
        // 实例Query对象
        $db = Db::name($this->table)->order('create_at desc');
        // 实例化并显示
        return parent::_list($db);
    }

    /**
     * 添加
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }


    /**
     * 删除
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('博客管理', '删除了公告');
            $this->success("公告删除成功！", '');
        }
        $this->error("公告删除失败，请稍候再试！");
    }

    /**
     * 禁用
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            LogService::write('博客管理', '禁用了公告');
            $this->success("禁用公告成功！", '');
        }
        $this->error("禁用公告失败，请稍候再试！");
    }

    /**
     * 启用
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            LogService::write('博客管理', '启用了公告');
            $this->success("启用公告成功！", '');
        }
        $this->error("启用公告失败，请稍候再试！");
    }

    /**
     * 列表数据处理
     * @param array $data 操作数据
     */
    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            $data['content'] = myTrim($data['content']);
            $data['updata_at'] = time();
        }
    }
}