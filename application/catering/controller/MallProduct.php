<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class MallProduct extends Controller{

    //获取积分商品配置信息(多用户一维规格版)
    public function getJfProConfigInfo(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = model('MallProducts')->getJfProConfigInfo($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据一级名称获取积分商品信息(多用户一维规格版)
    public function getJfConById(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = model('MallProducts')->getJfConById($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取选中商品单价
    public function getSelectedProPrice(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = model('Products')->getSelectedProPrice($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取积分商品详情
    public function getJfProDetail(){
        $pro_id = input('post.pro_id');
        $res = model('MallProducts')->getJfProDetail($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取积分商品列表(单用户版)
    public function getJfProductInfo(){
        //获取参数
        $page = input('get.page',1,'intval');
        $bis_id = input('get.bis_id',1,'intval');
        $limit = 10;
        $offset = $limit * ($page - 1);
        $res = model('MallProducts')->getJfProductInfo($bis_id,$limit,$offset);
        $count = count($res);
        if($count == $limit){
            $has_more = true;
        }else{
            $has_more = false;
        }
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more
        ));
        exit;
    }
}



