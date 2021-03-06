<?php
/**
 * Created by PhpStorm.
 * User: yangjingquan
 * Date: 2017/9/22
 * Time: 上午9:17
 */

namespace app\business\controller;
use think\Db;
use think\Exception;
use think\Loader;
use think\Log;

Loader::import('RiZhaoShiJieWxPay.WxPayApi',EXTEND_PATH);

class Shijiecollectnotify extends \WxPayNotify{

    public function NotifyProcess($data, &$msg){
        if($data['result_code'] == 'SUCCESS'){
            //第一步 检测库存量 (待开发)
            //-----------------------
            //订单号
            $orderNo = $data['out_trade_no'];
            try{
                //通过订单号查询外部订单信息(订单总金额)
                $out_order_info = Db::table('cy_pay_orders')->field('total_amount')->where('order_no = '.$orderNo)->find();
                //比较微信返回的总金额和外部订单表内的总金额
                if($out_order_info['total_amount'] * 100 == $data['total_fee']){
                    //更改外部订单状态,记录流水号
                    $order_status['order_status'] = 2;
                    $order_status['transaction_id'] = $data['transaction_id'];
                    $out_order_info = Db::table('cy_pay_orders')->where('order_no = '.$orderNo)->update($order_status);
                }
                return true;
            }catch(Exception $ex){
                Log::error($ex);
                return false;
            }
        }else{
            return true;
        }
    }
}