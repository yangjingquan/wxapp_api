<?php
namespace app\catering\model;
use think\Model;
use think\Db;
use app\api\service\CheckService;

class Order extends Model{

    //获取点餐/外卖订单信息
    public function getNormalOrderInfo($param){
        //获取参数
        $wx_id = !empty($param['openid']) ? $param['openid'] : '';
        $type = !empty($param['type']) ? $param['type'] : 1;
        $where = "main.mem_id = '$wx_id' and main.type = ".$type." and main.status = 1 ";


        $res = Db::table('cy_main_orders')->alias('main')->field('main.id as order_id,main.total_amount,main.order_status,bis.bis_name')
            ->join('cy_bis bis','main.bis_id = bis.id','LEFT')
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
        if($type == 1){
            foreach($res as $val){
                $result[$index]['order_id'] = $val['order_id'];
                $result[$index]['amount'] = $val['total_amount'];
                $result[$index]['status'] = $val['order_status'];
                $result[$index]['bis_name'] = $val['bis_name'];

                switch($val['order_status']){
                    case 0:
                        $status_text =  '未确认';
                        break;
                    case 1:
                        $status_text =  '已点餐';
                        break;
                    case 2:
                        $status_text =  '已付款';
                        break;
                    default:
                        $status_text =  '已完成';
                        break;
                }
                $result[$index]['status_text'] = $status_text;
                $result[$index]['pro_count'] = $this->getSubOrderProCount($val['order_id']);
                $result[$index]['pro_info'] = $this->getSubOrderInfoWithLimit($val['order_id']);
                $index ++;
            }
        }else{
            foreach($res as $val){
                $result[$index]['order_id'] = $val['order_id'];
                $result[$index]['amount'] = $val['total_amount'];
                $result[$index]['status'] = $val['order_status'];
                $result[$index]['bis_name'] = $val['bis_name'];

                switch($val['order_status']){
                    case 1:
                        $status_text =  '未付款';
                        break;
                    case 2:
                        $status_text =  '已付款';
                        break;
                    case 3:
                        $status_text =  '配送中';
                        break;
                    default:
                        $status_text =  '已完成';
                        break;
                }
                $result[$index]['status_text'] = $status_text;
                $result[$index]['pro_count'] = $this->getSubOrderProCount($val['order_id']);
                $result[$index]['pro_info'] = $this->getSubOrderInfoWithLimit($val['order_id']);
                $index ++;
            }
        }


        return $result;
    }

    //生成点餐订单
    public function makeDcOrder($param){
        //获取参数
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        $table = !empty($param['table']) ? $param['table'] : '';
        $total_amount = !empty($param['total_amount']) ? $param['total_amount'] : '';
        $with_balance_amount = !empty($param['with_balance_amount']) ? $param['with_balance_amount'] : '';
        $remark = !empty($param['remark']) ? $param['remark'] : '';
        $pro_info = !empty($param['cart_info']) ? $param['cart_info'] : '';
        $order_status = !empty($param['order_status']) ? $param['order_status'] : '';
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
            'mem_id'  => $openid,
            'type'  => 1,
            'table_id'  => $table,
            'order_no'  => '90'.substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
            'total_amount'  => $total_amount,
            'with_balance_amount'  => $with_balance_amount,
            'create_time'  => $create_time,
            'update_time'  => $update_time,
            'order_status'  => $order_status,
            'remark'  => $remark
        ];

        //向主表添加数据
        $main_res = Db::table('cy_main_orders')->insertGetId($main_data);

        if(!$main_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加主订单失败'
            ));
            exit;
        }

        $sub_data = array();
        foreach($pro_info as $val){
            //设置副订单表字段
            $temp_sub_data = [
                'main_id'  => $main_res,
                'pro_id'  => $val['id'],
                'count'  => $val['selected_count'],
                'unit_price'  => $val['original_price'],
                'amount'  => $val['selected_count'] * $val['original_price']
            ];
            array_push($sub_data,$temp_sub_data);


        }

        //向副表添加数据
        $sub_res = Db::table('cy_sub_orders')->insertAll($sub_data);
        if(!$sub_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加副订单失败'
            ));
            exit;
        }

        return $main_res;
    }

    //获取订单副表信息
    public function getSubOrderInfo($main_id){
        $where = "sub.main_id = $main_id and sub.status = 1";
        $res = Db::table('cy_sub_orders')->alias('sub')->field('pro.p_name,pro.image,sub.count,pro.original_price')
            ->join('cy_products pro','sub.pro_id = pro.id','LEFT')
            ->where($where)
            ->order('sub.id asc')
            ->select();

        return $res;
    }

    //获取订单副表信息(带限制)
    public function getSubOrderInfoWithLimit($main_id){
        $where = "sub.main_id = $main_id and sub.status = 1";
        $res = Db::table('cy_sub_orders')->alias('sub')->field('pro.p_name,pro.image,sub.count,pro.original_price')
            ->join('cy_products pro','sub.pro_id = pro.id','LEFT')
            ->where($where)
            ->order('sub.id asc')
            ->limit(3)
            ->select();

        return $res;
    }

    //获取订单副表信息数量
    public function getSubOrderProCount($main_id){
        $where = "main_id = $main_id and status = 1";
        $res = Db::table('cy_sub_orders')
            ->where($where)
            ->count();

        return $res;
    }

    //生成外卖订单
    public function makeWmOrder($param){
        //获取参数
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        $total_amount = !empty($param['total_amount']) ? $param['total_amount'] : '';
        $rec_name = !empty($param['rec_name']) ? $param['rec_name'] : '';
        $mobile = !empty($param['mobile']) ? $param['mobile'] : '';
        $address = !empty($param['addressDetail']) ? $param['addressDetail'] : '';
        $remark = !empty($param['remark']) ? $param['remark'] : '';
        $pro_info = !empty($param['cart_info']) ? $param['cart_info'] : '';
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
            'type'  => 2,
            'mem_id'  => $openid,
            'order_no'  => '90'.substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
            'total_amount'  => $total_amount,
            'create_time'  => $create_time,
            'update_time'  => $update_time,
            'order_status'  => 1,
            'remark'  => $remark,
            'rec_name'  => $rec_name,
            'mobile'  => $mobile,
            'address'  => $address,

        ];

        //向主表添加数据
        $main_res = Db::table('cy_main_orders')->insertGetId($main_data);

        if(!$main_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加主订单失败'
            ));
            exit;
        }

        $sub_data = array();
        foreach($pro_info as $val){
            //设置副订单表字段
            $temp_sub_data = [
                'main_id'  => $main_res,
                'pro_id'  => $val['id'],
                'count'  => $val['selected_count'],
                'unit_price'  => $val['original_price'],
                'amount'  => $val['selected_count'] * $val['original_price']
            ];
            array_push($sub_data,$temp_sub_data);


        }

        //向副表添加数据
        $sub_res = Db::table('cy_sub_orders')->insertAll($sub_data);
        if(!$sub_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加副订单失败'
            ));
            exit;
        }

        return $main_res;
    }

    //获取点餐订单详情
    public function getDcOrderDetail($order_id){
        $res = Db::table('cy_main_orders')
                ->field('id as main_id,type,order_no,total_amount,create_time,table_id,remark,order_status')
                ->where('id = '.$order_id)
                ->find();


        $res['pro_info'] = $this->getSubOrderInfo($res['main_id']);
        return $res;
    }

    //获取外卖订单详情
    public function getWmOrderDetail($order_id){
        $res = Db::table('cy_main_orders')
            ->field('id as main_id,type,order_no,total_amount,create_time,remark,order_status,rec_name,mobile,address')
            ->where('id = '.$order_id)
            ->find();

        switch($res['order_status']){
            case 1:
                $res['status_text'] = '未付款';
                break;
            case 2:
                $res['status_text'] = '已付款';
                break;
            case 3:
                $res['status_text'] = '配送中';
                break;
            default:
                $res['status_text'] = '已完成';
                break;
        }

        $res['pro_info'] = $this->getSubOrderInfo($res['main_id']);
        return $res;
    }

    //获取预订订单信息
    public function getReserveOrderInfo($param){
        //获取参数
        $wx_id = !empty($param['openid']) ? $param['openid'] : '';

        $where = "o.openid = '$wx_id' and o.status = 1 ";
        $res = Db::table('cy_pre_orders')->alias('o')->field('o.id as order_id,o.date,time,o.type,o.link_man,o.order_status,o.create_time,bis.bis_name')
                ->join('cy_bis bis','o.bis_id = bis.id','left')
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
            switch($val['order_status']){
                case 1:
                    $status_text =  '预订中';
                    break;
                case 2:
                    $status_text =  '已取消';
                    break;
                case 3:
                    $status_text =  '预定成功';
                    break;
                default:
                    $status_text =  '预订成功';
                    break;
            }
            $res[$index]['status_text'] = $status_text;
            $index ++;
        }
        return $res;
    }

    //生成收银订单
    public function makeSyOrder($param){
        //获取参数
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        $amount = !empty($param['amount']) ? $param['amount'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';

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
        //设置数据
        $data = [
           'openid' => $openid,
           'order_no' => '70'.substr(date('Y'),2,2).date('m').date('d').date('H').date('i').date('s').$new_bis_id.rand(1000,9999),
           'total_amount' => $amount,
           'bis_id' => $bis_id,
           'create_time' => date('Y-m-d H:i:s')
        ];
        $res = Db::table('cy_pay_orders')->insertGetId($data);
        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '操作失败!'
            ));
            exit;
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
            'bis_type' => 2,
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