<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class Address extends Controller{

    //获取地址信息
    public function getAddressInfo(){
        //获取参数
        $openid = input('post.openid');
        $res = model('Address')->getAddressInfo($openid);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //添加地址
    public function addAddress(){
        //获取参数
        $param = input('post.');
        $res = model('Address')->addAddress($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //编辑地址(返回地址信息)
    public function getAddressInfoById(){
        $a_id = input('post.aid');
        $res = model('Address')->getAddressInfoById($a_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //更新地址信息
    public function updateAddress(){
        //获取参数
        $param = input('post.');
        $res = model('Address')->updateAddress($param);
        echo json_encode(array(
            'statuscode'  => 1
        ));
        exit;
    }

    //下单时选择地址
    public function chooseAddress(){
        //获取参数
        $param = input('post.');
        $res = model('Address')->chooseAddress($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据id删除地址
    public function deleteAddress(){
        $address_id = input('post.address_id');
        $data['status'] = -1;
        $res = Db::table('cy_address')->where('id = '.$address_id)->update($data);
        if($res){
            echo json_encode(array(
                'statuscode'  => 1
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 0
            ));
            exit;
        }
    }
}



