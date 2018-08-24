<?php

namespace app\admin\controller;

use controller\BasicAdmin;
use down\Csv;
use service\DataService;
use think\Db;

/**
 * 系统日志管理
 * Class User
 * @package app\admin\controller
 * @date 2017/02/15 18:12
 */
class Log extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'SystemLog';

    public function demo()
    {
        $data = Db::name($this->table)->order('id desc')->select();
        $arr["code"] = 0;
        $arr["msg"] = "";
        $arr["count"] = 1000;
        $arr['data'] = $data;
        return json($arr);
    }

    /**
     * 日志列表
     */
    public function index()
    {
        $this->title = '系统操作日志';
        $this->assign('actions', Db::name($this->table)->group('action')->column('action'));
        $db = Db::name($this->table)->order('id desc');
        $get = $this->request->get();

        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['date']) && $get['date'] !== '') {
            list($start, $end) = explode('-', str_replace(' ', '', $get['date']));
            $db->whereBetween('create_at', [strtotime($start), strtotime($end)]);
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $data
     */
    protected function _index_data_filter(&$data)
    {
        $ip = new \Ip2Region();
        foreach ($data as &$vo) {
            $result = $ip->btreeSearch($vo['ip']);
            $vo['isp'] = isset($result['region']) ? $result['region'] : '';
            $vo['isp'] = str_replace(['|0|0|0|0', '|'], ['', ' '], $vo['isp']);
        }
    }

    /**
     * 日志删除操作
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("日志删除成功！", '');
        }
        $this->error("日志删除失败，请稍候再试！");
    }

    /**
     * 导出日志
     */
    public function export()
    {
        $file_name = "导出日志";
        $xlsCell = array(
            array('ip', 'IP'), array('node', '节点'),
            array('username', '姓名'), array('action', '行为'),
            array('content', '操作内容'), array('create_at', '操作时间'),
        );
        $db = Db::name($this->table);
        $filedList = array();
        $titleList = array();
        foreach ($xlsCell as $k => $v) {
            $filedList[$k] = $xlsCell[$k][0];
            $titleList[] = $xlsCell[$k][1];
        }
        $data = $db->field($filedList)->select();
        if (!$data) {
            return json(['code' => 0, 'msg' => "没有可导出的数据"]);
        }
        foreach ($data as $k => $v) {
            $data[$k]['create_at'] = dateTime($v['create_at']);
        }
        //$excel = new\Excel();
        //$excel->exportExcel($file_name, $xlsCell, $data);

        Csv::exportCsv($file_name, $titleList, $data);
        exit();
    }

}
