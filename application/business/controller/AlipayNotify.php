<?php
/**
 * Created by PhpStorm.
 * User: yangjingquan
 * Date: 2018/11/2
 * Time: 9:40 PM
 */
namespace app\business\controller;
use think\Controller;
use think\Db;

vendor('alipay.aop.AopClient');
vendor('alipay.aop.AlipayConfig');
vendor('alipay.aop.request.AlipayTradeAppPayRequest');

class AlipayNotify extends Controller{

    public function notify(){
        $aop = new \AopClient();
        $config = new \AlipayConfig();
        $aop->alipayrsaPublicKey = $config::ALIPAY_RSA_PUBLIC_KEY;
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        //验签
        if($flag){
            //支付成功:TRADE_SUCCESS   交易完成：TRADE_FINISHED
            if($_POST['trade_status'] == 'TRADE_SUCCESS' || $_POST['trade_status'] == 'TRADE_FINISHED'){
                //获取订单号
                $ordersn = $_POST['out_trade_no'];
                //交易号
                $trade_no = $_POST['trade_no'];
                //订单支付时间
                $gmt_payment = $_POST['gmt_payment'];
                //转换为时间戳
                $gtime = strtotime($gmt_payment);
                //交易金额
                $pay_money = $_POST['receipt_amount'];
                //此处编写回调处理逻辑
                header("Content-type: text/html; charset=utf-8");
                //设置更新的元素数组
                $arr = array(
                    'order_status' => 3,
//                    'transaction_id' => $data['transaction_id'],
                    'pay_time' => date('Y-m-d H:i:s', time()),
                    'update_time' => date('Y-m-d H:i:s', time())
                );

                //更新订单状态
                $re = Db::table('store_main_orders')->where(['order_no' => $ordersn])->update($arr);
            }else{
                exit('fail1');
            }
        }else{
            exit('fail2');
        }
    }
}
