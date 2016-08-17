<?php

namespace LibHessian\Exceptions;

use RuntimeException;


/**
* Hessian异常
*/
class HessianException extends RuntimeException
{

    protected $url = '';
    protected $method = '';
    protected $arguments = [];

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;

    }

    public function getMethod()
    {
        return $this->method;

    }

    public function getArguments()
    {
        return $this->arguments;

    }

    public function __toString()
    {

        $strArr = [];
        $strArr[] = '';
        $strArr[] = '****** extra message ******';
        $strArr[] = 'url:       ' . $this->getUrl() . ';';
        $strArr[] = 'method:    ' . $this->getMethod() . ';';
        $strArr[] = 'arguments: ' . json_encode($this->getArguments()) . ';';
        $strArr[] = '****** extra message end ******';
        $strArr[] = parent::__toString();

        // $pre = $this->getPrevious();
        // $strArr[] = $pre ? $pre->__toString() : null;


        return implode($strArr, "\n");
    }

}





