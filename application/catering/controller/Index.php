<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;
use think\Loader;
class Index extends Controller{

    //获取首页bannger
    public function getBannersInfo(){
        //获取参数
        $bis_id = input('get.bis_id');
        $res = model('Recommend')->getBanners($bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取店铺信息
    public function getBisInfo(){
        //获取参数
        $bis_id = input('post.bis_id');
        //获取店铺基本信息
        $bis_res = model('Bis')->getBisInfo($bis_id);
        //获取图片信息
        $img_res = model('Bis')->getBisImgInfo($bis_id);
        //获取活动信息
        $act_res = model('Bis')->getBisActInfo($bis_id);
        //获取banner信息
        $banner_res = model('Bis')->getBannerInfo($bis_id);

        echo json_encode(array(
            'statuscode'   => 1,
            'bis_res'      => $bis_res,
            'img_res'      => $img_res,
            'act_res'      => $act_res,
            'banner_res'   => $banner_res
        ));
        exit;
    }

    //获取微信openid
    public function getOpenId(){
        //获取参数
        $appid = input('post.appid');
        $secret = input('post.secret');
        $code = input('post.code');

        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$code."&grant_type=authorization_code";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $r = curl_exec($ch);
        curl_close($ch);
        echo $r;
        die;
    }
}
