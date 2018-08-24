<?php

namespace app\admin\middleware;

use think\exception\HttpResponseException;
use think\facade\Config;
use think\facade\View;
use think\Request;

/**
 * 访问权限管理
 * Class AccessAuth
 * @package hook
 * @date 2017/05/12 11:59
 */
class AccessAuth
{
    /**
     * 行为入口
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        list($module, $controller, $action) = [$request->module(), $request->controller(), $request->action()];
        $vars = get_class_vars(config('app_namespace') . "\\{$module}\\controller\\{$controller}");
        // 用户登录状态检查
        if ((!empty($vars['checkAuth']) || !empty($vars['checkLogin'])) && !session('user')) {
            if ($request->isAjax()) {
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
        if (PHP_SAPI !== 'cli') {
            //$this->baidu($params);
            //$this->cnzz($params);
        }
        $view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        $view->assign('classuri', strtolower("{$module}/{$controller}"));
        return $next($request);
    }


    /**
     * 百度统计实现代码
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function baidu($request, \Closure $next)
    {
        if (($key = sysconf('tongji_baidu_key'))) {
            $script = <<<SCRIPT
        <script>
            var _hmt = _hmt || [];
            (function() {
                var hm = document.createElement("script");
                hm.src = "https://hm.baidu.com/hm.js?{$key}";
                var s = document.getElementsByTagName("script")[0]; 
                s.parentNode.insertBefore(hm, s);
            })();
        </script>
SCRIPT;
            $request = preg_replace('|</body>|i', "{$script}\n    </body>", $request);
            return $next($request);
        }
    }

    /**
     * CNZZ统计实现代码
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
     public function cnzz($request, \Closure $next) {
         if (($key = sysconf('tongji_cnzz_key'))) {
             $query = ['siteid' => $key, 'r' => $request->server('HTTP_REFERER'), 'rnd' => mt_rand(100000, 999999)];
             $imgSrc = 'https://c.cnzz.com/wapstat.php?' . http_build_query($query);
             $request = preg_replace('|</body>|i', "<img src='{$imgSrc}' style='display:block;position:absolute' width='0' height='0'/>\n    </body>", $request);
             return $next($request);
         }
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
