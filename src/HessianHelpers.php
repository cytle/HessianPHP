<?php

namespace LibHessian;

use DateTime;
use Exception;
use LibHessian\Exceptions\HessianException;
use LibHessian\HessianClasses\SimpleEnum;
use LibHessian\HessianClasses\Basic\Long;
use LibHessian\Configs\BasicWriteFilters;
use LibHessian\Hessian\HessianClient;


/**
 * HessianPHP 简化使用
 *
 */
class HessianHelpers {

    // 执行异常
    CONST EXECUTION_ERROR = 10000;


    protected static $clients = [];

    /**
     * 获取hessian客户端
     * @author xsp
     *
     * @param  string  $url
     * @param  array   $options  配置
     * @return object
     */
    public static function getClient($url, array $options = [])
    {
        // 配置writeFilters，写入前回调
        if (! isset($options['writeFilters'])) {
            $options['writeFilters'] = BasicWriteFilters::getFilters();
        } else if ($options['writeFilters']) {
            $options['writeFilters'] = array_merge(
                BasicWriteFilters::getFilters(),
                $options['writeFilters']
            );
        }

        return static::createClient($url, $options);
    }


    /**
     * 根据缓存情况获取hessian客户端
     * @author xsp
     *
     * @param  string  $url
     * @param  string  $method
     * @param  array   $options  配置
     * @return object
     */
    public static function getClientWithCache($url, $method = '', array $options = [])
    {
        // cache
        if (! isset(static::$clients[$url][$method])) {
            static::$clients[$url][$method] = static::getClient($url, $options);
        }

        return static::$clients[$url][$method];
    }

    /**
     * 使用hessian查询
     * @author xsp
     *
     * @param  string $url
     * @param  string $method
     * @param  array $arguments
     * @return object
     */
    public static function query($url, $method, array $arguments = [], array $options = [])
    {
        try {
            $hessian = static::getClientWithCache($url, $method, $options);
            $result = $hessian->__hessianCall($method, $arguments);

            return $result;
        } catch (Exception $e) {

            $hessianException = new HessianException('Hessian execution error', static::EXECUTION_ERROR, $e);

            $hessianException
                ->setUrl($url)
                ->setMethod($method)
                ->setArguments($arguments);

            throw $hessianException;
        }
    }


    /**
     * 实例化hessian客户端
     * @author xsp
     *
     * @param  string $url
     * @param  HessianOptions|array $options 配置
     * @return object
     */
    public static function createClient($url, array $options = [])
    {
        return new HessianClient($url, $options);
    }

    /**
     * 产生一个枚举类
     * @author xsp
     *
     * @param  string $name
     * @return object
     */
    public static function createEnum($name, $__type = null) {

        return new SimpleEnum($name, $__type);
    }


    /**
     * 产生long
     * @author xsp
     *
     * @param  int $name
     * @return object
     */
    public static function createLong($value)
    {

        return new Long($value);
    }

    /**
     * 产生一个DateTime 实例
     * @author xsp
     *
     * @param  string $time
     * @return object
     */
    public static function createDateTime($time)
    {
        return new DateTime($time);
    }
}
