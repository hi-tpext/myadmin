<?php

namespace app\common\exception;

use Exception;
use think\exception\Handle;

class Http extends Handle
{

    public function render(Exception $e)
    {
        $code = $e->getCode();
        $message = $e->getMessage();

        $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        $data = [
            'File' => $e->getFile(),
            'file_line' => $e->getLine(),
            'remote' => $remote,
            'method' => $method,
            'uri' => $uri,
        ];

        $data = [
            'code' => $code,
            'msg' => $message,
            'data' => $data,
        ];

        $now = date('Y-m-d H:i:s');
        $content = "[{$now}] ERROR: " . json_encode($data) . "\n";
        $log_file = app()->getRuntimePath() . "log/exception" . DIRECTORY_SEPARATOR . 'error-' . date('Ymd', time()) . ".log";
        $path = dirname($log_file);
        !is_dir($path) && mkdir($path, 0755, true);
        file_put_contents($log_file, $content, FILE_APPEND | LOCK_EX);
        //TODO::开发者对异常的操作
        //可以在此交由系统处理
        return parent::render($e);
    }

}
