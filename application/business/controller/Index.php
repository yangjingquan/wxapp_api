<?php
namespace app\business\controller;
use think\Controller;
use think\Db;
class Index extends Controller{

    //获取新品列表(多用户版)
    public function getNewProInfoMut(){
        //获取参数
        $bis_id = input('get.bis_id');
        $res = model('Index')->getNewProInfoMut($bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取广告位信息
    public function getAdsInfo(){
        $where = [
            'status'  => 1
        ];

        $order = [
            'listorder'  => 'desc',
            'created_time'  => 'desc'
        ];
        $res = Db::table('store_ads')->where($where)->order($order)->select();

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取推荐商城店铺
    public function getRecommendMallList(){
        $res = model('Index')->getRecommendMallList();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 1,
                'result'      => $res
            ));
            exit;
        }
    }

    //获取餐饮推荐商城店铺
    public function getCatRecommendMallList(){
        $res = model('Index')->getCatRecommendMallList();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 1,
                'result'      => $res
            ));
            exit;
        }
    }

    //获取推荐商品列表
    public function getRecProInfo(){
        $res = model('Index')->getRecProInfo();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取附近店铺
   public function getNearMallInfo(){
       $res = model('Index')->getNearMallInfo();
       echo json_encode(array(
           'statuscode'  => 1,
           'result'      => $res
       ));
       exit;
   }
}
