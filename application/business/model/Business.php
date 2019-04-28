<?php
namespace app\business\model;
use think\Model;
use think\Db;

class Business extends Model{

    //添加邀请码
    public function addYaoQingMa($openid,$yaoqingma){
        $data = [
            'yaoqingma' => $yaoqingma
        ];

        $yqm_data = [
            'used'  => 1
        ];
        $res = DB::table('store_members')->where("mem_id = '$openid' and status = 1 ")->update($data);
        $yqm_res = DB::table('store_yqm_code')->where("code = '$yaoqingma'")->update($yqm_data);

        if($res){
            return $res;
        }else{
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加失败'
            ));
            exit;
        }
    }

    //获取邀请码
    public function getYaoQingMa($openid){
        $res = DB::table('store_members')->field('yaoqingma')->where("mem_id = '$openid' and status = 1 ")->find();
        if($res){
            return $res;
        }else{
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '邀请码获取失败'
            ));
            exit;
        }
    }

}