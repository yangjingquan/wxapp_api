<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class Category extends Controller{

    //获取一级分类信息
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

    //根据一级id获取二级分类信息
    public function getSecondCarInfoById(){
        $cat1_id = input('post.cat_id');
        $res = model('Category')->getSecondCarInfoById($cat1_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据一级分类id获取商品信息
    public function getProInfoByFirstId(){
        $param = input('post.');
        $res = model('Products')->getProInfoByFirstId($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据二级分类id获取商品信息
    public function getProInfoBySecondId(){
        $param = input('post.');
        $res = model('Products')->getProInfoBySecondId($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

}
