<?php
namespace app\business\controller;
use think\Controller;
use think\Db;

vendor('alipay.aop.AopClient');
vendor('alipay.aop.AlipayConfig');
vendor('alipay.aop.request.AlipayTradeAppPayRequest');

class Alipay extends Controller{

    public function pay(){
        //获取参数
        $order_no = input('get.order_no','');
        //获取订单信息
        $orderInfo = Db::table('store_main_orders')->where("order_no = '$order_no' and order_status = 2 and status = 1")->find();

        if($orderInfo){
            $aop = new \AopClient();
            $config = new \AlipayConfig();
            $aop->gatewayUrl = $config::GATEWAY_URL; //支付宝网关
            $aop->appId = $config::APPID;
            $aop->rsaPrivateKey = $config::RSA_PRIVATE_KEY;
            $aop->alipayrsaPublicKey = $config::ALIPAY_RSA_PUBLIC_KEY;
            $aop->apiVersion = '1.0';
            $aop->signType = $config::SIGN_TYPE;
            $aop->postCharset = 'UTF-8';
            $aop->format = "json";
            //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
            $appRequest = new \AlipayTradeAppPayRequest();
            //SDK已经封装掉了公共参数，这里只需要传入业务参数
            $bizcontent = json_encode([
                'body' => '商城购买',//订单描述
                'subject' => '普通购买',//订单标题
                'timeout_express' => '30m',
                'out_trade_no' => $order_no,//商户网站唯一订单号
                'total_amount' => $orderInfo['total_amount'],//订单总金额
                'product_code' => 'QUICK_MSECURITY_PAY', //固定值
            ]);

            $notifyUrl = $config::NOTIFY_URL;
            $appRequest->setNotifyUrl($notifyUrl);//设置异步通知地址
            $appRequest->setBizContent($bizcontent);
            //这里和普通的接口调用不同，使用的是sdkExecute
            $response = $aop->sdkExecute($appRequest);

            //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
            echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理
        }else{
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '订单不存在,支付失败'
            ));
            exit;
        }

    }


}



