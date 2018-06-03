<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Base extends Controller{

    //初始化
    public function _initialize(){
        $res = Db::table(base64_decode('c3RvcmVfZG9tYWlu'))->field('domain_name')->where('id = 1')->find();
        $domain_name = $res['domain_name'];
        $ngrtgfh = $_SERVER['HTTP_HOST'];
        $gdfgr = base64_encode($ngrtgfh.'-asdf');
        if($domain_name != $gdfgr){
            echo '1';
            die;
        }else{
            return true;
        }
    }
}



