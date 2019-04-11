<?php
namespace app\pay\controller;
use think\Controller;
use think\Loader;
use think\Db;

Loader::import('CateringWxPay.WxPayApi',EXTEND_PATH);

class CateringNotifyUrl extends Controller{

    //原始支付回调
    public function receiveNotify(){
        //获取参数
        $bis_id = input('get.bis_id');
        $this->getAndSetKey($bis_id);
        $notify = new CheckSupplyProNotify();
        $notify->Handle();
    }

    //校验订单内是否有供货商品的支付回调
    public function checkSupplyProNotify(){
        $notify = new CateringMallWxNotify();
        $notify->Handle();
    }

    //餐饮点餐和外卖订单支付回调
    public function cateringOriWxNotify(){
        $notify = new CateringOriWxNotify();
        $notify->Handle();
    }

    //餐饮预定订单支付回调
    public function cateringReserveWxNotify(){
        $notify = new CateringReserveWxNotify();
        $notify->Handle();
    }

    //餐饮收银订单支付回调
    public function cateringCollectWxNotify(){
        $notify = new CateringCollectWxNotify();
        $notify->Handle();
    }

    //获取店铺微信支付的key，并赋值给配置文件中的$key
    public function getAndSetKey($bis_id){
        //获取db中配置内容
        $cfgRes = Db::table('cy_bis')->field('key')->where('id = '.$bis_id)->find();
        //设置配置内容
        $WxPayConfig = new \OriWxPayConfig();
        $WxPayConfig::$key = $cfgRes['key'];
    }
}



