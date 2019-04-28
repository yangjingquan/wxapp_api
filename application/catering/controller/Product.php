<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class Product extends Controller{

    //获取商品列表
    public function getProInfo(){
        //获取参数
        $bis_id = input('post.bis_id');
        $pro_type = input('post.pro_type');
        $cat_res = model('Products')->getCatInfoByBisId($bis_id,$pro_type);
        $pro_res = model('Products')->getProInfo($bis_id,$cat_res,$pro_type);

        echo json_encode(array(
            'statuscode' => 1,
            'cat_res'    => $cat_res,
            'pro_res'    => $pro_res
        ));
        exit;
    }

    //获取商品详情
    public function getProDetail(){
        $pro_id = input('post.pro_id');
        $res = model('Products')->getProDetail($pro_id);
        echo json_encode(array(
            'statuscode' => 1,
            'result'    => $res
        ));
        exit;
    }


}



