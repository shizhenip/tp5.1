<?php

namespace app\admin\validate;

use think\Validate;

class ClassifyValidate extends Validate
{
    protected $rule = [
        ['name','require|unique:SystemClassify','部门名称不能为空|部门名称已经存在'],
    ];
}