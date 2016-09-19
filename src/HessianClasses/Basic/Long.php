<?php
namespace LibHessian\HessianClasses\Basic;

/**
*
*/
class Long extends BasicWrite
{
    public function getValue()
    {
        $value = (int) $this->value;
        $stream = 'L';
        $stream .= pack('c', ($value >> 56));
        $stream .= pack('c', ($value >> 48));
        $stream .= pack('c', ($value >> 40));
        $stream .= pack('c', ($value >> 32));
        $stream .= pack('c', ($value >> 24));
        $stream .= pack('c', ($value >> 16));
        $stream .= pack('c', ($value >> 8));
        $stream .= pack('c', $value);
        return $this->getStremResult($stream);
    }
}
