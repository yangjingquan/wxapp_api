<?php
namespace app\catering\model;
use think\Model;
use think\Db;

class Category extends Model{
    //获取一级分类信息
    public function getCategoryInfo($bis_id){
        $res = Db::table('cy_category')->field('id as cat_id,cat_name')
                ->where('bis_id = '.$bis_id.' and status = 1')
                ->order('listorder desc')
                ->select();
        return $res;
    }
}
