<?php

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;

/**
 * 系统功能节点管理
 * Class Node
 * @package app\admin\controller
 * @date 2017/02/15 18:13
 */
class Node extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'SystemNode';

    /**
     * 显示节点列表
     */
    public function index()
    {
        $this->assign('alert', [
            'type' => 'danger',
            'title' => '安全警告',
            'content' => '结构为系统自动生成，状态数据请勿随意修改！'
        ]);
        $this->assign('title', '系统节点管理');
        $this->assign('nodes', ToolsService::arr2table(NodeService::get(), 'node', 'pnode'));
        return view();
    }

    /**
     * 保存节点变更
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (isset($post['name']) && isset($post['value'])) {
                $nameattr = explode('.', $post['name']);
                $field = array_shift($nameattr);
                $data = ['node' => join(',', $nameattr), $field => $post['value']];
                DataService::save($this->table, $data, 'node');
                $this->success('参数保存成功！', '');
            }
        } else {
            $this->error('访问异常，请重新进入...');
        }
    }

}
