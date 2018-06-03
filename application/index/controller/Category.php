<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Category extends Controller{

    //获取一级分类信息(单用户版)
    public function getFirstCatInfo(){
        //获取参数
        $bis_id = input('get.bis_id');
        $res = model('Category')->getFirstCatInfo($bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取一级分类信息(多用户版)
    public function getFirstCatInfoMulti(){
        $res = model('Category')->getFirstCatInfoMulti();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据一级id获取二级分类信息(单用户版)
    public function getSecondCarInfoById(){
        $cat1_id = input('post.cat_id');
        $res = model('Category')->getSecondCarInfoById($cat1_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据一级id获取二级分类信息(多用户版)
    public function getSecondCarInfoByIdMulti(){
        $cat1_id = input('post.cat_id');
        $res = model('Category')->getSecondCarInfoByIdMulti($cat1_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据一级分类id获取商品信息(单用户版)
    public function getProInfoByFirstId(){
        $param = input('post.');
        $res = model('Products')->getProInfoByFirstId($param);
        $count = model('Products')->getProInfoByFirstIdCount($param);
        if($count == 10){
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

    //根据一级分类id获取商品信息(多用户版)
    public function getProInfoByFirstIdMulti(){
        $param = input('post.');
        $res = model('Products')->getProInfoByFirstIdMulti($param);
        $count = model('Products')->getProInfoByFirstIdCountMulti($param);
        if($count == 10){
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

    //根据二级分类id获取商品信息(单用户版)
    public function getProInfoBySecondId(){
        $param = input('post.');
        $res = model('Products')->getProInfoBySecondId($param);
        $count = model('Products')->getProInfoBySecondIdCount($param);
        if($count == 10){
            $has_more = true;
        }else{
            $has_more = false;
        }
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more,
            'count'  =>$count
        ));
        exit;
    }

    //根据二级分类id获取商品信息(多用户版)
    public function getProInfoBySecondIdMulti(){
        $param = input('post.');
        $res = model('Products')->getProInfoBySecondIdMulti($param);
        $count = model('Products')->getProInfoBySecondIdCountMulti($param);
        if($count == 10){
            $has_more = true;
        }else{
            $has_more = false;
        }
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more,
            'count'  =>$count
        ));
        exit;
    }

    //根据一级分类id获取商品信息(拼团版)
    public function getGroupProInfoByFirstId(){
        $param = input('post.');
        $res = model('Products')->getGroupProInfoByFirstId($param);
        $count = model('Products')->getGroupProInfoByFirstIdCount($param);
        if($count == 10){
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


    //根据二级分类id获取商品信息(拼团版)
    public function getGroupProInfoBySecondId(){
        $param = input('post.');
        $res = model('Products')->getGroupProInfoBySecondId($param);
        $count = model('Products')->getGroupProInfoBySecondIdCount($param);
        if($count == 10){
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
