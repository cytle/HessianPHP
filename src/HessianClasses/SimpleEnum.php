<?php

namespace LibHessian\HessianClasses;

/**
* 普通枚举类
*/
class SimpleEnum
{
    public $name;
    public $__type;

    function __construct($name, $__type = null)
    {
        $this->name = $name;
        $this->__type = $__type;
    }
}
