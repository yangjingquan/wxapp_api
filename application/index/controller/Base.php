<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Base extends Controller{

    //初始化
//    public function _initialize(){
//        $res = Db::table(base64_decode('c3RvcmVfZG9tYWlu'))->field('domain_name')->where('id = 1')->find();
//        $domain_name = $res['domain_name'];
//        $ngrtgfh = $_SERVER['HTTP_HOST'];
//        $gdfgr = base64_encode($ngrtgfh.'-asdf');
//        if($domain_name != $gdfgr){
//            echo '1';
//            die;
//        }else{
//            return true;
//        }
//    }

    public function render($data, $code = 0, $msg = "ok", $file = null, $line = 0){
        static $is_rendered;
        $now = microtime(true);
        $code = intval($code);

        $response = array(
            'response' => array(
                'statuscode' => $code,
                'message' => $msg,
                'timestamp' => $now,
                'file' => $file,
                'line' => $line,
            ),
            'data' => $data,
        );

        header("Content-type:application/json");
        $response = json_encode($response);

        //避免重复输出
        if ($is_rendered === true) {
            return;
        }

        $is_rendered = true;
        echo $response;
    }

}



