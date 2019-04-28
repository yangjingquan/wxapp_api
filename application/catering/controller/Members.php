<?php
namespace app\catering\controller;
use app\index\controller\Base;
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
            'statuscode'  => 1
        ));
        exit;
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



