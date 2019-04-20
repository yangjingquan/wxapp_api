<?php
/**
 * Created by PhpStorm.
 * User: yangjingquan
 * Date: 2017/9/22
 * Time: 上午9:17
 */

namespace app\pay\controller;
use think\Db;
use think\Exception;
use think\Loader;
use think\Log;

Loader::import('CateringWxPay.WxPayApi',EXTEND_PATH);

class CateringOriWxNotify extends \WxPayNotify{

    public function NotifyProcess($data, &$msg){
        if($data['result_code'] == 'SUCCESS'){
            //第一步 检测库存量 (待开发)
            //-----------------------
            //订单号
            $orderNo = $data['out_trade_no'];
            Db::startTrans();
            try{
                //通过订单号查询外部订单信息(订单总金额)
                $out_order_info = Db::table('cy_main_orders')->field('mem_id,bis_id,total_amount,with_balance_amount')->where('order_no = '.$orderNo)->find();
                $openid = $out_order_info['mem_id'];
                $bisId = $out_order_info['bis_id'];
                $with_balance_amount = $out_order_info['with_balance_amount'];
                //添加积分
                $this->addJifen($bisId,$openid,$out_order_info['total_amount'],$orderNo,$with_balance_amount);

                //比较微信返回的总金额和外部订单表内的总金额
                if($out_order_info['total_amount'] * 100 == $data['total_fee']){
                    //更改外部订单状态,记录流水号
                    $order_status['order_status'] = 2;
                    $order_status['transaction_id'] = $data['transaction_id'];
                    $order_status['update_time'] = date('Y-m-d H:i:s');
                    $out_order_info = Db::table('cy_main_orders')->where('order_no = '.$orderNo)->update($order_status);
                }
                Db::commit();
                return true;
            }catch(Exception $ex){
                Log::error($ex);
                Db::rollback();
                return false;
            }
        }else{
            return true;
        }
    }

    //会员增加积分
    public function addJifen($bisId,$openid,$total_amount,$orderNo,$with_balance_amount){
        //获取积分比例
        $bisInfo = Db::table('cy_bis')->where('id = '.$bisId)->find();
        $jifen_ratio = $bisInfo['jifen_ratio'];
        if($jifen_ratio > 0){
            //可获得积分
            $jifen = floor($total_amount / $jifen_ratio);
            if($jifen > 0){
                //更新会员积分
                Db::table('cy_members')->where("mem_id = '$openid'")->setInc('jifen',$jifen);
                //生成积分明细
                $this->createJifenDetail($openid,$jifen,$orderNo);
            }
            //更新会员余额
            Db::table('cy_members')->where("mem_id = '$openid'")->setDec('balance',$with_balance_amount);
        }

        return true;
    }

    //生成积分明细
    public function createJifenDetail($openid,$jifen,$order_no){
        //生成积分明细记录
        $jf_data = [
            'mem_id'  => $openid,
            'changed_jifen'  => $jifen,
            'type'  => 1,
            'remark'  => $order_no,
            'create_time'  => date('Y-m-d H:i:s'),
        ];
        Db::table('cy_jifen_detailed')->insert($jf_data);
    }
}