<?php
/**
 * Created by PhpStorm.
 * User: yangjingquan
 * Date: 2017/9/22
 * Time: 上午9:17
 */

namespace app\index\controller;
use think\Db;
use think\Exception;
use think\Loader;
use think\Log;

Loader::import('WxPayGroup.WxPayApi',EXTEND_PATH);

class Groupwxnotify extends \WxPayNotify{

    public function NotifyProcess($data, &$msg){
        if($data['result_code'] == 'SUCCESS'){
            //第一步 检测库存量 (待开发)
            //-----------------------
            //订单号
            $orderNo = $data['out_trade_no'];
            try{
                //通过订单号查询外部订单信息(订单总金额)
                $out_order_info = Db::table('store_group_main_orders')->field('order_type,total_amount')->where('order_no = '.$orderNo)->find();
                if($out_order_info['order_type'] == 3){
                    //比较微信返回的总金额和外部订单表内的总金额
                    if($out_order_info['total_amount'] * 100 == $data['total_fee']){
                        //更改外部订单状态,记录流水号
                        $order_status['order_status'] = 2;
                        $order_status['transaction_id'] = $data['transaction_id'];
                        $order_status['update_time'] = date('Y-m-d H:i:s');
                        $out_order_info = Db::table('store_group_main_orders')->where('order_no = '.$orderNo)->update($order_status);
                    }
                    $this->updateGroupOrderInfo($orderNo);
                    return true;
                }else{
                    //比较微信返回的总金额和外部订单表内的总金额
                    if($out_order_info['total_amount'] * 100 == $data['total_fee']){
                        //更改外部订单状态,记录流水号
                        $order_status['order_status'] = $out_order_info['order_type'] == 1 ? 3 : 2;
                        $order_status['transaction_id'] = $data['transaction_id'];
                        $order_status['update_time'] = date('Y-m-d H:i:s');
                        $out_order_info = Db::table('store_group_main_orders')->where('order_no = '.$orderNo)->update($order_status);
                    }
                    return true;
                }
            }catch(Exception $ex){
                Log::error($ex);
                return false;
            }
        }else{
            return true;
        }
    }

    //拼团模式下更新订单表信息
    public function updateGroupOrderInfo($order_no){
        $con = "order_no = '$order_no'";
        $main_order_info = Db::table('store_group_main_orders')->field('group_num')->where($con)->find();
        //获取参数
        $group_num = $main_order_info['group_num'];
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
            $main_res = Db::table('store_group_main_orders')->where($where1)->update($data);
        }
    }
}