<?php
namespace app\pay\controller;
use think\Controller;
use think\Db;
use think\Loader;
use think\Log;

Loader::import('CurWxPay.WxPayApi',EXTEND_PATH);

class CurWxPay extends Controller{

    public function __construct()
    {
        //获取参数
        $param = input('post.');
        if(empty($param)){
            return true;
        }
        $bis_id = $param['bis_id'];
        //获取db中配置内容
        $cfgRes = Db::table('store_bis')->field('appid,mchid,key,notify_url')->where('id = '.$bis_id)->find();
        //设置配置内容
        $WxPayConfig = new \OriWxPayConfig();
        $WxPayConfig::$appid = $cfgRes['appid'];
        $WxPayConfig::$mch_id = $cfgRes['mchid'];
        $WxPayConfig::$key = $cfgRes['key'];
        $WxPayConfig::$notify_url = $cfgRes['notify_url'];
    }

    public function pay(){
        $param = input('post.');
        //校验配置内容合法性
        $WxPayConfig = new \OriWxPayConfig();
        if(empty($WxPayConfig::$appid) || empty($WxPayConfig::$key)
            || empty($WxPayConfig::$mch_id) || empty($WxPayConfig::$notify_url)){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '支付参数缺失，请到店铺后台配置。'
            ));
            exit;
        }
        $res = $this->makeWxPreOrder($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成微信预订单
    public function makeWxPreOrder($param){
        $WxPayConfig = new \OriWxPayConfig();
        //获取参数
        $tradeRes = $this->getOutTradeInfoById($param['order_id']);
        $trade_no = $tradeRes['order_no'];
        $body = $param['body'];
        $total_fee = $tradeRes['total_amount'];
        $notify_url = $WxPayConfig::$notify_url;
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
        //$wxOrder是微信返回的结果
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        //判断代码
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS'){
            //存入日志(不管)
        }
        $this->recordPreOrder($order_id,$wxOrder);
        $signature = $this->sign($wxOrder);

        return $signature;
    }

    //处理签名
    private function sign($wxOrder){
        $jsApiPayData = new \WxPayJsApiPay();
        $WxPayConfig = new \OriWxPayConfig();
        $jsApiPayData->SetAppid($WxPayConfig::$appid);
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time().mt_rand(0,1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');

        $sign = $jsApiPayData->MakeSign();
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
        $notify = new WxNotify();
        log::error('into receiveNotify method');
        $notify->Handle();
        log::error('out receiveNotify method');
    }

}



