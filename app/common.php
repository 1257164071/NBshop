<?php
use \think\facade\Request;
function createUrl(string $url = '', array $vars = [], $suffix = true, $domain = false){
    $arr = explode("/",$url);
    if(count($arr) == 1){
        $url = Request::controller(true) . '/' . $url;
    }else if(count($arr) == 2){
        // app('http')->getName()
    }

    return (string)url($url,$vars,$suffix, $domain);
}

    function log_write($msg){
        $data = date('Y-m-d H:i:s',time())."\r\n";
        if(is_array($msg)){
            $msg = var_export($msg,true);
        }
        $data .= $msg."\r\n";
        $file = 'log.txt';
        file_put_contents($file,$data,FILE_APPEND);
    }
    function send_post($url, $post_data) {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
      );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
