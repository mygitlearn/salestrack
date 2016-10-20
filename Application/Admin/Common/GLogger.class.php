<?php
namespace Admin\Common;

class GLogger
{
    const DEBUG = 1;    // Most Verbose
    const INFO = 2;    // ...
    const WARN = 3;    // ...
    const ERROR = 4;    // ...
    const FATAL = 5;    // Least Verbose
    const OFF = 6;    // Nothing at all.

    const TAIL_N = 100;//tail n
    const LOG_PATH = '/Public/Logs/';//file full path
    const LOGFILE = 'log.txt';
    const LOG_HTTP_FILE = 'http.txt';

//    const LOG_OPEN = 1;
//    const OPEN_FAILED = 2;
//    const LOG_CLOSED = 3;

    /* Public members: Not so much of an example of encapsulation, but that's okay. */
    //public static $Log_Status = GLogger::LOG_CLOSED;
    public static $DateFormat = "Y-m-d H:i:s";

    //public static $MessageQueue;


    private static function tail($filename, $n = 100)
    {
        //logger('n:'.$n);
        $n++;
        $file = fopen($filename, 'r');
        fseek($file, $n * 1024 * -1, SEEK_END);
       //fgets($file);
        $lines = array();
        while ($line = fgets($file)) {
            $lines[] = trim($line);
        }
        fclose($file);
        //return array_slice($lines, -$n);
        return $lines;
    }

    public static function view($tail = 100, $level = 'info')
    {
        $path = C('g_log_path');
        if ($path == false) {
            $path = getcwd() . GLogger::LOG_PATH;
        }
        $file = $path . GLogger::LOGFILE;
        if ($tail > 0) {
            $tail_n = $tail;
        } else {
            $tail_n = intval(C('tail_n'));
            if ($tail_n <= 0) {
                $tail_n = 100;
            }
        }

        //读取tail n records
        $lines = self::tail($file, $tail_n);
        //logger($lines);
        return $lines;
    }

    public static function auto_log_http()
    {

        //系统参数
        $ip = get_client_ip();
        $method = $_SERVER['REQUEST_METHOD'];
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $cookie = $_SERVER['HTTP_COOKIE'];
        $params = $_REQUEST;

        self::log_http($ip, $method, $host, $uri, $agent, $cookie, $params);
    }


    public static function log($priority, $args)
    {
        $path = C('g_log_path');
        if ($path == false) {
            $path = getcwd() . GLogger::LOG_PATH;
        }

        if ($priority == GLogger::OFF) {
            return;
        }

        //解析args
        $params = array();
        foreach ($args as $key => $value) {
            if (is_array($value)) {
                $json = json_encode($value);
                $params[] = '[@' . $json . '@]';
            } else {
                $params[] = $value;
            }
        }
//        logger('params:');
//        logger($params);

        $line = implode('<-->', $params);

        //写日志
        if ($line == true) {
            $status = self::getTimeLine($priority);
            self::WriteFreeFormLine($path, "$status $line \n");
        }
    }

    public static function log_http()
    {
        $path = C('g_log_path');
        if ($path == false) {
            $path = getcwd() . GLogger::LOG_PATH;
        }

        $priority = GLogger::INFO;

        $args = func_get_args();
        //解析args
        $params = array();
        foreach ($args as $key => $value) {
            if (is_array($value)) {
                $json = json_encode($value);
                $params[] = '[@' . $json . '@]';
            } else {
                $params[] = $value;
            }
        }

        $line = implode('<-->', $params);

        //写日志
        if ($line == true) {
            $status = self::getTimeLine($priority);
            self::WriteFreeFormLine($path, "$status $line \n", true);
        }
    }

    private static function get_file_handle($file)
    {
        if (file_exists($file)) {
            if (!is_writable($file)) {
                //logger("The file exists, but could not be opened for writing . Check that appropriate permissions have been set .");
                return null;
            }
        }

        if ($file_handle = fopen($file, "a")) {
            //logger("The log file was opened successfully");
        } else {
            //logger("The file could not be opened . Check permissions .");
        }

        return $file_handle;
    }


    private static function WriteFreeFormLine($path, $line, $is_http = false)
    {
        if ($is_http) {
            $file = $path . GLogger::LOG_HTTP_FILE;
        } else {
            $file = $path . GLogger::LOGFILE;
        }
        if (ceil(filesize($file) / 1024 / 1024) >= 500) {
            if ($is_http) {
                copy($file, $path . "/" . "http_" . date("YmdHis") . ".log");
            } else {
                copy($file, $path . "/" . date("YmdHi") . ".log");
            }
            fclose(fopen($file, 'w'));
        }

        $file_handle = self::get_file_handle($file);
        if ($file_handle) {
            if (fwrite($file_handle, $line) === false) {
                //logger("The file could not be written to . Check that appropriate permissions have been set . ");
            }
            fclose($file_handle);
        }
        //logger(self::$MessageQueue);
    }

    private function getTimeLine($level)
    {
        $time = date(self::$DateFormat);

        switch ($level) {
            case GLogger::INFO:
                return "$time - INFO  <-->";
            case GLogger::WARN:
                return "$time - WARN  <-->";
            case GLogger::DEBUG:
                return "$time - DEBUG <-->";
            case GLogger::ERROR:
                return "$time - ERROR <-->";
            case GLogger::FATAL:
                return "$time - FATAL <-->";
            default:
                return "$time - LOG   <-->";
        }
    }


    public static function info()
    {
        $args = func_get_args();
        self::log(GLogger::INFO, $args);
    }

    public static function i()
    {
        $args = func_get_args();
        self::log(GLogger::INFO, $args);
    }

    public static function debug()
    {
        $args = func_get_args();
        self::log(GLogger::DEBUG, $args);
    }

    public static function d()
    {
        $args = func_get_args();
        self::log(GLogger::DEBUG, $args);
    }

    public static function warn()
    {
        $args = func_get_args();
        self::log(GLogger::WARN, $args);
    }

    public static function w()
    {
        $args = func_get_args();
        self::log(GLogger::WARN, $args);
    }

    public static function error()
    {
        $args = func_get_args();
        self::log(GLogger::ERROR, $args);
    }

    public static function e()
    {
        $args = func_get_args();
        self::log(GLogger::ERROR, $args);
    }

    public static function fatal()
    {
        $args = func_get_args();
        self::log(GLogger::FATAL, $args);
    }

    public static function f()
    {
        $args = func_get_args();
        self::log(GLogger::FATAL, $args);
    }


}