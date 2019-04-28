<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class Table extends Controller{

    //获取该商家所有桌位信息
    public function getAllTablesInfo(){
        //获取参数
        $param = input('post.');
        $res = model('Table')->getAllTablesInfo($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'  => $res
        ));
        exit;
    }
}



