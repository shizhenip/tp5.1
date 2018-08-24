<?php

namespace app\admin\validate;


use think\Validate;

class AuthValidate extends Validate
{
    protected $rule = [
        ['title','require|unique:system_auth','权限名称不能为空|权限名称已经存在'],
      ];
}