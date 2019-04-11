<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class Bis extends Controller{

    //通过pro_id获取bis_id
    public function getBisIDByProId(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = Db::table('cy_mall_products')->alias('pro')->field('pro.bis_id,bis.is_pay')
            ->join('cy_bis bis','pro.bis_id = bis.id','left')
            ->where('pro.id = '.$pro_id)
            ->find();

        $bis_id = $res['bis_id'];
        $is_pay = $res['is_pay'];
        echo json_encode(array(
            'statuscode'  => 1,
            'bis_id'      => $bis_id,
            'is_pay'      => $is_pay
        ));
        exit;
    }

    public function subJifenOrg(){
        //接收参数
        $order_id = input('post.order_id');
        $openid = input('post.openid');
        $bis_id = input('post.bis_id');

        //查询该订单产生的积分
        $order_res = Db::table('cy_mall_main_orders')->alias('main')->field('main.jifen,main.order_no')
            ->where('main.id='.$order_id)
            ->find();
        $jifen = $order_res['jifen'];

        //更新会员积分
        $mem_where = "mem_id = '$openid' and status = 1";
        $mem_res = Db::table('cy_members')->field('jifen')->where($mem_where)->find();
        $mem_jifen = $mem_res['jifen'];
        $new_mem_jifen['jifen'] = $mem_jifen - $jifen;
        $new_mem_res = Db::table('cy_members')->where($mem_where)->update($new_mem_jifen);

        //生成积分明细记录
        $jf_data = [
            'mem_id'  => $openid,
            'changed_jifen'  => $jifen,
            'type'  => 2,
            'remark'  => $order_res['order_no'],
            'create_time'  => date('Y-m-d H:i:s'),
        ];
        $ji_res = Db::table('cy_jifen_detailed')->insert($jf_data);

        echo json_encode(array(
            'statuscode'  => 1,
            'message'     => '添加成功!'
        ));
        exit;
    }
}



