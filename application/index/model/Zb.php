<?php
/**
 * User: Wangsx
 * Date: 2019/4/15
 */

namespace app\index\model;


use think\Db;
use think\Model;

class Zb extends Model
{

    
    //获取会员信息
    public function getUserinfo($param)
    {

       $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $res = Db::table('store_members')->where("mem_id = '$mem_id' and status = 1")->find();
        if (!$res) {
            echo json_encode([
                'statuscode' => 0,
                'message' => '获取失败'
            ]);
            exit();
        }else{
            return $res;

        }
    }

    //修改会员信息
    public function editUserinfo($param)
    {
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $truename = !empty($param['truename']) ? $param['truename'] : '';
        $mobile = !empty($param['mobile']) ? $param['mobile'] : '';
        $address = !empty($param['address']) ? $param['address'] : '';
        $email = !empty($param['email']) ? $param['email'] : '';

        $re = Db::table('store_members')->where("mem_id = '$mem_id' and status = 1")->find();
        $data = [
            'truename'=>$truename,
            'mobile'=>$mobile,
            'address'=>$address,
            'email'=>$email,
        ];

        $res = Db::table('store_members')->where("mem_id = '$mem_id' and status = 1")->update($data);

        return $res;
    }



}