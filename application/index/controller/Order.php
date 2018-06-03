<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Order extends Controller{

    //获取订单信息(普通商城版)
    public function getOrderInfo(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->getOrderInfo($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取订单信息(多用户版)
    public function getOrderInfoMulti(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->getOrderInfoMulti($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取订单详情信息(普通商城版)
    public function getOrderDetailInfo(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->getOrderDetailInfo($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取订单详情信息(拼团版)
    public function getGroupOrderDetailInfo(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->getGroupOrderDetailInfo($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取订单信息(拼团单独购买版)
    public function getOrderInfoBySingle(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->getOrderInfoBySingle($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取订单信息(多用户拼团版)
    public function getOrderInfoBySingleMulti(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->getOrderInfoBySingleMulti($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成订单(普通商城版)
    public function makeOrder(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->makeOrder($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //积分商品生成订单(多用户版)
    public function makeJfOrder(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->makeJfOrder($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成订单(拼团单独购买版)
    public function makeOrderBySingle(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->makeOrderBySingle($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //生成订单(商城多用户版)
    public function makeOrderMulti(){
        //获取参数
        $param = input('post.');
        $res = model('Order')->makeOrderMulti($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //更改订单状态为已付款(普通商城版)
    public function updateOrderStatus(){
        $order_id = input('post.order_id');
        $data['order_status'] = 3;
        $data['update_time'] = date('Y-m-d H:i:s');
        $data['pay_time'] = date('Y-m-d H:i:s');
        Db::table('store_main_orders')->where('id = '.$order_id)->update($data);
    }

    //更改订单状态为已付款(拼团单独购买版)
    public function updateOrderStatusBySingle(){
        $order_id = input('post.order_id');
        $from = input('post.from');
        if($from == 'single'){
            $data['order_status'] = 3;
        }else{
            $data['order_status'] = 2;
        }

        $data['update_time'] = date('Y-m-d H:i:s');
        $data['pay_time'] = date('Y-m-d H:i:s');
        Db::table('store_group_main_orders')->where('id = '.$order_id)->update($data);
    }

    //更改积分订单状态
    public function updateJfOrderStatus(){
        $order_id = input('post.order_id');
        $data['order_status'] = 3;

        $data['update_time'] = date('Y-m-d H:i:s');
        $data['pay_time'] = date('Y-m-d H:i:s');
        Db::table('store_main_orders')->where('id = '.$order_id)->update($data);
    }

    //根据id查询订单号和总金额(普通商城版)
    public function getOrderInfoByOrderId(){
        $order_id = input('post.order_id');
        $res = Db::table('store_main_orders')->field('order_no,total_amount')->where('id = '.$order_id)->find();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据id查询订单号和总金额(拼团单独购买版)
    public function getOrderInfoByOrderIdBySingle(){
        $order_id = input('post.order_id');
        $res = Db::table('store_group_main_orders')->field('order_no,total_amount')->where('id = '.$order_id)->find();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //确认订单(普通商城版)
    public function confirmOrder(){
        $order_id = input('post.order_id');
        $data['order_status'] = 5;
        $res = Db::table('store_main_orders')->where('id = '.$order_id)->update($data);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //确认订单(拼团版)
    public function confirmGroupOrder(){
        $order_id = input('post.order_id');
        $data['order_status'] = 5;
        $res = Db::table('store_group_main_orders')->where('id = '.$order_id)->update($data);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //取消订单(普通商城版)
    public function cancelOrder(){
        $order_id = input('post.order_id');
        $data['status'] = -1;
        $res = Db::table('store_main_orders')->where('id = '.$order_id)->update($data);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //取消订单(拼团单独购买版)
    public function cancelOrderBySingle(){
        $order_id = input('post.order_id');
        $data['status'] = -1;
        $res = Db::table('store_group_main_orders')->where('id = '.$order_id)->update($data);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //设置主订单表推荐人及佣金信息(普通商城版)
    public function setMainRecInfo(){
        $order_id = input('post.order_id');
        $rec_id = input('post.rec_id');
        $res = model('Order')->setMainRecInfo($order_id,$rec_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //设置主订单表推荐人及佣金信息(拼团单独购买版)
    public function setMainRecInfoBySingle(){
        $order_id = input('post.order_id');
        $rec_id = input('post.rec_id');
        $res = model('Order')->setMainRecInfoBySingle($order_id,$rec_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //查询佣金订单
    public function getRecOrders(){
        $param = input('post.');
        $res = model('Order')->getRecOrders($param);
        $count = model('Order')->getRecOrderCount($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'count'       => $count,
            'result'      => $res
        ));
        exit;
    }

    //生成提现订单
    public function makeTixianOrders(){
        $param = input('post.');
        $res = model('Order')->makeTixianOrders($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'message'      => '生成提现订单成功'
        ));
        exit;
    }

    //查看提现记录
    public function getTixianRecords(){
        $openid = input('post.openid');
        $res = model('Order')->getTixianRecords($openid);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //查询物流
    public function getLogisticInfo(){
        //获取参数
        $shipperCode = input('post.shipperCode');
        $logisticCode = input('post.logisticCode');

        $EBusinessID = "1313383";
        $AppKey = "06ab8d28-ff46-4225-9fcf-86eb3693c6ec";
        $ReqURL = "http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx";

        $requestData= "{'OrderCode':'','ShipperCode':'".$shipperCode."','LogisticCode':'".$logisticCode."'}";

        $datas = array(
            'EBusinessID' => $EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $AppKey);
        $result = $this->sendPost($ReqURL, $datas);

        //根据公司业务处理返回的信息......
        return $result;
    }

    function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    //生成签名
    function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }

    //参团模式更新订单信息
    public function updateOrderByJoinGroup(){
        //获取参数
        $group_num = input('post.group_num');
        //获取数据
        $where = "group_num = '$group_num' and group_identity = 1";
        $res = Db::table('store_group_main_orders')->field('pintuan_count')->where($where)->find();
        $pintuan_count = $res['pintuan_count'];
        $where1 = "group_num = '$group_num' and (order_status = 2 or order_status = 3)";
        $count = Db::table('store_group_main_orders')->where($where1)->count();
        if($pintuan_count <= $count){
            $data = [
                'group_status'  => 2,
                'order_status'  => 3,
            ];
            //更新订单表
            Db::table('store_group_main_orders')->where($where1)->update($data);
            echo json_encode(array(
                'statuscode'  => 1
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 0
            ));
            exit;
        }
    }

    //检验当前是否已成团(通过团号)
    public function checkGroupStatusByGroupNum(){
        //获取参数
        $group_num = input('post.group_num');
        //获取数据
        $where = "group_num = '$group_num' and group_identity = 1";
        $res = Db::table('store_group_main_orders')->field('group_status')->where($where)->find();
        if($res['group_status'] != 2){
            echo json_encode(array(
                'statuscode'  => 0
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 1
            ));
            exit;
        }
    }

    //拼团-分享时获取订单信息
    public function getOrderInfoByShare(){
        //获取参数
        $order_id = input('post.order_id');
        $res = Db::table('store_group_main_orders')->alias('gmo')->field('gmo.id as order_id,gmo.group_num,gmo.pintuan_count,pro.p_name,pro.associator_price,pro.pintuan_price,img.thumb')
            ->join('store_products pro','gmo.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','pro.id = img.p_id','LEFT')
            ->where('gmo.id = '.$order_id)
            ->find();

        $group_num = $res['group_num'];
        //获取购买人信息
        $where = "gmo.group_num = '$group_num' and (gmo.order_status = 2 or gmo.order_status = 3)";
        $mem_res = Db::table('store_group_main_orders')->alias('gmo')->field('gmo.group_identity,mem.avatarUrl')
            ->join('store_members mem','gmo.mem_id = mem.mem_id','LEFT')
            ->where($where)
            ->order('gmo.create_time asc')
            ->select();

        //获取团长mem_id
        $where1 = "group_num = '$group_num' and group_identity = 1 and (order_status = 2 or order_status = 3)";
        $tuanzhang_res = Db::table('store_group_main_orders')->field('mem_id')
            ->where($where1)
            ->find();
        $mem_id = $tuanzhang_res['mem_id'];

        $pintuan_count = $res['pintuan_count'];
        $count = count($mem_res);
        if($count < $pintuan_count){
            $lack_count = $pintuan_count - $count;
            $temp_array = [
                'group_identity'  => 2,
                'avatarUrl'  => '/pics/icons/wenhao.png'
            ];
            for($i = 0;$i < $lack_count;$i++){
                array_push($mem_res,$temp_array);
            }
            $is_enough = false;
        }else{
            $is_enough = true;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'order_res'   => $res,
            'mem_res'     => $mem_res,
            'is_enough'   => $is_enough,
            'mem_id'      => $mem_id
        ));
        exit;
    }

    //拼团-分享时获取商品id
    public function getProIdByShare(){
        //获取参数
        $order_id = input('post.order_id');
        $res = Db::table('store_group_main_orders')->field('pro_id,group_num')
            ->where('id = '.$order_id)
            ->find();
        $pro_id = $res['pro_id'];
        $group_num = $res['group_num'];

        echo json_encode(array(
            'statuscode'  => 1,
            'pro_id'   => $pro_id,
            'group_num'   => $group_num
        ));
        exit;
    }

    //检验当前是否已成团
    public function checkGroupStatusByOrderId(){
        //获取参数
        $order_id = input('post.order_id');
        //获取团号
        $group_res = Db::table('store_group_main_orders')->field('group_num')->where('id = '.$order_id)->find();
        $group_num = $group_res['group_num'];
        //获取数据
        $where = "group_num = '$group_num' and group_identity = 1";
        $res = Db::table('store_group_main_orders')->field('group_status')->where($where)->find();
        if($res['group_status'] != 2){
            echo json_encode(array(
                'statuscode'  => 0
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 1
            ));
            exit;
        }
    }

    //支付成功后

}



