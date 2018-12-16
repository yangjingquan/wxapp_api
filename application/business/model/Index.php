<?php
namespace app\business\model;
use think\Model;
use think\Db;

class Index extends Model{

    //获取新品列表(多用户版)
    public function getNewProInfoMut(){
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.original_price,pro.associator_price,pro.jifen * 5 as jifen')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where('bis_id !=34 and pro.on_sale = 1 and pro.status = 1 and i.status = 1 and pro.is_jf_product = 0')
            ->order('pro.create_time desc')
            ->limit(9)
            ->select();

        $ind = 0;
        foreach($res as $val){
            $jifen = $val['jifen'];
            $res[$ind]['jifen'] = floor($jifen);
            $ind ++;
        }

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'      => '暂无数据'
            ));
            exit;
        }

        return $res;
    }

    public function getRecommendMallList(){
        $where = [
            'is_recommend'  => 1,
            'status'  => 1,
        ];
        $order = [
            'updated_time'  => 'desc'
        ];

        $res = Db::table('store_bis')->alias('bis')->field('id as bis_id,bis_name,thumb')
            ->where($where)
            ->order($order)
            ->limit(8)
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        return $res;
    }

    public function getCatRecommendMallList(){
        $where = [
            'is_recommend'  => 1,
            'status'  => 1,
        ];
        $order = [
            'updated_time'  => 'desc'
        ];

        $res = Db::table('cy_bis')->alias('bis')->field('id as bis_id,bis_name,thumb')
            ->where($where)
            ->order($order)
            ->limit(8)
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        return $res;
    }
    //获取推荐商品列表(多用户版)
    public function getRecProInfo(){
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.original_price,pro.associator_price,pro.jifen * 5 as jifen')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where('pro.on_sale = 1 and pro.is_recommend = 1 and pro.status = 1 and i.status = 1 and pro.is_jf_product = 0')
            ->order('pro.update_time desc')
            ->limit(9)
            ->select();

        return $res;
    }

    public function getProvinceInfo($provinceId){
        $res = Db::table('store_province')->where("id='$provinceId'")->find();
        return $res['p_name'];
    }

    public function getCityInfo($cityId){
        $res = Db::table('store_city')->where("id='$cityId'")->find();
        $c_name = $res['c_name'];
        return $c_name;
    }

    public function getNearMallInfo(){
        $where = [
            'bis.is_recommend'  => 1,
            'bis.status'  => 1,
            'br.status'  => 1,
        ];
        $order = [
            'bis.updated_time'  => 'desc'
        ];

        $res = Db::table('store_bis')->alias('bis')->field('bis.id as bis_id,bis.bis_name,bis.citys,bis.address,bis.thumb,br.brand_name')
            ->join('store_brand br','br.bis_id = bis.id','left')
            ->where($where)
            ->order($order)
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        foreach($res as $item){
            $citys = $item['citys'];
            $citys = explode(',',$citys);
            $provinceId = $citys[0];
            $cityId = $citys[1];
            $province = $this->getProvinceInfo($provinceId);
            $city = $this->getCityInfo($cityId);
            $res[$index]['address'] = $province.$city.$item['address'];
            $index ++;
        }
        return $res;
    }

}
