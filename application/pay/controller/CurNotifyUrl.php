<?php
namespace app\pay\controller;
use think\Controller;
use think\Loader;
use think\Db;

Loader::import('CurWxPay.WxPayApi',EXTEND_PATH);

class CurNotifyUrl extends Controller{

    //原始支付回调
    public function receiveNotify(){
        //获取参数
        $bis_id = input('get.bis_id',0);
        $this->getAndSetKey($bis_id);
        $notify = new OriWxNotify();
        $notify->Handle();
    }

    //校验订单内是否有供货商品的支付回调
    public function checkSupplyProNotify(){
        $notify = new CheckSupplyProNotify();
        $notify->Handle();
    }

    //获取店铺微信支付的key，并赋值给配置文件中的$key
    public function getAndSetKey($bis_id){
        //获取db中配置内容
        $cfgRes = Db::table('store_bis')->field('key')->where('id = '.$bis_id)->find();
        //设置配置内容
        $WxPayConfig = new \OriWxPayConfig();
        $WxPayConfig::$key = $cfgRes['key'];
    }
}



