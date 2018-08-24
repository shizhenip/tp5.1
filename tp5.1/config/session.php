<?php

$session_name = 's' . substr(md5(__DIR__), -8);
//$session_path = env('runtime_path') . 'session' . DIRECTORY_SEPARATOR;
//file_exists($session_path) || mkdir($session_path, 0777, true);
return [
    // +----------------------------------------------------------------------
    // | SESSION会话设置
    // +----------------------------------------------------------------------
    'id' => '',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => $session_name,
    // SESSION 前缀
    'prefix' => 'think',
    // 驱动方式 支持redis memcache memcached
    'type' => '',
    // 会话路劲
    //'path' => $session_path,
    // 会话名称
    //'name' => $session_name,
    // SESSION 保存时间
    'expire' => 8 * 3600,
    // 是否自动开启 SESSION
    'auto_start' => true,
];