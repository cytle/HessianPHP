<?php
namespace LibHessian\Configs;

use LibHessian\Contracts\BasicWriteContract;

/**
*
*/
class BasicWriteFilters
{
    private static $filters = [
        'LibHessian\HessianClasses\Basic\Long'
    ];

    public static function getFilters()
    {
        static $filters = null;

        if (is_null($filters)) {
            foreach (static::$filters as $filter) {
                $filters['@' . $filter] = 'LibHessian\Configs\BasicWriteFilters::callback';
            }
        }

        return $filters;
    }

    public static function callback($obj)
    {
        if ($obj instanceof BasicWriteContract) {
            return $obj->getValue();
        }

        return $obj;
    }
}

