<?php

use think\facade\Route;

//前台
Route::rule('home','index/index/index');//首页
Route::rule('message','index/message/index');//留言
Route::rule('about','/index/about/index');//关于

