<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Recommend extends Model{
    //获取首页banner图
    public function getBanners($bis_id){
        $res = Db::table('store_recommend')->where('bis_id = '.$bis_id.' and type = 1 and status = 1')->order('listorder desc,create_time desc')->limit(3)->select();
        return $res;
    }
}
