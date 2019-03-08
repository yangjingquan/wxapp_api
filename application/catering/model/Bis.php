<?php
namespace app\catering\model;
use think\Model;
use think\Db;

class Bis extends Model{
    //获取店铺信息
    public function getBisInfo($bis_id){
        $res = Db::table('cy_bis')
            ->field('bis_name,scope,business_time,min_price,link_tel,link_mobile,citys,address,lunch_box_fee,distribution_fee,is_pay')
            ->where('id = '.$bis_id)
            ->find();
        return $res;
    }

    //获取店铺图片信息
    public function getBisImgInfo($bis_id){
        $res = Db::table('cy_bis_images')
            ->where('bis_id = '.$bis_id)
            ->find();
        return $res;
    }

    //获取店铺活动信息
    public function getBisActInfo($bis_id){
        $res = Db::table('cy_bis')->alias('bis')
            ->field('act.type,act.activity_name,act.max,act.lose')
            ->join('cy_activitys act','bis.id = act.bis_id','LEFT')
            ->where('act.bis_id = '.$bis_id .' and act.status = 1')
            ->select();
        return $res;
    }

    //获取banner信息
    public function getBannerInfo($bis_id){
        $res = Db::table('cy_banners')
            ->where('bis_id = '.$bis_id .' and status = 1')
            ->select();
        return $res;
    }
}
