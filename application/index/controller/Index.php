<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
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

    //获取推荐商品列表(单用户版)
    public function getRecommendProInfo(){
        //获取参数
        $bis_id = input('get.bis_id');
        $res = model('Products')->getRecommendProInfo($bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取推荐商品列表(多用户普通商城版)
    public function getRecProInfo(){
        //获取参数
        $page = input('post.page',1,'intval');
        $limit = 10;
        $offset = $limit * ($page - 1);
        $res = model('Products')->getRecProInfo($limit,$offset);
        $count = count($res);
        if($count == $limit){
            $has_more = true;
        }else{
            $has_more = false;
        }
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more
        ));
        exit;
    }

    //获取推荐商品列表(多用户版)
    public function getRecProInfoMut(){
        $res = model('Products')->getRecProInfoMut();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取推荐商品列表(拼团)
    public function getRecProByGroup(){
        //获取参数
        $bis_id = input('post.bis_id');
        $page = input('post.page',1,'intval');
        $limit = 10;
        $offset = $limit * ($page - 1);
        $res = model('Products')->getRecProByGroup($bis_id,$limit,$offset);
        $count = model('Products')->getRecProCountByGroup($bis_id,$limit,$offset);
        if($count == $limit){
            $has_more = true;
        }else{
            $has_more = false;
        }
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more
        ));
        exit;
    }

    //获取推荐商品列表(多用户拼团)
    public function getRecProByGroupMulti(){
        //获取参数
        $page = input('post.page',1,'intval');
        $limit = 6;
        $offset = $limit * ($page - 1);
        $res = model('Products')->getRecProByGroupMulti($limit,$offset);
        $count = count($res);

        if($count == $limit){
            $has_more = true;
        }else{
            $has_more = false;
        }
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more
        ));
        exit;
    }

    //获取新品列表(单用户版)
    public function getNewProInfo(){
        //获取参数
        $bis_id = input('get.bis_id');
        $res = model('Products')->getNewProInfo($bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取新品列表(多用户版)
    public function getNewProInfoMut(){
        //获取参数
        $bis_id = input('get.bis_id');
        $res = model('Products')->getNewProInfoMut($bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取商品详情(二维规格)
    public function getProDetail(){
        $pro_id = input('post.pro_id');
        $res = model('Products')->getProDetail($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取商品详情(一维规格)
    public function getProDetailOneDimensional(){
        $pro_id = input('post.pro_id');
        $res = model('Products')->getProDetailOneDimensional($pro_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取微信openid
    public function getOpenId(){
        //获取参数
        $appid = input('post.appid');
        $secret = input('post.secret');
        $code = input('post.code');
        $bis_id = input('post.bis_id');
        $avatarUrl = input('post.avatarUrl');
        $city = input('post.city');
        $country = input('post.country');
        $gender = input('post.gender');
        $nickName = input('post.nickName');
        $province = input('post.province');
        $sysType = input('post.sysType');
        $sysType = !empty($sysType) ? $sysType : 1;

        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$code."&grant_type=authorization_code";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $r = curl_exec($ch);
        curl_close($ch);
        $arr = json_decode($r,true);
        $openid = $arr['openid'];

        $this->addMembers($sysType,$bis_id,$openid,$appid,$secret,$avatarUrl,$city,$country,$gender,$nickName,$province);
    }

    //查询我的推荐收入和提现中金额
    public function getMyIncome(){
        $openid = input('post.openid');
        $where = "mem_id = '$openid'";
        $res = Db::table('store_members')->field('ketixian,tixianzhong')
            ->where($where)
            ->find();

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //添加会员信息
    public function addMembers($sysType,$bis_id,$mem_id,$appid,$secret,$avatarUrl,$city,$country,$gender,$nickName,$province){

        //查询会员表中是否存在此会员及二维码
        $where = "mem_id = '$mem_id' and status = 1";
        $mem_res = Db::table('store_members')->field('id,rec_id,code_url,create_time')->where($where)->find();

        if($mem_res){
            //设置更新数据
            $up_data = [
                'avatarUrl'  => $avatarUrl,
                'city'  => $city,
                'country'  => $country,
                'sex'  => $gender,
                'nickname'  => $nickName,
                'province'  => $province
            ];
            $up_where = "mem_id = '$mem_id'";
            Db::table('store_members')->where($up_where)->update($up_data);
            if($mem_res['code_url'] == ''){
                if($sysType == 1){
                    return $this->getwxacode($appid,$secret,$mem_res['id'],$mem_id);
                }else{
                    return $this->getwxacodeMulti($appid,$secret,$mem_res['id'],$mem_id);
                }

            }else{
                echo json_encode(array(
                    'openid'      => $mem_id,
                    'acode_url'   => $mem_res['code_url']
                ));
                exit;
            }
        }

        //设置数据
        $data = [
            'bis_id'  => $bis_id,
            'mem_id'  => $mem_id,
            'username'  => $mem_id,
            'truename'  => $mem_id,
            'avatarUrl'  => $avatarUrl,
            'city'  => $city,
            'country'  => $country,
            'sex'  => $gender,
            'nickname'  => $nickName,
            'province'  => $province,
            'rec_id'  => 1,
            'create_time'  => date('Y-m-d H:i:s')
        ];

        //添加数据
        $res = Db::table('store_members')->insertGetId($data);
        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加会员失败!'
            ));
            exit;
        }
        if($sysType == 1){
            $this->getwxacode($appid,$secret,$res,$mem_id);
        }else{
            $this->getwxacodeMulti($appid,$secret,$res,$mem_id);
        }

    }

    //获取小程序二维码(单用户版)
    public function getwxacode($appid,$secret,$u_id,$mem_id){
        //创建文件夹
        $upload_file_path = 'wxcode/';
        if(!is_dir($upload_file_path)) {
            mkdir($upload_file_path,0777,true);
        }

        //获取access_token
        $access_token_json = $this->getAccessToken($appid,$secret);
        $arr = json_decode($access_token_json,true);
        $access_token = $arr['access_token'];
        //设置路径及二维码大小
        $path="pages/index/index?id=".$u_id;
        $width=430;

        $post_data='{"path":"'.$path.'","width":'.$width.'}';
        $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;
        $result = $this->api_notice_increment($url,$post_data);
        //设置图片名称
        $img_name = substr(date('Y'),2,2).date('m').date('d').$u_id.'.png';
        //设置图片路径
        $img_path = $upload_file_path.$img_name;
        file_put_contents($img_path, $result);
        //将图片路径更新到会员表中
        $up_data['code_url'] = $img_path;
        Db::table('store_members')->where('id = '.$u_id)->update($up_data);
        echo json_encode(array(
            'openid'      => $mem_id,
            'acode_url'   => $img_path
        ));
        exit;
    }

    //获取小程序二维码(多用户版)
    public function getwxacodeMulti($appid,$secret,$u_id,$mem_id){
        //创建文件夹
        $upload_file_path = 'wxcode/';
        if(!is_dir($upload_file_path)) {
            mkdir($upload_file_path,0777,true);
        }

        //获取access_token
        $access_token_json = $this->getAccessToken($appid,$secret);
        $arr = json_decode($access_token_json,true);
        $access_token = $arr['access_token'];
        //设置路径及二维码大小
        $path="pages/home/home?id=".$u_id;
        $width=430;

        $post_data='{"path":"'.$path.'","width":'.$width.'}';
        $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;
        $result = $this->api_notice_increment($url,$post_data);
        //设置图片名称
        $img_name = substr(date('Y'),2,2).date('m').date('d').$u_id.'.png';
        //设置图片路径
        $img_path = $upload_file_path.$img_name;
        file_put_contents($img_path, $result);
        //将图片路径更新到会员表中
        $up_data['code_url'] = $img_path;
        Db::table('store_members')->where('id = '.$u_id)->update($up_data);
        echo json_encode(array(
            'openid'      => $mem_id,
            'acode_url'   => $img_path
        ));
        exit;
    }

    //手动获取小程序二维码(单用户版--2018/2/1)
    public function getIndWxacode(){
        //获取参数
        $appid = input('post.appid');
        $secret = input('post.secret');
        $openid = input('post.openid');

        $up_where = "mem_id = '$openid'";
        $mem_res = Db::table('store_members')->where($up_where)->find();
        if($mem_res['code_url'] != ''){
            echo json_encode(array(
                'statuscode'  => 1,
                'result'      => $mem_res['code_url']
            ));
            exit;

        }else{
            $u_id = $mem_res['id'];

            //创建文件夹
            $upload_file_path = 'wxcode/';
            if(!is_dir($upload_file_path)) {
                mkdir($upload_file_path,0777,true);
            }

            //获取access_token
            $access_token_json = $this->getAccessToken($appid,$secret);
            $arr = json_decode($access_token_json,true);
            $access_token = $arr['access_token'];
            //设置路径及二维码大小
            $path="pages/index/index?id=".$u_id;
            $width=430;

            $post_data='{"path":"'.$path.'","width":'.$width.'}';
            $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;
            $result = $this->api_notice_increment($url,$post_data);
            //设置图片名称
            $img_name = substr(date('Y'),2,2).date('m').date('d').$u_id.'.png';
            //设置图片路径
            $img_path = $upload_file_path.$img_name;
            file_put_contents($img_path, $result);
            //将图片路径更新到会员表中
            $up_data['code_url'] = $img_path;
            Db::table('store_members')->where('id = '.$u_id)->update($up_data);
            echo json_encode(array(
                'statuscode'  => 1,
                'result'      => $img_path
            ));
            exit;
        }
    }

    //手动获取小程序二维码(多用户版--2018/01/30)
    public function getMultiWxacode(){
        //获取参数
        $appid = input('post.appid');
        $secret = input('post.secret');
        $openid = input('post.openid');

        $up_where = "mem_id = '$openid'";
        $mem_res = Db::table('store_members')->where($up_where)->find();
        if($mem_res['code_url'] != ''){
            echo json_encode(array(
                'statuscode'  => 1,
                'result'      => $mem_res['code_url']
            ));
            exit;

        }else{
            $u_id = $mem_res['id'];

            //创建文件夹
            $upload_file_path = 'wxcode/';
            if(!is_dir($upload_file_path)) {
                mkdir($upload_file_path,0777,true);
            }

            //获取access_token
            $access_token_json = $this->getAccessToken($appid,$secret);
            $arr = json_decode($access_token_json,true);
            $access_token = $arr['access_token'];
            //设置路径及二维码大小
            $path="pages/home/home?id=".$u_id;
            $width=430;

            $post_data='{"path":"'.$path.'","width":'.$width.'}';
            $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;
            $result = $this->api_notice_increment($url,$post_data);
            //设置图片名称
            $img_name = substr(date('Y'),2,2).date('m').date('d').$u_id.'.png';
            //设置图片路径
            $img_path = $upload_file_path.$img_name;
            file_put_contents($img_path, $result);
            //将图片路径更新到会员表中
            $up_data['code_url'] = $img_path;
            Db::table('store_members')->where('id = '.$u_id)->update($up_data);
            echo json_encode(array(
                'statuscode'  => 1,
                'result'      => $img_path
            ));
            exit;
        }

    }

    //获取access_token
    public function getAccessToken($appid,$secret){

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
        die;
    }

    function api_notice_increment($url, $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }else{
            return $tmpInfo;
        }
    }

    //获取微信openid
    public function getOpenIdOnly(){
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
        return $r;
    }

    //获取店铺的运费模式
    public function getTransportType(){
        $bis_id = input('post.bis_id');
        $res = Db::table('store_bis')->field('transport_type,ykj_price')->where('id = '.$bis_id)->find();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //支付失败发送模板消息
    public function setTemMessage(){
        //获取参数
        $touser = input('post.touser');
        $template_id = input('post.template_id');
        $form_id = input('post.form_id');
        $appid = input('post.appid');
        $secret = input('post.secret');
        $order_id = input('post.order_id');
        $page = 'pages/index/index';
        //获取access_token
        $access_token_json = $this->getAccessToken($appid,$secret);
        $arr = json_decode($access_token_json,true);
        $access_token = $arr['access_token'];
        //获取订单信息
        $order_info = $this->getOrderInfo($order_id);
        $order_no = $order_info['order_no'];
        $pro_detail = $order_info['pro_detail'];
        $order_time = $order_info['order_time'];
        $amount = '￥'.$order_info['amount'];
        $notice = '优惠有限,请您尽快完成支付';

        //设置请求url
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token;
        //设置参数
        $data = '{"keyword1": {"value": "'.$order_no.'","color": "#0b3768"},
                  "keyword2": {"value": "'.$pro_detail.'","color": "#0b3768"},
                  "keyword3": {"value": "'.$order_time.'","color": "#0b3768"},
                  "keyword4": {"value": "'.$amount.'","color": "#ff0000"},
                  "keyword5": {"value": "'.$notice.'","color": "#0b3768"}}';

        $post_data='{"touser":"'.$touser.'","template_id":"'.$template_id.'","page":"'.$page.'","form_id":"'.$form_id.'","data":'.$data.'}';
        $result = $this->sendPost($url,$post_data);
        return $result;
    }

    //支付失败发送模板消息(拼团版)
    public function setTemMessageByGroup(){
        //获取参数
        $touser = input('post.touser');
        $template_id = input('post.template_id');
        $form_id = input('post.form_id');
        $appid = input('post.appid');
        $secret = input('post.secret');
        $order_id = input('post.order_id');
        $page = 'pages/index/index';
        //获取access_token
        $access_token_json = $this->getAccessToken($appid,$secret);
        $arr = json_decode($access_token_json,true);
        $access_token = $arr['access_token'];
        //获取订单信息
        $order_info = $this->getOrderInfoByGroup($order_id);
        $order_no = $order_info['order_no'];
        $pro_detail = $order_info['pro_detail'];
        $order_time = $order_info['order_time'];
        $amount = '￥'.$order_info['amount'];
        $notice = '优惠有限,请您尽快完成支付';

        //设置请求url
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token;
        //设置参数
        $data = '{"keyword1": {"value": "'.$order_no.'","color": "#0b3768"},
                  "keyword2": {"value": "'.$pro_detail.'","color": "#0b3768"},
                  "keyword3": {"value": "'.$order_time.'","color": "#0b3768"},
                  "keyword4": {"value": "'.$amount.'","color": "#ff0000"},
                  "keyword5": {"value": "'.$notice.'","color": "#0b3768"}}';

        $post_data='{"touser":"'.$touser.'","template_id":"'.$template_id.'","page":"'.$page.'","form_id":"'.$form_id.'","data":'.$data.'}';
        $result = $this->sendPost($url,$post_data);
        return $result;
    }

    function sendPost($url, $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;

    }

    //获取订单信息
    public function getOrderInfo($order_id){
        $main_res = Db::table('store_main_orders')->alias('main')->field('main.order_no,main.total_amount,main.create_time')
            ->where('main.id = '.$order_id)
            ->find();

        $sub_res = Db::table('store_sub_orders')->alias('sub')->field('pro.p_name')
            ->join('store_pro_config con','sub.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where('sub.main_id = '.$order_id.' and sub.status = 1')
            ->find();

        $pro_count =  Db::table('store_sub_orders')
                ->where('main_id = '.$order_id.' and status = 1')
                ->count();

        if($pro_count > 1){
            $pro_detail = $sub_res['p_name'].'等'.$pro_count.'件';
        }else{
            $pro_detail = $sub_res['p_name'];
        }
        $order_info = [
            'order_no'  => $main_res['order_no'],
            'amount'  => $main_res['total_amount'],
            'order_time'  => $main_res['create_time'],
            'pro_detail'  => $pro_detail
        ];

        return $order_info;
    }

    //获取订单信息
    public function getOrderInfoByGroup($order_id){
        $main_res = Db::table('store_group_main_orders')->alias('main')->field('main.order_no,main.total_amount,main.create_time')
            ->where('main.id = '.$order_id)
            ->find();

        $sub_res = Db::table('store_group_sub_orders')->alias('sub')->field('pro.p_name')
            ->join('store_pro_config con','sub.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where('sub.main_id = '.$order_id.' and sub.status = 1')
            ->find();

        $pro_count =  Db::table('store_group_sub_orders')
            ->where('main_id = '.$order_id.' and status = 1')
            ->count();

        if($pro_count > 1){
            $pro_detail = $sub_res['p_name'].'等'.$pro_count.'件';
        }else{
            $pro_detail = $sub_res['p_name'];
        }
        $order_info = [
            'order_no'  => $main_res['order_no'],
            'amount'  => $main_res['total_amount'],
            'order_time'  => $main_res['create_time'],
            'pro_detail'  => $pro_detail
        ];

        return $order_info;
    }

    //***************以下是新的<生成openid>及<添加会员>接口*****************

    //获取微信openid
    public function getOpenIdNew(){
        //获取参数
        $appid = input('post.appid');
        $secret = input('post.secret');
        $code = input('post.code');
        $bis_id = input('post.bis_id');
        $avatarUrl = input('post.avatarUrl');
        $city = input('post.city');
        $country = input('post.country');
        $gender = input('post.gender');
        $nickName = input('post.nickName');
        $province = input('post.province');
        $sysType = input('post.sysType');

        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$code."&grant_type=authorization_code";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $r = curl_exec($ch);
        curl_close($ch);
        $arr = json_decode($r,true);
        $openid = $arr['openid'];

        //生成会员
        $this->addMembersNew($bis_id,$openid,$avatarUrl,$city,$country,$gender,$nickName,$province);
    }

    //添加会员信息
    public function addMembersNew($bis_id,$openid,$avatarUrl,$city,$country,$gender,$nickName,$province){
        //查询会员表中是否存在此会员
        $where = "mem_id = '$openid' and status = 1";
        $mem_res = Db::table('store_members')->field('id,rec_id,create_time')->where($where)->find();
        
        if($mem_res){
            //设置更新数据
            $up_data = [
                'avatarUrl'  => $avatarUrl,
                'city'  => $city,
                'country'  => $country,
                'sex'  => $gender,
                'nickname'  => $nickName,
                'province'  => $province
            ];

            $up_where = "mem_id = '$openid'";
            Db::table('store_members')->where($up_where)->update($up_data);
            echo json_encode(array(
                'openid'      => $openid,
                'bis_id'   => $bis_id,
                'status'   => $mem_res
            ));
            exit;
        }else{
            //设置数据
            $data = [
                'bis_id'  => $bis_id,
                'mem_id'  => $openid,
                'username'  => $openid,
                'truename'  => $openid,
                'avatarUrl'  => $avatarUrl,
                'city'  => $city,
                'country'  => $country,
                'sex'  => $gender,
                'nickname'  => $nickName,
                'province'  => $province,
                'rec_id'  => 1,
                'create_time'  => date('Y-m-d H:i:s')
            ];

            //添加数据
            Db::table('store_members')->insertGetId($data);

            //返回openid
            echo json_encode(array(
                'openid'      => $openid,
                'bis_id'   => $data
            ));
            exit;
        }
    }
}
