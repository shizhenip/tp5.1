<?php

namespace hook;

use think\facade\Config;
use think\exception\HttpResponseException;
use think\facade\Request;
use think\facade\View;

/**
 * 访问权限管理
 * Class AccessAuth
 * @package hook
 * @date 2017/05/12 11:59
 */
class AccessAuth
{

    /**
     * 当前请求对象
     * @var Request
     */
    protected $request;

    /**
     * 行为入口
     * @param $params
     */
    public function handle(&$params)
    {
        $this->request = Request::instance();
        list($module, $controller, $action) = [$this->request->module(), $this->request->controller(), $this->request->action()];
        $vars = get_class_vars(config('app_namespace') . "\\{$module}\\controller\\{$controller}");
        // 用户登录状态检查
        if ((!empty($vars['checkAuth']) || !empty($vars['checkLogin'])) && !session('user')) {
            if ($this->request->isAjax()) {
                $result = ['code' => 0, 'msg' => '抱歉, 您还没有登录获取访问权限!', 'data' => '', 'url' => '/login.html', 'wait' => 3];
                throw new HttpResponseException(json($result));
            }
            throw new HttpResponseException(redirect('/login.html'));
        }
        // 访问权限节点检查
        if (!empty($vars['checkLogin']) && !auth("{$module}/{$controller}/{$action}")) {
            $result = ['code' => 0, 'msg' => '抱歉, 您没有访问该模块的权限!', 'data' => '', 'url' => '', 'wait' => 3];
            throw new HttpResponseException(json($result));
        }
//        $noallow_module_list = explode(',', sysconf('allow_module_list'));//需要验证ip的模块
//        $allow_mobile_list = explode(',', sysconf('allow_mobile_list'));
//        $phone = session('user.phone');
//        if (!in_array($phone, $allow_mobile_list) && session('user')) {
//            if (in_array($this->request->module(), $noallow_module_list)) {
//                $ipList = explode(',', sysconf('ip_list'));
//                self::checkIp($ipList);
//            }
//        }
        // 权限正常, 默认赋值
        $view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        $view->assign('classuri', strtolower("{$module}/{$controller}"));
    }

    /**
     * 函数描述 限制IP
     * @param array $ipList 不需要验证的ip列表
     * @return string
     */
    protected static function checkIp($ipList)
    {
        $IP = \think\facade\Request::instance()->ip();
        $check_ip_arr = explode('.', $IP);
        if (!in_array($IP, $ipList)) {
            foreach ($ipList as $val) {
                if (strpos($val, '*')) {
                    $arr = explode('.', $val);
                    $bl = true;
                    for ($i = 0; $i < 4; $i++) {
                        if ($arr[$i] != '*') {
                            if ($arr[$i] != $check_ip_arr[$i]) {
                                $bl = false;
                                break;
                            }
                        }
                    }
                    if ($bl) return;
                }
            }
            header('HTTP/1.1 403 Forbidden');
            die("拒绝访问");
        }
        header('HTTP/1.1 403 Forbidden');
        die("拒绝访问");
    }

}
