<?php

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\ToolsService;
use think\Db;

/**
 * 无限级管理
 * Class User
 * @package app\admin\controller
 * @date 2017/02/15 18:12
 */
class Classify extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'SystemClassify';

    /**
     * 无限级列表
     */
    public function index()
    {
        // 设置页面标题
        $this->title = '无限级列表';
        // 实例Query对象
        $db = Db::name($this->table);
        // 实例化并显示
        return parent::_list($db, false);
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_data_filter(&$data)
    {
        $data = ToolsService::arr2table($data);
    }

    /**
     * 添加
     */
    public function add()
    {
        $_menus = Db::name($this->table)->where('status', 1)->order('sort desc,id desc')->select();
        $_menus[] = ['name' => '顶级部门', 'id' => '0', 'pid' => '-1'];
        $menus = ToolsService::arr2table($_menus);
        $this->assign('menus', $menus);
        if ($this->request->isPost()) {
            $post_data = input('post.');
            $result = $this->validate($post_data, 'ClassifyValidate');
            if (true !== $result) {
                $this->error($result);
            }
        }
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $id = input('id');
        $_menus = Db::name($this->table)->where('status', 1)->where('id', 'neq', $id)->order('sort desc,id desc')->select();
        $_menus[] = ['name' => '顶级', 'id' => '0', 'pid' => '-1'];
        $menus = ToolsService::arr2table($_menus);
        $this->assign('menus', $menus);
        if ($this->request->isPost()) {
            $post_data = input('post.');
            $result = $this->validate($post_data, 'ClassifyValidate');
            if (true !== $result) {
                $this->error($result);
            }
        }
        return $this->_form($this->table, 'form');
    }

    /**
     * 删除
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }
}   