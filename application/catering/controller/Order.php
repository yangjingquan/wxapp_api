<?php
namespace app\catering\controller;
use app\index\controller\Base;
use think\Controller;
use think\Db;
use think\Exception;

class Order extends Base{

    //获取点餐/外卖订单信息
    public function getNormalOrderInfo(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->getNormalOrderInfo($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成点餐订单
    public function makeDcOrder(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->makeDcOrder($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成外卖订单
    public function makeWmOrder(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->makeWmOrder($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }


    //取消订单
    public function cancelOrder(){
        $order_id = input('post.order_id');
        $data['order_status'] = 2;
        $res = Db::table('cy_pre_orders')->where('id = '.$order_id)->update($data);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //查询点餐订单详情
    public function getDcOrderDetail(){
        $order_id = input('post.order_id');
        $res = model('Order')->getDcOrderDetail($order_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //查询外卖订单详情
    public function getWmOrderDetail(){
        $order_id = input('post.order_id');
        $res = model('Order')->getWmOrderDetail($order_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取预订订单信息
    public function getReserveOrderInfo(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->getReserveOrderInfo($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成收银订单
    public function makeSyOrder(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->makeSyOrder($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成充值订单
    public function makeRechargeOrder(){
        //接收参数
        $param = input('post.');
        try{
            $res = Model('Order')->makeRechargeOrder($param);
        }catch (Exception $e) {
            return $this->render(false, $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
        }
        return $this->render($res);
    }

}



