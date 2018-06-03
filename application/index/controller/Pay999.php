<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Loader;

Loader::import('WxPay999.WxPayApi',EXTEND_PATH);

class Pay999 extends Controller{

    public function pay(){
        $param = input('post.');
        $res = $this->makeWxPreOrder($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成微信预订单
    public function makeWxPreOrder($param){
        $WxPayConfig = new \WxPayConfig();
        //获取参数
        $trade_no = $this->getOutTradeInfoById($param['order_id'])['order_no'];
        $body = $param['body'];
        $total_fee = $this->getOutTradeInfoById($param['order_id'])['total_amount'];
        $notify_url = $WxPayConfig::NOTIFY_URL;
        $openid = $param['openid'];
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($trade_no);
        $wxOrderData->SetBody($body);
        $wxOrderData->SetTotal_fee($total_fee * 100);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetNotify_url($notify_url);
        $wxOrderData->SetOpenid($openid);
        return $this->getPaySignature($param['order_id'],$wxOrderData,$param['bis_id']);
    }

    //该方法内部调用微信预订单接口
    private function getPaySignature($order_id,$wxOrderData,$bis_id){
        //获取appid,machid
        $paymentInfo = $this->getPaymentInfo($bis_id);
        //$wxOrder是微信返回的结果
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData,$paymentInfo);
        //判断代码
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS'){
            //存入日志(不管)
        }
        $this->recordPreOrder($order_id,$wxOrder);
        $signature = $this->sign($wxOrder,$bis_id);

        return $signature;
    }

    //处理签名
    private function sign($wxOrder,$bis_id){
        $jsApiPayData = new \WxPayJsApiPay();
        //获取appid,machid
        $paymentInfo = $this->getPaymentInfo($bis_id);
        $jsApiPayData->SetAppid($paymentInfo['appid']);
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time().mt_rand(0,1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');

        $sign = $jsApiPayData->MakeSign($paymentInfo['key']);
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['sign'] = $sign;

        unset($rawValues['appId']);
        return $rawValues;
    }

    //处理 prepay_id,把 prepay_id存入数据库
    private function recordPreOrder($order_id,$wxOrder){
        $data['prepay_id'] = $wxOrder['prepay_id'];
        $res = Db::table('store_main_orders')->where('id = '.$order_id)->update($data);
    }

    //根据外部订单id获取相关信息
    public function getOutTradeInfoById($order_id){
        $res = Db::table('store_main_orders')->field('order_no,total_amount')->where('id = '.$order_id)->find();
        return $res;
    }

    //支付回调
    public function receiveNotify(){
        $dataa['param'] = '接口起始';
        $dataa['create_time'] = date('Y-m-d H:i:s');
        Db::table('store_tempTable')->insert($dataa);
        $notify = new WxNotify999();
        $notify->Handle();
    }

    //获取支付相关信息(appid,mchid,key)
    public function getPaymentInfo($bis_id){
        $res = Db::table('store_bis')->field('appid,mchid,key')->where('id = '.$bis_id)->find();
        return $res;
    }

}



