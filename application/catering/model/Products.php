<?php
namespace app\catering\model;
use think\Model;
use think\Db;

class Products extends Model{
    //获取商品列表
    public function getProInfo($bis_id,$cat_res,$pro_type){
        $res_info = array();
        $index = 0;
        foreach($cat_res as $val){
            $res_info[$index]['cat_id'] = $val['cat_id'];
            $res_info[$index]['cat_name'] = $val['cat_name'];
            $res_info[$index]['pro_info'] = $this->getProInfoByCatId($bis_id,$val['cat_id'],$pro_type);
            $index ++;
        }
        return $res_info;
    }

    //根据分类获取商品信息
    public function getProInfoByCatId($bis_id,$cat_id,$pro_type){
        if($pro_type == 1){
            $con = " and p.pro_type in(1,3)";
        }else{
            $con = " and p.pro_type in(2,3)";
        }
        $res = Db::table('cy_products')->alias('p')->field('p.id, p.p_name,p.original_price,p.sold,p.image')
            ->join('cy_category cat', 'p.cat_id = cat.id','LEFT')
            ->where('p.cat_id = '.$cat_id .' and p.bis_id = '.$bis_id.$con.' and p.on_sale = 1 and p.status = 1')
            ->select();
        $index = 0;
        $res_info = array();
        foreach($res as $val){
            $res_info[$index]['id'] = $val['id'];
            $res_info[$index]['p_name'] = $val['p_name'];
            $res_info[$index]['original_price'] = $val['original_price'];
            $res_info[$index]['sold'] = $val['sold'];
            $res_info[$index]['image'] = $val['image'];
            $res_info[$index]['selected_count'] = 0;
            $index ++;
        }
        return $res_info;
    }

    //获取该店铺已经上传商品所对应的全部分类
    public function getCatInfoByBisId($bis_id,$pro_type){
        if($pro_type == 1){
            $con = " and pro_type in(1,3)";
        }else{
            $con = " and pro_type in(2,3)";
        }
        $res = Db::table('cy_products')->field('cat_id')
            ->where('bis_id = '.$bis_id.$con.' and status = 1 and on_sale = 1')
            ->select();
        $catid_res = array();
        foreach($res as $val){
            if(!in_array($val['cat_id'],$catid_res)){
                array_push($catid_res,$val['cat_id']);
            }else{
                continue;
            }
        }
        $index = 0;
        $cat_info = array();
        foreach($catid_res as $val){
            $cat_info[$index]['cat_id'] = $val;
            $cat_info[$index]['cat_name'] = $this->getCatNameById($val)['cat_name'];
            $index ++;
        }
        return $cat_info;
    }

    //根据分类id获取分类名称
    public function getCatNameById($cat_id){
        $res = Db::table('cy_category')->field('cat_name')->where('id = '.$cat_id)->find();
        return $res;
    }

    //获取商品详情
    public function getProDetail($pro_id){
        $res = Db::table('cy_products')->alias('pro')->field('pro.*')
            ->where("pro.id = $pro_id")
            ->find();

        if(!empty($res['detail_images'])){
            $temp_detail_images = json_decode($res['detail_images'],true);
            foreach($temp_detail_images as $item){
                if(!empty($item)){
                    $detail_images[] = $item;
                }
            }
            $res['detail_images'] = $detail_images;
        }

        return $res;
    }

    //获取推荐商品
    public function getRecommendProInfo($bis_id){
        if(empty($bis_id)){
            return array();
        }

        $res = Db::table('cy_products')
            ->where('bis_id = '.$bis_id.' and on_sale = 1 and status = 1 and is_recommend = 1')
            ->order('update_time desc')
            ->limit(8)
            ->select();

        return $res;
    }

    //获取推荐商品
    public function getNewestProInfo($bis_id){
        if(empty($bis_id)){
            return array();
        }

        $res = Db::table('cy_products')
            ->where('bis_id = '.$bis_id.' and on_sale = 1 and status = 1')
            ->order('update_time desc')
            ->limit(8)
            ->select();

        return $res;
    }

}
