<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class MallOrder extends Controller{

    //获取订单信息(普通商城版)
    public function getOrderInfo(){
        //获取参数
        $param = input('post.');
        $res = model('MallOrder')->getOrderInfo($param);
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
        $res = model('MallOrder')->getOrderDetailInfo($param);
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
        $res = model('MallOrder')->makeJfOrder($param);
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

    //确认订单(普通商城版)
    public function confirmOrder(){
        $order_id = input('post.order_id');
        $data['order_status'] = 5;
        $res = Db::table('cy_mall_main_orders')->where('id = '.$order_id)->update($data);
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
        $res = Db::table('cy_mall_main_orders')->where('id = '.$order_id)->update($data);
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
        $res = model('MallOrder')->setMainRecInfo($order_id,$rec_id);
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

}



