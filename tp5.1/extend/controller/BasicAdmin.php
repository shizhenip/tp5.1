<?php


namespace controller;

use service\DataService;
use think\Controller;
use think\Db;
use think\db\Query;

/**
 * 后台权限基础控制器
 * Class BasicAdmin
 * @package controller
 */
class BasicAdmin extends Controller
{

    /**
     * 页面标题
     * @var string
     */
    public $title;

    /**
     * 默认操作数据表
     * @var string
     */
    public $table;

    /**
     * 默认检查用户登录状态
     * @var bool
     */
    public $checkLogin = true;

    /**
     * 默认检查节点访问权限
     * @var bool
     */
    public $checkAuth = true;

    /**
     * 表单默认操作
     * @param Query $dbQuery 数据库查询对象
     * @param string $tplFile 显示模板名字
     * @param string $pkField 更新主键规则
     * @param array $where 查询规则
     * @param array $data 扩展数据
     * @param string $url 成功跳转地址
     * @return array|string
     */
    protected function _form($dbQuery = null, $tplFile = '', $pkField = '', $where = [], $data = [], $url = '')
    {
        $db = is_null($dbQuery) ? Db::name($this->table) : (is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery);
        $pk = empty($pkField) ? ($db->getPk() ? $db->getPk() : 'id') : $pkField;
        $pkValue = $this->request->request($pk, isset($where[$pk]) ? $where[$pk] : (isset($data[$pk]) ? $data[$pk] : null));
        // POST请求, 数据自动存库
        if ($this->request->isPost()) {
            $data = array_merge($this->request->post(), $data);
            if (false !== $this->_callback('_form_filter', $data)) {
                $result = DataService::save($db, $data, $pk, $where);
                if (false === $this->_callback('_form_result', $result)) {
                    return $result;
                }
                if ($result !== false) {
                    $this->success('恭喜, 数据保存成功!', $url);
                }
                $this->error('数据保存失败, 请稍候再试!');
            }
        }
        // GET请求, 获取并显示表单页面
        $vo = ($pkValue !== null) ? array_merge((array)$db->where($pk, $pkValue)->where($where)->find(), $data) : $data;
        if (false === $this->_callback('_form_filter', $vo)) {
            return $vo;
        }
        empty($this->title) or $this->assign('title', $this->title);
        return $this->fetch($tplFile, ['vo' => $vo]);
    }

    /**
     * 列表集成处理方法
     * @param Query $dbQuery 数据库查询对象
     * @param bool $isPage 是启用分页
     * @param bool $isDisplay 是否直接输出显示
     * @param bool $total 总记录数
     * @param string $template 模板路径
     * @return array|string
     */
    protected function _list($dbQuery = null, $isPage = true, $isDisplay = true, $total = false, $template = '')
    {
        $db = is_null($dbQuery) ? Db::name($this->table) : (is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery);
        // 列表排序默认处理
        if ($this->request->isPost() && $this->request->post('action') === 'resort') {
            $data = $this->request->post();
            unset($data['action']);
            foreach ($data as $key => &$value) {
                if (false === $db->where('id', intval(ltrim($key, '_')))->setField('sort', $value)) {
                    $this->error('列表排序失败, 请稍候再试');
                }
            }
            $this->success('列表排序成功, 正在刷新列表', '');
        }
        // 列表数据查询与显示
        $result = [];
        if (null === $db->getOptions('order')) {
            in_array('sort', $db->getTableFields($db->getTable())) && $db->order('sort', 'asc');
        }
        if ($isPage) {
            $row_page = $this->request->get('rows', config('paginate.list_rows'), 'intval');
            $page = $db->paginate($row_page, $total, ['query' => $this->request->get()]);
            $result['total_num'] = $page->total();
            $result['list'] = $page->all();
            $result['page'] = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'], $page->render());
        } else {
            $result['list'] = $db->select();
        }
        if (false === $this->_callback('_data_filter', $result['list']) || !$isDisplay) {
            return $result;
        }
        !empty($this->title) && $this->assign('title', $this->title);
        return $this->fetch($template, $result);

    }

    /**
     * 当前对象回调成员方法
     * @param string $method
     * @param array|bool $data
     * @return bool
     */
    protected function _callback($method, &$data)
    {
        foreach ([$method, "_" . $this->request->action() . "{$method}"] as $_method) {
            if (method_exists($this, $_method) && false === $this->$_method($data)) {
                return false;
            }
        }
        return true;
    }

}
