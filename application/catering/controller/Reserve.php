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
        $deposit = input('post.deposit');
        $create_time = date('Y-m-d H:i:s');
        $order_no = '60'.substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').rand(100000,999999);

        //设置数据
        $data = [
            'order_no'  => $order_no,
            'bis_id'  => $bis_id,
            'date'  => $date,
            'time'  => $time,
            'type'  => $type,
            'link_man'  => $link_man,
            'count'  => $count,
            'mobile'  => $mobile,
            'remark'  => $remark,
            'openid'  => $openid,
            'deposit'  => $deposit,
            'create_time'  => $create_time,
            'update_time'  => $create_time
        ];
        //执行操作
        $res = Db::table('cy_pre_orders')->insertGetId($data);
        if($res > 0){
            echo json_encode(array(
                'statuscode'  => 1,
                'result'  => $res
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 0,
                'message'  => '创建订单失败'
            ));
            exit;
        }

    }

    //获取桌位类型信息
    public function getTableTypeInfo(){
        $bis_id = input('get.bis_id',0);
        $res = Db::table('cy_reserve_table_info')
            ->where('bis_id = '.$bis_id.' and status = 1')
            ->order('created_at asc')
            ->select();

        $table_info = $deposit = array();
        if(is_array($res)){
            foreach($res as $val){
                array_push($table_info,$val['table_name']);
                array_push($deposit,$val['deposit']);
            }
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'table_type'  => $table_info,
            'deposit'  => $deposit
        ));
        exit;
    }
}



