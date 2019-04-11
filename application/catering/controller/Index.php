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

    //获取广告位
    public function getAdsInfo(){
        $bis_id = input('post.bis_id','0');
        if(empty($bis_id)){
            echo json_encode(array(
                'statuscode'   => 1,
                'result'      => array()
            ));
            exit;
        }
        $res = Db::table('cy_ads')
            ->where('bis_id = '.$bis_id.' and status = 1')
            ->order('listorder desc,id asc')
            ->select();

        echo json_encode(array(
            'statuscode'   => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取推荐商品
    public function getRecommendProInfo(){
        $bis_id = input('get.bis_id','');
        $res = model('Products')->getRecommendProInfo($bis_id);
        echo json_encode(array(
            'statuscode'   => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取最新商品
    public function getNewestProInfo(){
        $bis_id = input('get.bis_id','');
        $res = model('Products')->getNewestProInfo($bis_id);
        echo json_encode(array(
            'statuscode'   => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取店家环境图片
    public function getBisEnvInfo(){
        $bis_id = input('get.bis_id','');
        $res = Db::table('cy_bis_images')->where('bis_id = '.$bis_id)->find();
        $tempArr = array();
        !empty($res['env_image1']) ? array_push($tempArr,$res['env_image1']) : '';
        !empty($res['env_image2']) ? array_push($tempArr,$res['env_image2']) : '';
        !empty($res['env_image3']) ? array_push($tempArr,$res['env_image3']) : '';
        !empty($res['env_image4']) ? array_push($tempArr,$res['env_image4']) : '';
        !empty($res['env_image5']) ? array_push($tempArr,$res['env_image5']) : '';
        !empty($res['env_image6']) ? array_push($tempArr,$res['env_image6']) : '';

        $returnList = array(
            'bis_id' => $res['bis_id'],
            'env_info' => $tempArr
        );
        echo json_encode(array(
            'statuscode'   => 1,
            'result'      => $returnList
        ));
        exit;
    }

    //获取店铺的运费模式
    public function getTransportType(){
        $bis_id = input('post.bis_id');
        $res = Db::table('cy_bis')->field('logistics_status,transport_type,ykj_price')->where('id = '.$bis_id)->find();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }
}
