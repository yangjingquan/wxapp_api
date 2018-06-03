<?php
namespace app\catering\model;
use think\Model;
use think\Db;

class Table extends Model{

    //获取该商家所有桌位信息
    public function getAllTablesInfo($param){
        //获取参数
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';

        $res = Db::table('cy_tables')
                ->where('shows = 1 and bis_id = '.$bis_id .' and status = 1')
                ->select();

        return $res;
    }

}