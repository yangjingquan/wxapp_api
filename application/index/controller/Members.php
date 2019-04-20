<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Exception;

class Members extends Base{

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

    //获取个人信息
    public function getMemberInfo(){
        //接收参数
        $param = input('post.');
        try{
            $res = Model('Members')->getMemberInfo($param);
        }catch (Exception $e) {
            return $this->render(false, $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
        }
        return $this->render($res);
    }

    //更新余额
    public function subBalance(){
        //接收参数
        $param = input('post.');
        try{
            $res = Model('Members')->subBalance($param);
        }catch (Exception $e) {
            return $this->render(false, $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
        }
        return $this->render($res);
    }

}



