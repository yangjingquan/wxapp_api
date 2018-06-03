<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Product extends Controller{

    //获取商品配置信息(二维规格)
    public function getProConfigInfo(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = model('Products')->getProConfigInfo($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取商品配置信息(一维规格普通版)
    public function getProConfigInfoOneDimensional(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = model('Products')->getProConfigInfoOneDimensional($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取积分商品配置信息(多用户一维规格版)
    public function getJfProConfigInfo(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = model('Products')->getJfProConfigInfo($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取商品配置信息(一维规格拼团版)
    public function getProConfigInfoOneDimensionalByGroup(){
        //获取参数
        $pro_id = input('post.pro_id');
        $from = input('post.from');
        $res = model('Products')->getProConfigInfoOneDimensionalByGroup($pro_id,$from);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据一级名称&商品id获取二级配置信息(二维规格)
    public function getConfig2InfoById(){
        //获取参数
        $pro_id = input('post.pro_id');
        $con_info = input('post.con_info');
        $res = model('Products')->getConfig2InfoById($pro_id,$con_info);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据一级名称获取商品信息(一维规格普通版)
    public function getConByIdOneDimensional(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = model('Products')->getConByIdOneDimensional($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据一级名称获取商品信息(一维规格拼团版)
    public function getConByIdOneDimensionalByGroup(){
        //获取参数
        $pro_id = input('post.pro_id');
        $from = input('post.from');
        $res = model('Products')->getConByIdOneDimensionalByGroup($pro_id,$from);
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
        $res = model('Products')->getJfConById($pro_id);
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

    //获取指定商品的拼团信息
    public function getGroupInfoByProId(){
        $pro_id = input('post.pro_id');
        $res = model('Products')->getGroupInfoByProId($pro_id);
        $count = model('Products')->getGroupCountByProId($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'count'       => $count,
        ));
        exit;
    }

    //搜索(获取商品信息-拼团版)
    public function getProInfoBySearch(){
        //获取参数
        $bis_id = input('post.bis_id');
        $param = input('post.param');
        $page = input('post.page',1,'intval');

        $limit = 10;
        $offset = $limit * ($page - 1);

        $where = "(pro.p_name like '%$param%' or pro.keywords like '%$param%') and pro.bis_id = $bis_id and pro.status = 1 and pro.on_sale = 1";
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.pintuan_price,pro.pintuan_count,pro.associator_price')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where($where)
            ->order('pro.update_time desc')
            ->limit($offset,$limit)
            ->select();

        $count = count($res);

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

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

    //搜索(获取商品信息-普通版)
    public function getOrgProInfoBySearch(){
        //获取参数
        $bis_id = input('post.bis_id');
        $param = input('post.param');
        $page = input('post.page',1,'intval');

        $limit = 10;
        $offset = $limit * ($page - 1);

        $where = "(pro.p_name like '%$param%' or pro.keywords like '%$param%') and pro.bis_id = $bis_id and pro.status = 1 and pro.on_sale = 1";
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.original_price,pro.associator_price')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where($where)
            ->order('pro.update_time desc')
            ->limit($offset,$limit)
            ->select();

        $count = count($res);

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

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

    //查询热门搜索词汇
    public function getHotSearchWords(){
        $bis_id = input('post.bis_id');
        $res = Db::table('store_search_words')->field('word')->where('bis_id = '.$bis_id.' and status = 1')->select();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取积分商品列表(多用户版)
    public function getJfProInfo(){
        //获取参数
        $page = input('post.page',1,'intval');
        $limit = 10;
        $offset = $limit * ($page - 1);
        $res = model('Products')->getJfProInfo($limit,$offset);
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

    //获取积分商品详情
    public function getJfProDetail(){
        $pro_id = input('post.pro_id');
        $res = model('Products')->getJfProDetail($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }
}



