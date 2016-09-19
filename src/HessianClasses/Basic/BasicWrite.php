<?php
namespace LibHessian\HessianClasses\Basic;

use LibHessian\Contracts\BasicWriteContract;

use LibHessian\Hessian\HessianStreamResult;
/**
*
*/
class BasicWrite implements BasicWriteContract
{
    protected $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getStremResult($value)
    {
        return new HessianStreamResult($value);
    }
}
