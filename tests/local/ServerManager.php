<?php

include_once 'until.php';

/**
*
*/
class ServerManager
{
    private $port = 8765;
    private $host = 'localhost';
    private $sResource = null;


    function __constructor ($options)
    {
        if (isset($options['host']) && $options['host']) {
            $this->host = $options['host'];
        }

        if (isset($options['port']) && $options['port']) {
            $this->port = $options['port'];
        }
    }

    public function run()
    {
        $this->open();

        $status = proc_get_status($this->sResource);
        nLog(__METHOD__, $status);

        return $this->isRunning();
    }

    public function open()
    {
        if (is_null($this->sResource)) {
            $path = 'Service.php';
            $url = $this->getUrl();

            $dirname = dirname(__FILE__);

            $descriptorspec = array(
               0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
               1 => array("pipe", "w"),  // 标准输出，子进程向此管道中写入数据
               2 => array("file", $dirname . "/logs/nLog.log", "a") // 标准错误，写入到一个文件
            );

            $this->sResource = proc_open("php -S $url $path", $descriptorspec, $pipes, $dirname);
        }

        return $this->sResource;
    }

    public function terminate()
    {
        if (is_null($this->sResource)) {
            return true;
        }

        return proc_terminate($this->sResource);
    }

    // 检查是否在运行中
    public function isRunning()
    {
        if (is_null($this->sResource)) {
            return false;
        }

        $status = proc_get_status($this->sResource);

        return $status['running'];
    }

    public function getPort()
    {
        return $this->port;
    }


    public function getHost()
    {
        return $this->host;
    }

    public function getUrl()
    {
        return $this->getHost() . ':' . $this->getPort();
    }

    function __destruct()
    {
        $this->terminate();
    }

    public static function getInstance()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new ServerManager();
            $instance->run();
        }

        return $instance;
    }
}

// FIX: 需要提前执行这个有点奇怪，待优化
ServerManager::getInstance();


