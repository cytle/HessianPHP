<?php

defined('__TEST_UNTIL_PATH__') || define('__TEST_UNTIL_PATH__', dirname(__FILE__));
defined('REQUEST_LOG_ID') || define('REQUEST_LOG_ID', substr(base_convert(rand(), 10, 16), 0, 6));

date_default_timezone_set('PRC');

if (! function_exists('nLog')) {
    function nLog($name, $msg) {
        $file = fopen(__TEST_UNTIL_PATH__ . "/logs/nLog.log","a");

        $time = date("Y-m-d H:i:s");
        if ($msg instanceof Exception) {
            $msg = $msg->__toString();
        } else if (is_numeric($msg)) {
            // $msg = sprintf("%.2f", $msg);

            $msg = strval($msg);

        } else if (! is_string($msg)) {
            $msg = var_export($msg, true);
        }

        $id = REQUEST_LOG_ID;

        $str = "[$time $id]$name: $msg";

        fwrite($file, $str . PHP_EOL);
        fclose($file);
    }
}


?>
