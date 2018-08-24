<?php

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\Db;
use think\facade\View;
use think\facade\Env;
use think\App;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @date 2017/02/15 10:41
 */
class Index extends BasicAdmin
{
    /**
     * 后台框架布局
     * @return View
     */
    public function index()
    {
        NodeService::applyAuthNode();
        $list = (array)Db::name('SystemMenu')->where('status', '1')->order('sort asc,id asc')->cache('indexSystemMenu', 60)->select();
        $menus = $this->_filterMenu(ToolsService::arr2tree($list));
        $this->assign('title', '系统管理');
        $this->assign('menus', $menus);
        return view();
    }

    /**
     * 后台主菜单权限过滤
     * @param array $menus
     * @return array
     */
    private function _filterMenu($menus)
    {
        foreach ($menus as $key => &$menu) {
            if (!empty($menu['sub'])) {
                $menu['sub'] = $this->_filterMenu($menu['sub']);
            }
            if (!empty($menu['sub'])) {
                $menu['url'] = '#';
            } elseif (stripos($menu['url'], 'http') === 0) {
                continue;
            } elseif ($menu['url'] !== '#' && auth(join('/', array_slice(explode('/', $menu['url']), 0, 3)))) {
                $menu['url'] = url($menu['url']);
            } else {
                unset($menus[$key]);
            }
        }
        return $menus;
    }

    /**
     * 主机信息显示
     * @return View
     */
    public function main()
    {
        $_version = Db::query('select version() as ver');
        $version = array_pop($_version);
        $this->assign('server', getenv());
        $this->assign('mysql_ver', $version['ver']);
        $this->assign('think_ver' , App::VERSION);
        if (session('user.password') === 'e10adc3949ba59abbe56e057f20f883e') {
            $url = url('admin/index/pass') . '?id=' . session('user.id');
            $alert = [
                'type' => 'danger',
                'title' => '安全提示',
                'content' => "亲，你的密码设置的太简单，建议马上<a href='javascript:void(0)' data-modal='{$url}'>修改</a>！"
            ];
            $this->assign('alert', $alert);
            $this->assign('title', '后台首页');
        }
        return view();
    }

    /**
     * 修改密码
     */
    public function pass()
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        if (intval($this->request->request('id')) !== intval(session('user.id'))) {
            $this->error('访问异常！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', true);
            return $this->_form('SystemUser', 'user/pass');
        } else {
            $data = $this->request->post();
            if ($data['password'] !== $data['repassword']) {
                $this->error('两次输入的密码不一致，请重新输入！');
            }
            $user = Db::name('SystemUser')->where('id', session('user.id'))->find();
            if (md5($data['oldpassword']) !== $user['password']) {
                $this->error('旧密码验证失败，请重新输入！');
            }
            if (DataService::save('SystemUser', ['id' => session('user.id'), 'password' => password($data['password'])])) {
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
    }

    /**
     * 修改资料
     */
    public function info()
    {
        $_menus = Db::name('System_department')->where('status', 1)->order('sort desc,id desc')->select();
        $_menus[] = ['name' => '顶级部门', 'id' => '0', 'pid' => '-1'];
        $menus = ToolsService::arr2table($_menus);

        $this->assign([
            'menus' => $menus
        ]);
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        if (intval($this->request->request('id')) === intval(session('user.id'))) {
            return $this->_form('SystemUser', 'user/form');
        }
        $this->error('访问异常！');
    }

    /**
     * 清空文件缓存
     */
    public function clearCache()
    {
        self::delFileUnderDir(Env::get('runtime_path'));
        $this->success('已经成功清理缓存!', '');
    }

    /**
     * 清除缓存
     * @param string $dirName 文件名
     */
    protected static function delFileUnderDir($dirName)
    {
        if ($handle = opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item")) {
                        self::delFileUnderDir("$dirName/$item");
                    } else {
                        unlink("$dirName/$item");
                    }
                }
            }
            closedir($handle);
        }
    }

}
