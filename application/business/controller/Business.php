<?php
namespace app\business\controller;
use think\Controller;
use think\Db;

class Business extends Controller{

    //校验邀请码
    public function checkYaoQingMa(){
        //获取参数
        $bis_id = input('post.bis_id');
        $yapqingma = input('post.yaoqingma');
        $res = Db::table('store_yqm_code')->where("bis_id = '$bis_id' and code = '$yapqingma' and status = 1")->find();

        if($res){
            if($res['used'] == 1){
                echo json_encode(array(
                    'statuscode'  => 0,
                    'msg'         => '邀请码已使用'
                ));
                exit;
            }else{
                echo json_encode(array(
                    'statuscode'  => 1,
                    'msg'         => 'success'
                ));
                exit;
            }

        }else{
            echo json_encode(array(
                'statuscode'  => 0,
                'msg'         => '邀请码不合法'
            ));
            exit;
        }
    }

    //添加邀请码
    public function addYaoQingMa(){
        //获取参数
        $openid = input('post.openid');
        $yaoqingma = input('post.yaoqingma');
        $res = model('Business')->addYaoQingMa($openid,$yaoqingma);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $yaoqingma
        ));
        exit;
    }

    //获取邀请码
    public function getYaoQingMa(){
        $openid = input('post.openid');
        $res = model('Business')->getYaoQingMa($openid);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    public function test(){
        for($i=0;$i<5000;$i++){
            $data = [
                'code' => rand(100000,999999),
                'bis_id'  => 50,
                'create_time'  => date('Y-m-d H:i:s')
            ];

            Db::table('store_yqm_code')->insert($data);
        }
    }

}



