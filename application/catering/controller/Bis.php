<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;
use think\Exception;

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

    public function subJifen(){
        //接收参数
        $order_id = input('post.order_id');
        $openid = input('post.openid');

        Db::startTrans();
        try{
            //查询该订单产生的积分
            $jifen = Db::table('cy_mall_sub_orders')->alias('sub')->field('pro.id as pro_id')
                ->join('cy_mall_pro_config con','sub.pro_id = con.id','LEFT')
                ->where('sub.main_id='.$order_id)
                ->SUM('con.ex_jifen * sub.count');

            //更新会员积分
            $mem_where = "mem_id = '$openid' and status = 1";
            Db::table('cy_members')->where($mem_where)->setDec('jifen',$jifen);

            //获取订单号
            $order_res = Db::table('cy_mall_main_orders')->alias('main')->field('main.order_no')
                ->where('main.id='.$order_id)
                ->find();

            //生成积分明细记录
            $jf_data = [
                'mem_id'  => $openid,
                'changed_jifen'  => $jifen,
                'type'  => 2,
                'remark'  => $order_res['order_no'],
                'create_time'  => date('Y-m-d H:i:s'),
            ];
            Db::table('cy_jifen_detailed')->insert($jf_data);

            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            echo json_encode(array(
                'statuscode'  => 1,
                'message'     => $e->getMessage()
            ));
            exit;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'message'     => '添加成功!'
        ));
        exit;
    }

    //积分明细
    public function getJifenDetailed(){
        //接收参数
        $openid = input('post.openid');
        $page = input('post.page',1,'intval');
        $limit = 10;
        $offset = $limit * ($page - 1);

        $where = "mem_id = '$openid' and status = 1";
        $jf_res = Db::table('cy_members')->field('jifen')->where($where)->find();
        $jifen = $jf_res['jifen'];

        $where = "mem_id = '$openid' and status = 1";
        $res = Db::table('cy_jifen_detailed')
            ->where($where)
            ->limit($offset,$limit)
            ->order('create_time desc')
            ->select();

        $ind = 0;
        foreach($res as $val){
            $res[$ind]['changed_jifen'] = floor($val['changed_jifen']);
            $ind ++;
        }

        $count = count($res);
        if($count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'jifen'      => $jifen,
            'has_more'    => $has_more
        ));
        exit;
    }

    //付款成功后添加积分
    public function addJifen(){
        //接收参数
        $order_id = input('post.order_id');
        $openid = input('post.openid');

        Db::startTrans();
        try{
            //查询该订单产生的积分
            $jifen = Db::table('cy_mall_sub_orders')->alias('sub')->field('pro.id as pro_id')
                ->join('cy_mall_pro_config con','sub.pro_id = con.id','LEFT')
                ->join('cy_mall_products pro','con.pro_id = pro.id','LEFT')
                ->where('sub.main_id='.$order_id)
                ->SUM('pro.jifen * sub.count');

            //获取订单号
            $order_res = Db::table('cy_mall_main_orders')->alias('main')->field('main.order_no')
                ->where('main.id='.$order_id)
                ->find();

            //更新会员积分
            $mem_where = "mem_id = '$openid' and status = 1";
            Db::table('cy_members')->where($mem_where)->setInc('jifen',$jifen);

            //生成积分明细记录
            $jf_data = [
                'mem_id'  => $openid,
                'changed_jifen'  => $jifen,
                'type'  => 1,
                'remark'  => $order_res['order_no'],
                'create_time'  => date('Y-m-d H:i:s'),
            ];
            Db::table('cy_jifen_detailed')->insert($jf_data);
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
        }


        echo json_encode(array(
            'statuscode'  => 1,
            'message'     => '添加成功!'
        ));
        exit;
    }

    //余额明细
    public function getBalanceDetail(){
        //接收参数
        $openid = input('post.openid');
        $page = input('post.page',1,'intval');
        $limit = 10;
        $offset = $limit * ($page - 1);

        $where = "mem_id = '$openid'";
        $bisRes = Db::table('cy_members')->field('balance')->where($where)->find();
        $balance = $bisRes['balance'];

        $recordsWhere = "openid = '$openid' and recharge_status = 2 and bis_type = 2";
        $res = Db::table('store_member_recharge_records')
            ->where($recordsWhere)
            ->limit($offset,$limit)
            ->order('created_at desc')
            ->select();

        $ind = 0;
        foreach($res as $val){
            $res[$ind]['amount'] = floatval($val['amount']);
            $ind ++;
        }

        $count = count($res);
        if($count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'balance'      => $balance,
            'has_more'    => $has_more
        ));
        exit;
    }
}



