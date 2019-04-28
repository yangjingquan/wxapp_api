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

class CateringMallWxNotify extends \WxPayNotify{

    public function NotifyProcess($data, &$msg){
        if($data['result_code'] == 'SUCCESS'){
            //第一步 检测库存量 (待开发)
            //-----------------------
            //订单号
            $orderNo = $data['out_trade_no'];
            Db::startTrans();
            try{
                $outMainOrderInfo = $this->getMainOrderInfo($orderNo);

                $mainId = $outMainOrderInfo['id'];
                $totalAmount = $outMainOrderInfo['total_amount'];
                $openId = $outMainOrderInfo['mem_id'];
                $orderType = $outMainOrderInfo['order_type'];

                if($orderType == 1){
                    //普通商品购买后，增加对应积分
                    $this->addJifen($mainId,$orderNo,$openId);
                }else{
                    //积分商城购买后减去订单产生的积分
                    $this->subJifen($mainId,$orderNo,$openId);
                }

                //减去订单产生的积分
                $this->subJifen($mainId,$orderNo,$openId);

                //副订单表数据
                $outSubOrderInfo = Db::table('cy_mall_sub_orders')->alias('sub')->field('sub.id as subId,pro.id as proId,pro.supply_pro_id')
                    ->join('cy_mall_pro_config con','sub.pro_id = con.id','left')
                    ->join('cy_mall_products pro','con.pro_id = pro.id','left')
                    ->where('sub.main_id = '.$mainId)
                    ->select();

                //验证订单内是否存在供货商品
                $supplyProExist = $this->checkIsSupplyProExist($outSubOrderInfo);

                //存在供货商品
                if($supplyProExist){
                    //校验订单内是否全都是供货商品
                    $checkAllSupplyPro = $this->checkIsAllSupplyPro($outSubOrderInfo);
                    //如果全是供货商品，更新主订单信息，把is_supply_order字段置为1
                    if($checkAllSupplyPro['isAllSupplyPro']){
                        $res = $this->updateMainOrder($orderNo,$data,$totalAmount,1);
                    }else{
                        //如果不全是供货商品,把供货商品找出来，重新生成一条主订单作为供货订单，订单号为当前主订单号+'_1'后缀，
                        //并绑定供货订单与副订单表的供货商品所在记录
                        $subIdsArr = $checkAllSupplyPro['subIdsArr'];
                        //创建一条新的主订单,并更新副订单表
                        $newOrderNo = $this->createMainOrder($outMainOrderInfo,$subIdsArr);
                        //更新新旧主订单信息
                        $oldOrderRes = $this->updateMainOrder($orderNo,$data,$totalAmount);
                        $newOrderRes = $this->updateMainOrder($newOrderNo,$data,$totalAmount,1);
                    }
                }else{
                    //直接更新主订单
                    $res = $this->updateMainOrder($orderNo,$data,$totalAmount);
                }
                Db::commit();
                return true;
            }catch(Exception $ex){
                Db::rollback();
                Log::error($ex);
                return false;
            }
        }else{
            return true;
        }
    }

    //查询当前主订单表数据
    public function getMainOrderInfo($orderNo){
        $outMainOrderInfo = Db::table('cy_mall_main_orders')->where("order_no = '$orderNo'")->find();
        return $outMainOrderInfo;
    }

    //验证是否存在供货商品
    public function checkIsSupplyProExist($outSubOrderInfo){
        $isExist = false;
        foreach ($outSubOrderInfo as $item){
            if(!empty($item['supply_pro_id'])){
                $isExist = true;
                break;
            }
        }
        return $isExist;
    }

    //验证订单内是否全部是供货商品，若不是，把供货商品找出来
    public function checkIsAllSupplyPro($outSubOrderInfo){
        $isAllSupplyPro = true;
        $subIdsArr = array();
        foreach ($outSubOrderInfo as $item){
            if(empty($item['supply_pro_id'])){
                $isAllSupplyPro = false;
            }else{
                array_push($subIdsArr,$item['subId']);
            }
        }
        return array(
            'isAllSupplyPro'  => $isAllSupplyPro,
            'subIdsArr'  => $subIdsArr
        );
    }

    //更新主订单表
    public function updateMainOrder($orderNo,$data,$totalAmount,$isSupplyOrder = 0){
        if($totalAmount * 100 == $data['total_fee']){
            //更改外部订单状态,记录流水号
            $mainOrderData = [
                'order_status' => 3,
                'transaction_id' => $data['transaction_id'],
                'update_time' => date('Y-m-d H:i:s'),
                'pay_time' => date('Y-m-d H:i:s'),
                'is_supply_order' => $isSupplyOrder
            ];
            Db::table('cy_mall_main_orders')->where("order_no = '$orderNo'")->update($mainOrderData);
        }
        return true;
    }

    //创建一条新的主订单，并更新副订单表
    public function createMainOrder($outMainOrderInfo,$subIdsArr){
        //修改个别字段
        $outMainOrderInfo['order_no'] = $outMainOrderInfo['order_no'] . '_1';
        $outMainOrderInfo['create_time'] = date('Y-m-d H:i:s');
        $outMainOrderInfo['update_time'] = date('Y-m-d H:i:s');
        $outMainOrderInfo['is_supply_order'] = 1;
        unset($outMainOrderInfo['id']);
        $newMainId = Db::table('cy_mall_main_orders')->insertGetId($outMainOrderInfo);

        //更新副订单表
        $this->updateSubOrderData($newMainId,$subIdsArr);
        return $outMainOrderInfo['order_no'];
    }

    //更新副订单表
    public function updateSubOrderData($newMainId,$subIdsArr){
        $data['main_id'] = $newMainId;
        $subIds = implode(',',$subIdsArr);
        $where = "id in ('".$subIds."')";
        $res = Db::table('cy_mall_sub_orders')->where($where)->update($data);
        return $res;
    }

    //积分商城支付后，减去对应的积分
    public function subJifen($order_id,$order_no,$openid){
        //查询该订单产生的积分
        $jifen = Db::table('cy_mall_sub_orders')->alias('sub')->field('pro.id as pro_id')
            ->join('cy_mall_pro_config con','sub.pro_id = con.id','LEFT')
            ->where('sub.main_id='.$order_id)
            ->SUM('con.ex_jifen * sub.count');

        //更新会员积分
        $mem_where = "mem_id = '$openid' and status = 1";
        Db::table('cy_members')->where($mem_where)->setDec('jifen',$jifen);

        //生成积分明细记录
        $jf_data = [
            'mem_id'  => $openid,
            'changed_jifen'  => $jifen,
            'type'  => 2,
            'remark'  => $order_no,
            'create_time'  => date('Y-m-d H:i:s'),
        ];
        Db::table('cy_jifen_detailed')->insert($jf_data);
    }

    //付款成功后添加积分
    public function addJifen($order_id,$order_no,$openid){
        //查询该订单产生的积分
        $jifen = Db::table('cy_mall_sub_orders')->alias('sub')->field('pro.id as pro_id')
            ->join('cy_mall_pro_config con','sub.pro_id = con.id','LEFT')
            ->join('cy_mall_products pro','con.pro_id = pro.id','LEFT')
            ->where('sub.main_id='.$order_id)
            ->SUM('pro.jifen * sub.count');

        //更新会员积分
        $mem_where = "mem_id = '$openid' and status = 1";
        Db::table('cy_members')->where($mem_where)->setInc('jifen',$jifen);

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