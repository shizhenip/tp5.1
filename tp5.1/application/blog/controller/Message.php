<?php

namespace app\blog\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;

/**
 * 留言管理控制器
 * Class Message
 * @package app\blog\controller
 * @date 2018/07/15 18:12
 */
class Message extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'BlogMessages';

    /**
     * 留言列表
     */
    public function index()
    {
        // 设置页面标题
        $this->title = '留言列表';
        // 实例Query对象
        $db = Db::name($this->table)->alias('m')
            ->join('blog_user u', 'u.id=m.uid', 'LEFT')
            ->field('u.username,u.phone,m.*')
            ->order('m.msg_time desc');
        // 实例化并显示
        return parent::_list($db);
    }

    /**
     * 删除留言
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

    /**
     * 列表数据处理
     * @param array $data 操作数据
     */
    protected function _data_filter(&$data)
    {
        foreach ($data as $k => $v) {
            //$data[$k]['content'] = omit($v['content']);
        }
    }
}