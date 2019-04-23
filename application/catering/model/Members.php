<?php
namespace app\catering\model;
use think\Model;
use think\Db;
use app\api\service\CheckService;
use think\Exception;

class Members extends Model{
    //添加会员信息
    public function addMembers($param){
        //获取参数
        $mem_id = !empty($param['mem_id']) ? $param['mem_id'] : '';

        //查询会员表中是否存在此会员
        $where = "mem_id = '$mem_id'";
        $mem_res = Db::table('cy_members')->where($where)->select();
        if($mem_res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '该会员已存在!'
            ));
            exit;
        }

        //设置数据
        $data = [
            'mem_id'  => $mem_id,
            'username'  => $mem_id,
            'create_time'  => date('Y-m-d H:i:s')
        ];

        //添加数据
        $res = Db::table('cy_members')->insert($data);
        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加会员失败!'
            ));
            exit;
        }

        return $res;
    }

    //获取会员信息
    public function getMemberInfo($param){
        CheckService::checkEmpty($param['openid']);
        CheckService::checkEmpty($param['bis_id']);

        $where = "mem_id = '".$param['openid']."' and bis_id = ".$param['bis_id']." and status = 1";
        $res = Db::table('cy_members')->where($where)->find();

        if(empty($res)){
            throw new Exception('获取会员信息失败',-1);
        }
        return $res;
    }

    //更新余额
    public function subBalance($param){
        CheckService::checkEmpty($param['openid']);
        CheckService::checkEmpty($param['bis_id']);
        CheckService::checkEmpty($param['with_balance_amount']);

        Db::startTrans();
        try{
            $where = "mem_id = '".$param['openid']."' and bis_id = ".$param['bis_id'];
            $res = Db::table('cy_members')->where($where)->setDec('balance',$param['with_balance_amount']);

            //生成余额变动记录
            $balanceData = [
                'bis_id'  => $param['bis_id'],
                'openid'  => $param['openid'],
                'bis_type'  => 2,
                'amount'  => $param['with_balance_amount'],
                'type'  => 2,
                'recharge_status'  => 2,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];
            Db::table('store_member_recharge_records')->insert($balanceData);

            Db::commit();
        }catch (Exception $e){
            Db::rollback();
        }

        if(empty($res)){
            throw new Exception('更新会员信息失败',-1);
        }
        return $res;
    }


}