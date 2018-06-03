<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class Members extends Controller{

    //添加会员信息
    public function addMembers(){
        //获取参数
        $param = input('post.');
        $res = model('Members')->addMembers($param);
        echo json_encode(array(
            'statuscode'  => 1
        ));
        exit;
    }
}



