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

    //获取推荐餐饮店铺
    public function getRecommendCatList(){
        $res = model('Index')->getRecommendCatList();

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
        $location = input('post.location');
        $limit = 4;
        $offset = 0;
        $res = model('Index')->getNearMallsInfo($location,$limit,$offset);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //附近餐饮店铺列表
    public function getNearCatList(){
        $location = input('post.location');
        $page = input('post.page',1);
        $limit = 8;
        $offset = $limit * ($page - 1);
        $res = model('Index')->getNearCatList($location,$limit,$offset);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //附近商城店铺列表
    public function getNearShopList(){
        $location = input('post.location');
        $page = input('post.page',1);
        $limit = 8;
        $offset = $limit * ($page - 1);
        $res = model('Index')->getNearShopList($location,$limit,$offset);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取商城店铺列表
    public function getBisList(){
        //获取参数
        $page = input('post.page',1,'intval');
        $location = input('post.location');
        $limit = 8;
        $offset = $limit * ($page - 1);

        $res = Db::table('store_bis')->field('id as bis_id,bis_name,thumb,is_pintuan,brand,address,positions')
            ->where("status = 1 and (positions <> null or positions <> '') ")
            ->limit($offset,$limit)
            ->order('id desc')
            ->select();

        $count = count($res);
        if($count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        $ind = 0;
        foreach($res as $val){
            $positions = $val['positions'];
            $locationJson = model('Index')->execUrl($location,$positions);
            $locationArr = json_decode($locationJson,true);
            $distance = $locationArr['results'][0]['distance'];
            $res[$ind]['distance'] = $distance >= 1000 ? round(($distance / 1000),1).'km' : $distance.'m';
            $ind ++;
        }
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more
        ));
        exit;
    }

    //获取餐饮店铺列表
    public function getCatBisList(){
        //获取参数
        $page = input('post.page',1,'intval');
        $location = input('post.location');
        $limit = 8;
        $offset = $limit * ($page - 1);

        $res = Db::table('cy_bis')->alias('bis')->field("bis.id as bis_id,bis.bis_name,bis.address,bis.positions,img.logo_image")
            ->join('cy_bis_images img','img.bis_id = bis.id','left')
            ->where("bis.status = 1 and (bis.positions <> null or bis.positions <> '') ")
            ->limit($offset,$limit)
            ->order('bis.id desc')
            ->select();

        $count = count($res);
        if($count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        $ind = 0;
        foreach($res as $val){
            $positions = $val['positions'];
            $locationJson = model('Index')->execUrl($location,$positions);
            $locationArr = json_decode($locationJson,true);
            $distance = $locationArr['results'][0]['distance'];
            $res[$ind]['distance'] = $distance >= 1000 ? round(($distance / 1000),1).'km' : $distance.'m';
            $ind ++;
        }
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more
        ));
        exit;
    }
}
