<?php

namespace app\admin\controller;

use controller\BasicAdmin;
use down\Csv;
use service\DataService;
use service\LogService;
use service\ToolsService;
use think\Db;

/**
 * 系统用户管理控制器
 * Class User
 * @package app\admin\controller
 * @date 2017/02/15 18:12
 */
class User extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'SystemUser';
    
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
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        // 实例化并显示
        return parent::_list($db);
    }

    /**
     * 授权管理
     * @return array|string
     */
    public function auth()
    {
        return $this->_form($this->table, 'auth');
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
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', false);
            return $this->_form($this->table, 'pass');
        }
        $data = $this->request->post();
        if ($data['password'] !== $data['repassword']) {
            $this->error('两次输入的密码不一致！');
        }
        if (DataService::save($this->table, ['id' => $data['id'], 'password' => password($data['password'])], 'id')) {
            $this->success('密码修改成功，下次请使用新密码登录！', '');
        }
        $this->error('密码修改失败，请稍候再试！');
    }

    /**
     * 表单数据默认处理
     * @param array $data
     */
    public function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            if ($this->request->action() == "add") {
                $data['password'] = password(123456);//默认密码
            }
            if (isset($data['authorize']) && is_array($data['authorize'])) {
                $data['authorize'] = join(',', $data['authorize']);
            }

            if (!isset($data['authorize'])) {
                $data['authorize'] = "";
            }
            if (isset($data['id'])) {
                unset($data['phone']);
            } elseif (Db::name($this->table)->where('phone', $data['phone'])->find()) {
                $this->error('手机已经存在，请使用其它手机！');
            }
        } else {
            $data['authorize'] = explode(',', isset($data['authorize']) ? $data['authorize'] : '');
            $this->assign([
                'authorizes' => Db::name('SystemAuth')->select()
            ]);
        }
    }

    /**
     * 删除用户
     */
    public function del()
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止删除！');
        }
        if (DataService::update($this->table)) {
            $id = $this->request->post('id');
            $name = Db::name($this->table)->where('id', $id)->value('realname');
            LogService::write('系统管理', '删除了员工' . $name);
            $this->success("用户删除成功！", '');
        }
        $this->error("用户删除失败，请稍候再试！");
    }

    /**
     * 用户禁用
     */
    public function forbid()
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        $id = $this->request->post('id');
        $name = Db::name($this->table)->where('id', $id)->value('realname');
        if (DataService::update($this->table)) {
            LogService::write('系统管理', '禁用了员工' . $name);
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
            $id = $this->request->post('id');
            $name = Db::name($this->table)->where('id', $id)->value('realname');
            LogService::write('系统管理', '启用了员工' . $name);
            $this->success("用户启用成功！", '');
        }
        $this->error("用户启用失败，请稍候再试！");
    }

    /**
     *导入员工
     */
    public function upload()
    {
        if ($this->request->isPost()) {
            $filedList = array('username', 'realname', 'phone', 'mail', 'desc');
            $file_name = input('file_name', '', 'string');
            $index = strpos($file_name, 'static');
            $file_name = substr($file_name, $index, 200);
            $data = Csv::uploadCsv($file_name, $filedList);
            empty($data) && $this->error('暂无数据需要导入！');

            /* $user_list = array();
             $list = Db::name('system_user')->field('phone')->select();
             foreach ($list as $k => $v) {
                 $user_list[] = $v['phone'];
             }*/
            $err = 0;
            /*foreach ($data as $k => $v) {
                if (in_array($v['phone'], $user_list)) {
                    unset($data[$k]);
                    $err++;
                }
            }*/

            try {
                if (count($data) == 0) {
                    return json(['code' => 0, 'msg' => "暂无数据需要导入"]);
                } else {
                    foreach ($data as $k => $v) {
                        $data[$k]['password'] = md5(123456);//默认密码
                        $data[$k]['create_at'] = time();
                    }
                    Db::name($this->table)->insertAll($data);
                    return json(['code' => 1, 'msg' => "成功导入" . count($data) . "条数据！", "重复数据" . $err . "条"]);
                }
            } catch (\Exception  $e) {
                $this->error($e->getMessage());
            }
        }
        return view();
    }

}
