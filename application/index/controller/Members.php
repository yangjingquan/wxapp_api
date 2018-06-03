<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Members extends Controller{

    //添加会员信息
    public function addMembers(){
        //获取参数
        $param = input('post.');
        $res = model('Members')->addMembers($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'   => $res
        ));
        exit;
    }

    //验证该用户是否已被推荐,若未被推荐,更新推荐用户到成员表中
    public function checkRecStatus(){
        //获取参数
        $param = input('post.');
        model('Members')->checkRecStatus($param);
    }

    //验证该用户是否已被推荐,若未被推荐,更新推荐用户到成员表中
    public function checkRecStatusMulti(){
        //获取参数
        $param = input('post.');

        $res = model('Members')->checkRecStatusMulti($param);
    }
}



