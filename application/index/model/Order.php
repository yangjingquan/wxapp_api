<?php
namespace app\index\model;
use app\api\service\CheckService;
use think\Model;
use think\Db;

class Order extends Model{

    const ORI_ORDER_TYPE = 1;//普通订单类型
    const JF_ORDER_TYPE = 2;//积分订单类型

    //获取订单信息(普通商城版)
    public function getOrderInfo($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $type = !empty($param['type']) ? $param['type'] : 1;
        $where = "main.bis_id = ".$bis_id." and main.mem_id = '$wx_id' and main.status = 1 ";

        if($type == 2){
            $con = "and main.order_status = 1";
        }elseif($type == 3){
            $con = "and main.order_status = 2";
        }elseif($type == 4){
            $con = "and main.order_status = 3";
        }elseif($type == 5){
            $con = "and main.order_status = 4";
        }elseif($type == 6){
            $con = "and main.order_status = 5";
        }else{
            $con = "";
        }
        $where .= $con;

        $res = Db::table('store_main_orders')->alias('main')->field('main.id as order_id,main.order_no,main.total_amount,main.order_status,main.express_no,mode.post_code,mode.post_mode,main.jifen,main.with_balance_amount')
            ->join('store_post_mode mode','main.mode = mode.id','LEFT')
            ->where($where)
            ->order('main.create_time desc')
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        $result = array();
        foreach($res as $val){
            $result[$index]['order_id'] = $val['order_id'];
            $result[$index]['order_no'] = $val['order_no'];
            $result[$index]['amount'] = $val['total_amount'];
            $result[$index]['with_balance_amount'] = $val['with_balance_amount'];
            $result[$index]['status'] = $val['order_status'];

            switch($val['order_status']){
                case 2:
                    $status_text =  '待付款';
                    break;
                case 3:
                    $status_text =  '待发货';
                    break;
                case 4:
                    $status_text =  '待收货';
                    break;
                default:
                    $status_text =  '已完成';
                    break;
            }
            $result[$index]['status_text'] = $status_text;
            $result[$index]['pro_info'] = $this->getSubOrderInfo($val['order_id']);
            $result[$index]['post_code'] = $val['post_code'];
            $result[$index]['express_no'] = $val['express_no'];
            $result[$index]['post_mode'] = $val['post_mode'];
            if($val['jifen'] != 0){
                if($val['total_amount'] < '0.01'){
                    $result[$index]['amount'] = $val['jifen'].'积分';
                    $result[$index]['order_type'] = 1;
                }else{
                    $result[$index]['amount'] = $val['jifen'].'积分'.' + ￥'.$val['total_amount'];
                    $result[$index]['order_type'] = 2;
                }

            }else{
                //普通订单
                $result[$index]['order_type'] = 3;
                $result[$index]['amount'] = '￥'.$val['total_amount'];
            }
            $index ++;
        }

        return $result;
    }

    //获取订单信息(多用户版)
    public function getOrderInfoMulti($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $type = !empty($param['type']) ? $param['type'] : 1;
        $where = "main.mem_id = '$wx_id' and main.status = 1 and main.order_from = 2 ";

        if($type == 2){
            $con = "and main.order_status = 1";
        }elseif($type == 3){
            $con = "and main.order_status = 2";
        }elseif($type == 4){
            $con = "and main.order_status = 3";
        }elseif($type == 5){
            $con = "and main.order_status = 4";
        }elseif($type == 6){
            $con = "and main.order_status = 5";
        }else{
            $con = "";
        }

        $where .= $con;

        $res = Db::table('store_main_orders')->alias('main')->field('main.id as order_id,main.bis_id,bis.bis_name,bis.thumb,main.order_no,main.total_amount,main.order_status,main.express_no,mode.post_code,mode.post_mode,main.jifen,bis.is_pay')
            ->join('store_post_mode mode','main.mode = mode.id','LEFT')
            ->join('store_bis bis','main.bis_id = bis.id','LEFT')
            ->where($where)
            ->order('main.create_time desc,main.id desc')
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        $result = array();
        foreach($res as $val){
            $result[$index]['order_id'] = $val['order_id'];
            $result[$index]['bis_id'] = $val['bis_id'];
            $result[$index]['bis_name'] = $val['bis_name'];
            $result[$index]['thumb'] = $val['thumb'];
            $result[$index]['order_no'] = $val['order_no'];
            $result[$index]['amount'] = $val['total_amount'];
            $result[$index]['status'] = $val['order_status'];

            switch($val['order_status']){
                case 2:
                    $status_text =  '待付款';
                    break;
                case 3:
                    $status_text =  '待发货';
                    break;
                case 4:
                    $status_text =  '待收货';
                    break;
                default:
                    $status_text =  '已完成';
                    break;
            }
            $result[$index]['status_text'] = $status_text;
            $result[$index]['pro_info'] = $this->getSubOrderInfo($val['order_id']);
            $result[$index]['post_code'] = $val['post_code'];
            $result[$index]['express_no'] = $val['express_no'];
            $result[$index]['post_mode'] = $val['post_mode'];
            $result[$index]['is_pay'] = $val['is_pay'];
            if($val['jifen'] != 0){
                if($val['total_amount'] < '0.01'){
                    $result[$index]['amount'] = $val['jifen'].'积分';
                    $result[$index]['order_type'] = 1;
                }else{
                    $result[$index]['amount'] = $val['jifen'].'积分'.' + ￥'.$val['total_amount'];
                    $result[$index]['order_type'] = 2;
                }

            }else{
                //普通订单
                $result[$index]['order_type'] = 3;
                $result[$index]['amount'] = '￥'.$val['total_amount'];
            }
            $index ++;
        }

        return $result;
    }

    //获取订单详情信息(普通商城版)
    public function getOrderDetailInfo($param){
        //获取参数
        $order_id = !empty($param['order_id']) ? $param['order_id'] : '';
        $where = "main.order_no = '$order_id'";

        $res = Db::table('store_main_orders')->alias('main')->field('main.id as order_id,main.order_no,main.total_amount,main.order_status,main.mobile,main.address,main.rec_name,main.create_time,main.pay_time,main.payment,main.pro_total_amount,main.transport_fee,main.express_no,mode.post_code,mode.post_mode,main.jifen')
            ->join('store_post_mode mode','main.mode = mode.id','LEFT')
            ->where($where)
            ->find();

        switch($res['order_status']){
            case 2:
                $status_text =  '待付款';
                break;
            case 3:
                $status_text =  '待发货';
                break;
            case 4:
                $status_text =  '待收货';
                break;
            default:
                $status_text =  '已完成';
                break;
        }
        switch($res['payment']){
            case 1:
                $payment_text =  '微信支付';
                break;
        }

        if($res['jifen'] != 0){
            if($res['total_amount'] < '0.01'){
                $res['total_amount'] = $res['jifen'].'积分';
                $res['order_type'] = 1;
            }else{
                $res['total_amount'] = $res['jifen'].'积分'.' + ￥'.$res['total_amount'];
                $res['order_type'] = 2;
            }
        }else{
            //普通订单
            $res['order_type'] = 3;
            $res['total_amount'] = '￥'.$res['total_amount'];
        }
        $res['payment_text'] = $payment_text;
        $res['status_text'] = $status_text;
        $res['pro_info'] = $this->getSubOrderInfo($res['order_id']);

        return $res;
    }

    //获取订单详情信息(拼团版)
    public function getGroupOrderDetailInfo($param){
        //获取参数
        $order_id = !empty($param['order_id']) ? $param['order_id'] : '';
        $where = "main.order_no = '$order_id'";

        $res = Db::table('store_group_main_orders')->alias('main')->field('main.id as order_id,main.order_no,main.total_amount,main.order_status,main.mobile,main.address,main.rec_name,main.create_time,main.pay_time,main.payment,main.pro_total_amount,main.transport_fee,main.express_no,mode.post_code,mode.post_mode')
            ->join('store_post_mode mode','main.mode = mode.id','LEFT')
            ->where($where)
            ->find();

        switch($res['order_status']){
            case 1:
            case 1:
                $status_text =  '待付款';
                break;
            case 2:
                $status_text =  '待成团';
                break;
            case 3:
                $status_text =  '待发货';
                break;
            case 4:
                $status_text =  '待收货';
                break;
            default:
                $status_text =  '已完成';
                break;
        }
        switch($res['payment']){
            case 1:
                $payment_text =  '微信支付';
                break;
        }
        $res['payment_text'] = $payment_text;
        $res['status_text'] = $status_text;
        $res['pro_info'] = $this->getGroupSubOrderInfo($res['order_id']);

        return $res;
    }

    //获取订单信息(拼团单独购买版)
    public function getOrderInfoBySingle($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $type = !empty($param['type']) ? $param['type'] : 1;
        $where = "main.bis_id = ".$bis_id." and main.mem_id = '$wx_id' and main.status = 1 ";

        if($type == 2){
            $con = "and main.order_status = 1";
        }elseif($type == 3){
            $con = "and main.order_status = 2";
        }elseif($type == 4){
            $con = "and main.order_status = 3";
        }elseif($type == 5){
            $con = "and main.order_status = 4";
        }elseif($type == 6){
            $con = "and main.order_status = 5";
        }else{
            $con = "";
        }
        $where .= $con;

        $res = Db::table('store_group_main_orders')->alias('main')->field('main.id as order_id,main.order_no,main.total_amount,main.order_status,main.express_no,mode.post_code,mode.post_mode')
            ->join('store_post_mode mode','main.mode = mode.id','LEFT')
            ->where($where)
            ->order('main.create_time desc')
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        $result = array();
        foreach($res as $val){
            $result[$index]['order_id'] = $val['order_id'];
            $result[$index]['order_no'] = $val['order_no'];
            $result[$index]['amount'] = $val['total_amount'];
            $result[$index]['status'] = $val['order_status'];

            switch($val['order_status']){
                case 1:
                    $status_text =  '待付款';
                    break;
                case 2:
                    $status_text =  '待成团';
                    break;
                case 3:
                    $status_text =  '待发货';
                    break;
                case 4:
                    $status_text =  '待收货';
                    break;
                default:
                    $status_text =  '已完成';
                    break;
            }
            $result[$index]['status_text'] = $status_text;
            $result[$index]['pro_info'] = $this->getSubOrderInfoBySingle($val['order_id']);
            $result[$index]['post_code'] = $val['post_code'];
            $result[$index]['express_no'] = $val['express_no'];
            $result[$index]['post_mode'] = $val['post_mode'];
            $index ++;
        }

        return $result;
    }

    //获取订单信息(多用户拼团版)
    public function getOrderInfoBySingleMulti($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $type = !empty($param['type']) ? $param['type'] : 1;
        $where = "main.mem_id = '$wx_id' and main.status = 1 and main.order_from = 2  ";

        if($type == 2){
            $con = "and main.order_status = 1";
        }elseif($type == 3){
            $con = "and main.order_status = 2";
        }elseif($type == 4){
            $con = "and main.order_status = 3";
        }elseif($type == 5){
            $con = "and main.order_status = 4";
        }elseif($type == 6){
            $con = "and main.order_status = 5";
        }else{
            $con = "";
        }
        $where .= $con;

        $res = Db::table('store_group_main_orders')->alias('main')->field('main.id as order_id,main.order_no,main.bis_id,bis.bis_name,bis.thumb,main.total_amount,main.order_status,main.express_no,mode.post_code,mode.post_mode')
            ->join('store_post_mode mode','main.mode = mode.id','LEFT')
            ->join('store_bis bis','main.bis_id = bis.id','LEFT')
            ->where($where)
            ->order('main.create_time desc')
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        $result = array();
        foreach($res as $val){
            $result[$index]['order_id'] = $val['order_id'];
            $result[$index]['bis_id'] = $val['bis_id'];
            $result[$index]['bis_name'] = $val['bis_name'];
            $result[$index]['thumb'] = $val['thumb'];
            $result[$index]['order_no'] = $val['order_no'];
            $result[$index]['amount'] = $val['total_amount'];
            $result[$index]['status'] = $val['order_status'];

            switch($val['order_status']){
                case 1:
                    $status_text =  '待付款';
                    break;
                case 2:
                    $status_text =  '待成团';
                    break;
                case 3:
                    $status_text =  '待发货';
                    break;
                case 4:
                    $status_text =  '待收货';
                    break;
                default:
                    $status_text =  '已完成';
                    break;
            }
            $result[$index]['status_text'] = $status_text;
            $result[$index]['pro_info'] = $this->getSubOrderInfoBySingle($val['order_id']);
            $result[$index]['post_code'] = $val['post_code'];
            $result[$index]['express_no'] = $val['express_no'];
            $result[$index]['post_mode'] = $val['post_mode'];
            $index ++;
        }

        return $result;
    }

    //生成订单(普通商城版)
    public function makeOrder($param){
        //获取参数
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $rec_name = !empty($param['rec_name']) ? $param['rec_name'] : '';
        $mobile = !empty($param['mobile']) ? $param['mobile'] : '';
        $address = !empty($param['address']) ? $param['address'] : '';
        $id_no = !empty($param['id_no']) ? $param['id_no'] : '';
        $total_amount = !empty($param['total_amount']) ? $param['total_amount'] : '';
        $with_balance_amount = !empty($param['with_balance_amount']) ? $param['with_balance_amount'] : '';
        $remark = !empty($param['remark']) ? $param['remark'] : '';
        $pro_amount = !empty($param['pro_amount']) ? $param['pro_amount'] : '';
        $transport_fee = !empty($param['transport_fee']) ? $param['transport_fee'] : '';
        $selected_transport_type = !empty($param['selected_transport_type']) ? $param['selected_transport_type'] : '';
        $pro_info = !empty($param['pro_info']) ? $param['pro_info'] : '';
        $appid = !empty($param['appid']) ? $param['appid'] : '';
        $secret = !empty($param['secret']) ? $param['secret'] : '';
        $create_time = date('Y-m-d H:i:s');
        $update_time = date('Y-m-d H:i:s');

        //补全店铺id格式
        if($bis_id < 10){
            $new_bis_id = '000'.$bis_id;
        }elseif($bis_id < 100 and $bis_id >=10){
            $new_bis_id = '00'.$bis_id;
        }elseif($bis_id < 1000 and $bis_id >=100){
            $new_bis_id = '0'.$bis_id;
        }else{
            $new_bis_id = $bis_id;
        }

        //设置主订单表字段
        $main_data = [
            'bis_id'  => $bis_id,
            'mem_id'  => $mem_id,
            'rec_name' => $rec_name,
            'mobile'  => $mobile,
            'address'  => $address,
            'id_no'  => $id_no,
            'payment'  => 1,
            'order_no'  => substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
            'total_amount'  => $total_amount,
            'with_balance_amount'  => $with_balance_amount,
            'create_time'  => $create_time,
            'pro_total_amount'  => $pro_amount,
            'transport_fee'  => $transport_fee,
            'selected_transport_type'  => $selected_transport_type,
            'appid'  => $appid,
            'secret'  => $secret,
            'update_time'  => $update_time,
            'order_status'  => $total_amount == 0 ? 3 : 2,
            'remark'  => $remark,
            'order_type'  => self::ORI_ORDER_TYPE
        ];

        //向主表添加数据
        $main_res = Db::table('store_main_orders')->insertGetId($main_data);

        if(!$main_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加主订单失败'
            ));
            exit;
        }

        $sub_data = array();
        $cart_ids = '';
        foreach($pro_info as $val){
            //设置副订单表字段
            $temp_sub_data = [
                'main_id'  => $main_res,
                'pro_id'  => $val['pro_id'],
                'count'  => $val['count'],
                'rec_rate'  => $val['rec_rate'],
                'unit_price'  => $val['associator_price'],
                'amount'  => $val['count'] * $val['associator_price'],
                'rec_amount'  => ($val['count'] * $val['associator_price']) * $val['rec_rate']
            ];
            array_push($sub_data,$temp_sub_data);

            //设置接收的购物车表信息
            $cart_ids .= $val['cart_id'].',';
        }

        //向副表添加数据
        $sub_res = Db::table('store_sub_orders')->insertAll($sub_data);
        if(!$sub_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加副订单失败'
            ));
            exit;
        }
        //格式化购物车表信息
        $cart_ids = substr($cart_ids,0,-1);
        //更改对应购物车信息状态
        $cart_data['status'] = 0;
        $update_cart_res = Db::table('store_shopping_carts')->where("id in ($cart_ids)")->update($cart_data);
        if(!$update_cart_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '更改购物车状态失败'
            ));
            exit;
        }

        return $main_res;
    }

    //积分商品生成订单(多用户版)
    public function makeJfOrder($param){
        //获取参数
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $rec_name = !empty($param['rec_name']) ? $param['rec_name'] : '';
        $mobile = !empty($param['mobile']) ? $param['mobile'] : '';
        $address = !empty($param['address']) ? $param['address'] : '';
        $id_no = !empty($param['id_no']) ? $param['id_no'] : '';
        $total_amount = !empty($param['total_amount']) ? $param['total_amount'] : '';
        $jifen_amount = !empty($param['jifen_amount']) ? $param['jifen_amount'] : '';
        $order_status = !empty($param['order_status']) ? $param['order_status'] : '';
        $remark = !empty($param['remark']) ? $param['remark'] : '';
        $order_from = !empty($param['order_from']) ? $param['order_from'] : '';
        $pro_amount = !empty($param['pro_amount']) ? $param['pro_amount'] : '';
        $transport_fee = !empty($param['transport_fee']) ? $param['transport_fee'] : '';
        $selected_transport_type = !empty($param['selected_transport_type']) ? $param['selected_transport_type'] : '';
        $pro_info = !empty($param['pro_info']) ? $param['pro_info'] : '';
        $appid = !empty($param['appid']) ? $param['appid'] : '';
        $secret = !empty($param['secret']) ? $param['secret'] : '';
        $create_time = date('Y-m-d H:i:s');
        $update_time = date('Y-m-d H:i:s');
        $pay_time = date('Y-m-d H:i:s');

        //补全店铺id格式
        if($bis_id < 10){
            $new_bis_id = '000'.$bis_id;
        }elseif($bis_id < 100 and $bis_id >=10){
            $new_bis_id = '00'.$bis_id;
        }elseif($bis_id < 1000 and $bis_id >=100){
            $new_bis_id = '0'.$bis_id;
        }else{
            $new_bis_id = $bis_id;
        }

        //设置主订单表字段
        $main_data = [
            'bis_id'  => $bis_id,
            'mem_id'  => $mem_id,
            'rec_name' => $rec_name,
            'mobile'  => $mobile,
            'address'  => $address,
            'id_no'  => $id_no,
            'payment'  => 1,
            'order_no'  => substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
            'total_amount'  => $total_amount,
            'create_time'  => $create_time,
            'pro_total_amount'  => $pro_amount,
            'transport_fee'  => $transport_fee,
            'selected_transport_type'  => $selected_transport_type,
            'jifen'  => $jifen_amount,
            'appid'  => $appid,
            'secret'  => $secret,
            'update_time'  => $update_time,
            'order_status'  => $order_status,
            'order_from'  => $order_from,
            'remark'  => $remark,
            'order_type'  => self::JF_ORDER_TYPE
        ];

        if($order_status == 3){
            $main_data['pay_time'] = $pay_time;
        }

        //向主表添加数据
        $main_res = Db::table('store_main_orders')->insertGetId($main_data);

        if(!$main_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加主订单失败'
            ));
            exit;
        }

        $sub_data = array();
        $cart_ids = '';
        foreach($pro_info as $val){
            //设置副订单表字段
            $temp_sub_data = [
                'main_id'  => $main_res,
                'pro_id'  => $val['pro_id'],
                'count'  => $val['count'],
                'rec_rate'  => $val['rec_rate'],
                'unit_price'  => $val['associator_price'],
                'amount'  => $val['count'] * $val['associator_price'],
                'rec_amount'  => ($val['count'] * $val['associator_price']) * $val['rec_rate']
            ];
            array_push($sub_data,$temp_sub_data);

            //设置接收的购物车表信息
            $cart_ids .= $val['cart_id'].',';
        }

        //向副表添加数据
        $sub_res = Db::table('store_sub_orders')->insertAll($sub_data);
        if(!$sub_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加副订单失败'
            ));
            exit;
        }
        //格式化购物车表信息
        $cart_ids = substr($cart_ids,0,-1);
        //更改对应购物车信息状态
        $cart_data['status'] = 0;
        $update_cart_res = Db::table('store_shopping_carts')->where("id in ($cart_ids)")->update($cart_data);
        if(!$update_cart_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '更改购物车状态失败'
            ));
            exit;
        }

        return $main_res;
    }

    //生成订单(拼团-单独购买版)
    public function makeOrderBySingle($param){
        //获取参数
        $from = !empty($param['from']) ? $param['from'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $pro_id = !empty($param['pro_id']) ? $param['pro_id'] : '';
        $rec_name = !empty($param['rec_name']) ? $param['rec_name'] : '';
        $mobile = !empty($param['mobile']) ? $param['mobile'] : '';
        $address = !empty($param['address']) ? $param['address'] : '';
        $id_no = !empty($param['id_no']) ? $param['id_no'] : '';
        $total_amount = !empty($param['total_amount']) ? $param['total_amount'] : '';
        $remark = !empty($param['remark']) ? $param['remark'] : '';
        $pro_amount = !empty($param['pro_amount']) ? $param['pro_amount'] : '';
        $transport_fee = !empty($param['transport_fee']) ? $param['transport_fee'] : '';
        $selected_transport_type = !empty($param['selected_transport_type']) ? $param['selected_transport_type'] : '';
        $pro_info = !empty($param['pro_info']) ? $param['pro_info'] : '';
        $appid = !empty($param['appid']) ? $param['appid'] : '';
        $secret = !empty($param['secret']) ? $param['secret'] : '';
        $pintuan_count = !empty($param['pintuan_count']) ? $param['pintuan_count'] : '';
        $group_num = !empty($param['group_num']) ? $param['group_num'] : '';
        $order_from = !empty($param['order_from']) ? $param['order_from'] : 1;
        $create_time = date('Y-m-d H:i:s');
        $update_time = date('Y-m-d H:i:s');

        //补全店铺id格式
        if($bis_id < 10){
            $new_bis_id = '000'.$bis_id;
        }elseif($bis_id < 100 and $bis_id >=10){
            $new_bis_id = '00'.$bis_id;
        }elseif($bis_id < 1000 and $bis_id >=100){
            $new_bis_id = '0'.$bis_id;
        }else{
            $new_bis_id = $bis_id;
        }

        if($from == 'single'){
            //设置主订单表字段
            $main_data = [
                'bis_id'  => $bis_id,
                'mem_id'  => $mem_id,
                'pro_id'  => $pro_id,
                'rec_name' => $rec_name,
                'mobile'  => $mobile,
                'address'  => $address,
                'id_no'  => $id_no,
                'payment'  => 1,
                'order_no'  => substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
                'total_amount'  => $total_amount,
                'order_type'  => 1,
                'create_time'  => $create_time,
                'pro_total_amount'  => $pro_amount,
                'transport_fee'  => $transport_fee,
                'selected_transport_type'  => $selected_transport_type,
                'appid'  => $appid,
                'secret'  => $secret,
                'update_time'  => $update_time,
                'order_status'  => 1,
                'remark'  => $remark,
                'order_from'  => $order_from
            ];
        }else if($from == 'group'){
            //设置主订单表字段
            $main_data = [
                'bis_id'  => $bis_id,
                'mem_id'  => $mem_id,
                'pro_id'  => $pro_id,
                'rec_name' => $rec_name,
                'mobile'  => $mobile,
                'address'  => $address,
                'id_no'  => $id_no,
                'payment'  => 1,
                'order_no'  => substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
                'total_amount'  => $total_amount,
                'order_type'  => 2,
                'create_time'  => $create_time,
                'pro_total_amount'  => $pro_amount,
                'transport_fee'  => $transport_fee,
                'selected_transport_type'  => $selected_transport_type,
                'appid'  => $appid,
                'secret'  => $secret,
                'update_time'  => $update_time,
                'order_status'  => 1,
                'remark'  => $remark,
                'group_num'  => substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').rand(100000,999999),
                'pintuan_count'  => $pintuan_count,
                'group_status'  => 1,
                'group_identity'  => 1,
                'order_from'  => $order_from
            ];
        }else{
            //设置主订单表字段
            $main_data = [
                'bis_id'  => $bis_id,
                'mem_id'  => $mem_id,
                'pro_id'  => $pro_id,
                'rec_name' => $rec_name,
                'mobile'  => $mobile,
                'address'  => $address,
                'id_no'  => $id_no,
                'payment'  => 1,
                'order_no'  => substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
                'total_amount'  => $total_amount,
                'order_type'  => 3,
                'create_time'  => $create_time,
                'pro_total_amount'  => $pro_amount,
                'transport_fee'  => $transport_fee,
                'selected_transport_type'  => $selected_transport_type,
                'appid'  => $appid,
                'secret'  => $secret,
                'update_time'  => $update_time,
                'order_status'  => 1,
                'remark'  => $remark,
                'group_num'  => $group_num,
                'pintuan_count'  => $pintuan_count,
                'group_status'  => 1,
                'group_identity'  => 2,
                'order_from'  => $order_from
            ];
        }

        //向主表添加数据
        $main_res = Db::table('store_group_main_orders')->insertGetId($main_data);

        if(!$main_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加主订单失败'
            ));
            exit;
        }

        //设置副订单表字段
        $sub_data = [
            'main_id'  => $main_res,
            'pro_id'  => $pro_info['pro_id'],
            'count'  => $pro_info['count'],
            'rec_rate'  => $pro_info['rec_rate'],
            'unit_price'  => $pro_info['associator_price'],
            'amount'  => $pro_info['count'] * $pro_info['associator_price'],
            'rec_amount'  => ($pro_info['count'] * $pro_info['associator_price']) * $pro_info['rec_rate']
        ];

        //设置接收的购物车表信息
        $cart_ids = $pro_info['cart_id'];

        //向副表添加数据
        $sub_res = Db::table('store_group_sub_orders')->insert($sub_data);
        if(!$sub_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加副订单失败'
            ));
            exit;
        }

        //更改对应购物车信息状态
        $cart_data['status'] = 0;
        $update_cart_res = Db::table('store_shopping_carts')->where("id = ".$cart_ids)->update($cart_data);
        if(!$update_cart_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '更改购物车状态失败'
            ));
            exit;
        }

        return $main_res;
    }

    //生成订单(多用户商城版)
    public function makeOrderMulti($param){
        //获取参数
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $cart_info = !empty($param['cart_info']) ? $param['cart_info'] : '';
        $address_info = !empty($param['address_info']) ? $param['address_info'] : '';
        $appid = !empty($param['appid']) ? $param['appid'] : '';
        $secret = !empty($param['secret']) ? $param['secret'] : '';

        //设置参数
        $rec_name = $address_info['rec_name'];
        $mobile = $address_info['mobile'];
        $address = $address_info['province'].$address_info['city'].$address_info['area'].$address_info['address'];
        $id_no = $address_info['idno'];
        $create_time = date('Y-m-d H:i:s');
        $update_time = date('Y-m-d H:i:s');

        foreach($cart_info as $val){
            $bis_id = $val['bis_id'];
            //补全店铺id格式
            if($bis_id < 10){
                $new_bis_id = '000'.$bis_id;
            }elseif($bis_id < 100 and $bis_id >=10){
                $new_bis_id = '00'.$bis_id;
            }elseif($bis_id < 1000 and $bis_id >=100){
                $new_bis_id = '0'.$bis_id;
            }else{
                $new_bis_id = $bis_id;
            }
            //设置主订单表字段
            $main_data = [
                'bis_id'  => $new_bis_id,
                'mem_id'  => $mem_id,
                'rec_name' => $rec_name,
                'mobile'  => $mobile,
                'address'  => $address,
                'id_no'  => $id_no,
                'payment'  => 1,
                'order_no'  => substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
                'total_amount'  => $val['total_amount'],
                'create_time'  => $create_time,
                'pro_total_amount'  => $val['pro_amount'],
                'transport_fee'  => $val['transport_fee'],
                'selected_transport_type'  => $val['selected_transport_type'],
                'appid'  => $appid,
                'secret'  => $secret,
                'update_time'  => $update_time,
                'order_status'  => 2,
                'order_from'  => 2,
            ];
            $pro_info = $val['pro_info'];
            //向主表添加数据
            $main_res = Db::table('store_main_orders')->insertGetId($main_data);

            if(!$main_res){
                echo json_encode(array(
                    'statuscode'  => 0,
                    'message'     => '添加主订单失败'
                ));
                exit;
            }
            $sub_data = array();
            $cart_ids = '';
            foreach($pro_info as $v){
                //设置副订单表字段
                $temp_sub_data = [
                    'main_id'  => $main_res,
                    'pro_id'  => $v['pro_id'],
                    'count'  => $v['count'],
                    'rec_rate'  => $v['rec_rate'],
                    'unit_price'  => $v['associator_price'],
                    'amount'  => $v['count'] * $v['associator_price'],
                    'rec_amount'  => ($v['count'] * $v['associator_price']) * $v['rec_rate']
                ];
                array_push($sub_data,$temp_sub_data);

                //设置接收的购物车表信息
                $cart_ids .= $v['cart_id'].',';
            }

            //向副表添加数据
            $sub_res = Db::table('store_sub_orders')->insertAll($sub_data);
            if(!$sub_res){
                echo json_encode(array(
                    'statuscode'  => 0,
                    'message'     => '添加副订单失败'
                ));
                exit;
            }
            //格式化购物车表信息
            $cart_ids = substr($cart_ids,0,-1);
            //更改对应购物车信息状态
            $cart_data['status'] = 0;
            $update_cart_res = Db::table('store_shopping_carts')->where("id in ($cart_ids)")->update($cart_data);
            if(!$update_cart_res){
                echo json_encode(array(
                    'statuscode'  => 0,
                    'message'     => '更改购物车状态失败'
                ));
                exit;
            }

        }

        return 1;
    }

    //获取订单副表信息(普通商城版)
    public function getSubOrderInfo($main_id){
        $where = "sub.main_id = $main_id and sub.status = 1";
        $res = Db::table('store_sub_orders')->alias('sub')->field('pro.p_name,img.thumb,con.con_content1,con.con_content2,count,unit_price,amount')
            ->join('store_pro_config con','sub.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('sub.id asc')
            ->select();

        return $res;
    }

    //获取订单副表信息(拼团版)
    public function getGroupSubOrderInfo($main_id){
        $where = "sub.main_id = $main_id and sub.status = 1";
        $res = Db::table('store_group_sub_orders')->alias('sub')->field('pro.p_name,img.thumb,con.con_content1,con.con_content2,count,unit_price,amount')
            ->join('store_pro_config con','sub.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('sub.id asc')
            ->select();

        return $res;
    }

    //获取订单副表信息(拼团单独购买版)
    public function getSubOrderInfoBySingle($main_id){
        $where = "sub.main_id = $main_id and sub.status = 1";
        $res = Db::table('store_group_sub_orders')->alias('sub')->field('pro.p_name,img.thumb,con.con_content1,con.con_content2')
            ->join('store_pro_config con','sub.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('sub.id asc')
            ->select();

        return $res;
    }

    //设置主订单表推荐人及佣金信息(普通商城版)
    public function setMainRecInfo($order_id,$rec_id){
        //获取该订单全部推荐佣金
        $rec_amount = Db::table('store_sub_orders')
                ->where('main_id = '.$order_id .' and status = 1')
                ->SUM('rec_amount');

        //更新主订单表相关信息
        $data['rec_id'] = $rec_id;
        $data['rec_income'] = $rec_amount;
        $res = Db::table('store_main_orders')->where('id = '.$order_id)->update($data);

        //设置推荐人的佣金总额
        $mem_res = Db::table('store_members')->field('ketixian')->where('id = '.$rec_id)->find();
        $ketixian_amount = $mem_res['ketixian'];
        $ketixian_data['ketixian'] = $ketixian_amount + $rec_amount;
        $new_mem_res = Db::table('store_members')->where('id = '.$rec_id)->update($ketixian_data);

        //生成佣金订单
        $this->makeRecOrder($order_id,$rec_id,'org');
        return $res;
    }

    //设置主订单表推荐人及佣金信息(拼团单独购买版)
    public function setMainRecInfoBySingle($order_id,$rec_id){
        //获取该订单全部推荐佣金
        $rec_amount = Db::table('store_group_sub_orders')
            ->where('main_id = '.$order_id .' and status = 1')
            ->SUM('rec_amount');

        //更新主订单表相关信息
        $data['rec_id'] = $rec_id;
        $data['rec_income'] = $rec_amount;
        $res = Db::table('store_group_main_orders')->where('id = '.$order_id)->update($data);

        //设置推荐人的佣金总额
        $mem_res = Db::table('store_members')->field('ketixian')->where('id = '.$rec_id)->find();
        $ketixian_amount = $mem_res['ketixian'];
        $ketixian_data['ketixian'] = $ketixian_amount + $rec_amount;
        $new_mem_res = Db::table('store_members')->where('id = '.$rec_id)->update($ketixian_data);

        //生成佣金订单
        $this->makeRecOrder($order_id,$rec_id,'single');
        return $res;
    }

    //生成佣金订单
    public function makeRecOrder($order_id,$rec_id,$type){
        if($type == 'org'){
            $order_info = Db::table('store_main_orders')
                ->field('id as order_id,order_no,total_amount,rec_income,mem_id,create_time,rec_name')
                ->where('id = '.$order_id)
                ->find();
        }else{
            $order_info = Db::table('store_group_main_orders')
                ->field('id as order_id,order_no,total_amount,rec_income,mem_id,create_time,rec_name')
                ->where('id = '.$order_id)
                ->find();
        }

        //设置佣金订单字段
        $data = [
            'order_no' => $order_info['order_no'],
            'buyer_name' => $order_info['rec_name'],
            'total_amount' => $order_info['total_amount'],
            'rec_amount' => $order_info['rec_income'],
            'pay_mem_id' => $order_info['mem_id'],
            'rec_id' => $rec_id,
            'create_time'  => date('Y-m-d H:i:s')
        ];

        Db::table('store_rec_orders')->insert($data);
    }

    //查询佣金订单
    public function getRecOrders($param){
        //获取参数
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        $page = !empty($param['page']) ? $param['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        //查询用户id
        $where = "mem_id = '$openid'";
        $user_info = Db::table('store_members')->field('id')->where($where)->find();
        $user_id = $user_info['id'];
        //查询佣金订单
        $res = Db::table('store_rec_orders')
                ->where('rec_id = '.$user_id)
                ->order('create_time desc')
                ->limit($offset,$limit)
                ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        foreach($res as $val){
            $res[$index]['create_time'] = substr($val['create_time'],0,10);
            $index++;
        }

        return $res;
    }

    //查询佣金订单数量
    public function getRecOrderCount($param){
        //获取参数
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        //查询用户id
        $where = "mem_id = '$openid'";
        $user_info = Db::table('store_members')->field('id')->where($where)->find();
        $user_id = $user_info['id'];
        //查询佣金订单
        $res = Db::table('store_rec_orders')
            ->where('rec_id = '.$user_id)
            ->count();

        return $res;
    }

    //生成提现订单
    public function makeTixianOrders($param){
        //获取参数
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        $amount = !empty($param['amount']) ? $param['amount'] : '';
        $name = !empty($param['name']) ? $param['name'] : '';
        $id_no = !empty($param['id_no']) ? $param['id_no'] : '';

        //设置提现订单数据
        $data = [
            'tranid' => 'tx'.substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').rand(100000,999999),
            'bis_id'  => $bis_id,
            'amount'  => $amount,
            'mem_id'  => $openid,
            'name'  => $name,
            'id_no'  => $id_no,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
            'tx_status'  => 1
        ];

        //生成提现订单
        $res = Db::table('store_withdraw_records')->insert($data);


        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '生成提现订单失败'
            ));
            exit;
        }
        //会员表相应字段操作
        $where = "mem_id = '$openid'";
        $mem_res = Db::table('store_members')->field('ketixian,tixianzhong')->where($where)->find();
        $new_mem_data['ketixian'] = $mem_res['ketixian'] - $amount;
        $new_mem_data['tixianzhong'] = $mem_res['tixianzhong'] + $amount;
        $update_mem_res = Db::table('store_members')->where($where)->update($new_mem_data);
        if(!$update_mem_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '会员表操作失败'
            ));
            exit;
        }
        return $res;
    }

    //查看提现记录
    public function getTixianRecords($openid){
        $where = "mem_id = '$openid' and status = 1";
        $res = Db::table('store_withdraw_records')
            ->field('tranid,amount,create_time,tixian_status')
            ->where($where)
            ->order('create_time desc')
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        foreach($res as $val){
            switch($val['tixian_status']){
                case 1:
                    $res[$index]['t_status'] = '申请中';
                    break;
                case 2:
                    $res[$index]['t_status'] = '提现成功';
                    break;
                default :
                    $res[$index]['t_status'] = '提现失败';
                    break;
            }
            $index ++;
        }
        return $res;
    }

    //生成充值订单
    public function makeRechargeOrder($param){
        //校验数据
        CheckService::checkEmpty($param['bis_id'],'店铺id');
        CheckService::checkEmpty($param['openid'],'用户openid');
        CheckService::checkEmpty($param['amount'],'充值金额');

        $rechargeId = 're'.date('Y').date('m').date('d').date('H').date('i').date('s').rand(100000,999999);

        $data = [
            'bis_id' => $param['bis_id'],
            'openid' => $param['openid'],
            'bis_type' => 1,
            'amount' => $param['amount'],
            'recharge_id'  => $rechargeId,
            'recharge_status'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $res = Db::table('store_member_recharge_records')->insertGetId($data);

        return $res;

    }
}