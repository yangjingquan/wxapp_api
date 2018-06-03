<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class Reserve extends Controller{

    //添加预定信息
    public function addReserveInfo(){
        //获取参数
        $bis_id = input('post.bis_id');
        $date = input('post.date');
        $time = input('post.time');
        $type = input('post.type');
        $link_man = input('post.link_man');
        $count = input('post.count');
        $mobile = input('post.mobile');
        $remark = input('post.remark');
        $openid = input('post.openid');
        $create_time = date('Y-m-d H:i:s');

        //设置数据
        $data = [
            'bis_id'  => $bis_id,
            'date'  => $date,
            'time'  => $time,
            'type'  => $type,
            'link_man'  => $link_man,
            'count'  => $count,
            'mobile'  => $mobile,
            'remark'  => $remark,
            'openid'  => $openid,
            'create_time'  => $create_time,
            'update_time'  => $create_time
        ];
        //执行操作
        $res = Db::table('cy_pre_orders')->insert($data);
        echo json_encode(array(
            'statuscode'  => 1
        ));
        exit;
    }
}



