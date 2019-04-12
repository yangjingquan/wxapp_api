<?php
namespace app\catering\model;
use think\Model;
use think\Db;

class MallOrder extends Model{

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

        $res = Db::table('cy_mall_main_orders')->alias('main')->field('main.id as order_id,main.order_no,main.total_amount,main.order_status,main.express_no,mode.post_code,mode.post_mode,main.jifen')
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

    //获取订单详情信息(普通商城版)
    public function getOrderDetailInfo($param){
        //获取参数
        $order_id = !empty($param['order_id']) ? $param['order_id'] : '';
        $where = "main.order_no = '$order_id'";

        $res = Db::table('cy_mall_main_orders')->alias('main')->field('main.id as order_id,main.order_no,main.total_amount,main.order_status,main.mobile,main.address,main.rec_name,main.create_time,main.pay_time,main.payment,main.pro_total_amount,main.transport_fee,main.express_no,mode.post_code,mode.post_mode,main.jifen')
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

    //积分商品生成订单(多用户版)
    public function makeJfOrder($param){
        //获取参数
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';
        $rec_name = !empty($param['rec_name']) ? $param['rec_name'] : '';
        $mobile = !empty($param['mobile']) ? $param['mobile'] : '';
        $address = !empty($param['address']) ? $param['address'] : '';
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
        $main_res = Db::table('cy_mall_main_orders')->insertGetId($main_data);

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
                'unit_price'  => $val['associator_price'],
                'amount'  => $val['count'] * $val['associator_price'],
            ];
            array_push($sub_data,$temp_sub_data);

            //设置接收的购物车表信息
            $cart_ids .= $val['cart_id'].',';
        }

        //向副表添加数据
        $sub_res = Db::table('cy_mall_sub_orders')->insertAll($sub_data);
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
        $update_cart_res = Db::table('cy_shopping_carts')->where("id in ($cart_ids)")->update($cart_data);
        if(!$update_cart_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '更改购物车状态失败'
            ));
            exit;
        }

        return $main_res;
    }



    //获取订单副表信息(普通商城版)
    public function getSubOrderInfo($main_id){
        $where = "sub.main_id = $main_id and sub.status = 1";
        $res = Db::table('cy_mall_sub_orders')->alias('sub')->field('pro.p_name,img.thumb,con.con_content1,con.con_content2,count,unit_price,amount')
            ->join('cy_mall_pro_config con','sub.pro_id = con.id','LEFT')
            ->join('cy_mall_products pro','con.pro_id = pro.id','LEFT')
            ->join('cy_mall_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('sub.id asc')
            ->select();

        return $res;
    }

    //设置主订单表推荐人及佣金信息(普通商城版)
    public function setMainRecInfo($order_id,$rec_id){
        //获取该订单全部推荐佣金
        $rec_amount = Db::table('cy_mall_sub_orders')
                ->where('main_id = '.$order_id .' and status = 1')
                ->SUM('rec_amount');

        //更新主订单表相关信息
        $data['rec_id'] = $rec_id;
        $data['rec_income'] = $rec_amount;
        $res = Db::table('cy_mall_main_orders')->where('id = '.$order_id)->update($data);

        //设置推荐人的佣金总额
        $mem_res = Db::table('cy_members')->field('ketixian')->where('id = '.$rec_id)->find();
        $ketixian_amount = $mem_res['ketixian'];
        $ketixian_data['ketixian'] = $ketixian_amount + $rec_amount;
        $new_mem_res = Db::table('cy_members')->where('id = '.$rec_id)->update($ketixian_data);

        //生成佣金订单
        $this->makeRecOrder($order_id,$rec_id,'org');
        return $res;
    }

    //生成佣金订单
    public function makeRecOrder($order_id,$rec_id,$type){
        if($type == 'org'){
            $order_info = Db::table('cy_mall_main_orders')
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

        Db::table('cy_rec_orders')->insert($data);
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
}