<?php
namespace app\index\model;
use app\api\service\CheckService;
use think\Exception;
use think\Model;
use think\Db;

class Members extends Model{

    //验证该用户是否已被推荐,若未被推荐,更新推荐用户到成员表中
    public function checkRecStatus($param){

        //获取参数
        $rec_id = !empty($param['rec_id']) ? $param['rec_id'] : '';
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        
        //验证是否已被推荐
        $where = "mem_id = '$openid'  and status = 1";
        $check_res = Db::table('store_members')
                ->field('id,rec_id,create_time')
                ->where($where)
                ->find();

        $now = date('Y-m-d H:i:s');
        
        //不是扫码进入
        if($rec_id == ''){
            //会员不存在,生成会员
            if(!$check_res){
                $in_data['mem_id'] = $openid;
                $in_data['username'] = $openid;
                $in_data['truename'] = $openid;
                $in_data['rec_id'] = 1;
                $in_data['create_time'] = $now;
                $in_data['bis_id'] = $bis_id;
                $insert_res = Db::table('store_members')->insert($in_data);

                //返回推荐id为1
                echo json_encode(array(
                    'statuscode'  => 1,
                    'result'      => 1
                )); 
                exit;
                //会员存在
            }else{
                //返回推荐id
                echo json_encode(array(
                    'statuscode'  => 1,
                    'result'      => $check_res['rec_id']
                ));
                exit;
            }
            //扫码进入
        }else{
            //会员不存在,生成会员
            if(!$check_res){
                $in_data['mem_id'] = $openid;
                $in_data['username'] = $openid;
                $in_data['truename'] = $openid;
                $in_data['rec_id'] = $rec_id;
                $in_data['create_time'] = $now;
                $in_data['bis_id'] = $bis_id;
                $insert_res = Db::table('store_members')->insert($in_data);

                //返回推荐id
                echo json_encode(array(
                    'statuscode'  => 1,
                    'result'      => $rec_id
                ));
                exit;

                //会员存在
            }else{
                $old_time = $check_res['create_time'];
                if(strtotime($now) - strtotime($old_time) < 20){
                    $up_data['rec_id'] = $rec_id;
                    $update_res = Db::table('store_members')->where($where)->update($up_data);

                    //返回推荐id
                    echo json_encode(array(
                        'statuscode'  => 1,
                        'result'      => $rec_id
                    ));
                    exit;
                }else{
                    //返回推荐id
                    echo json_encode(array(
                        'statuscode'  => 1,
                        'result'      => $check_res['rec_id']
                    ));
                    exit;
                }
            }
        }

    }

    //验证该用户是否已被推荐,若未被推荐,更新推荐用户到成员表中
    public function checkRecStatusMulti($param){
        //获取参数
        $rec_id = !empty($param['rec_id']) ? $param['rec_id'] : '';
        $openid = !empty($param['openid']) ? $param['openid'] : '';

        $now = date('Y-m-d H:i:s');

        //验证会员是否存在
        $where = "mem_id = '$openid' and status = 1";
        $check_res = Db::table('store_members')
            ->field('id,rec_id,create_time')
            ->where($where)
            ->find();

        //不是扫码进入
        if($rec_id == ''){
            //会员不存在,生成会员
            if(!$check_res){
                $in_data['mem_id'] = $openid;
                $in_data['username'] = $openid;
                $in_data['truename'] = $openid;
                $in_data['rec_id'] = 1;
                $in_data['create_time'] = $now;
                $insert_res = Db::table('store_members')->insert($in_data);

                //返回推荐id为1
                echo json_encode(array(
                    'statuscode'  => 1,
                    'result'      => 1
                ));
                exit;
                //会员存在
            }else{
                //返回推荐id
                echo json_encode(array(
                    'statuscode'  => 1,
                    'result'      => $check_res['rec_id']
                ));
                exit;
            }
            //扫码进入
        }else{
            //会员不存在,生成会员
            if(!$check_res){
                $in_data['mem_id'] = $openid;
                $in_data['username'] = $openid;
                $in_data['truename'] = $openid;
                $in_data['rec_id'] = $rec_id;
                $in_data['create_time'] = $now;
                $insert_res = Db::table('store_members')->insert($in_data);

                //返回推荐id
                echo json_encode(array(
                    'statuscode'  => 1,
                    'result'      => $rec_id
                ));
                exit;

                //会员存在
            }else{
                $old_time = $check_res['create_time'];
                if(strtotime($now) - strtotime($old_time) < 20){
                    $up_data['rec_id'] = $rec_id;
                    $update_res = Db::table('store_members')->where($where)->update($up_data);

                    //返回推荐id
                    echo json_encode(array(
                        'statuscode'  => 1,
                        'result'      => $rec_id
                    ));
                    exit;
                }else{
                    //返回推荐id
                    echo json_encode(array(
                        'statuscode'  => 1,
                        'result'      => $check_res['rec_id']
                    ));
                    exit;
                }
            }
        }
    }

    //获取会员信息
    public function getMemberInfo($param){
        CheckService::checkEmpty($param['openid']);
        CheckService::checkEmpty($param['bis_id']);

        $where = "mem_id = '".$param['openid']."' and bis_id = ".$param['bis_id']." and status = 1";
        $res = Db::table('store_members')->where($where)->find();

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

        $where = "mem_id = '".$param['openid']."' and bis_id = ".$param['bis_id'];
        $res = Db::table('store_members')->where($where)->setDec('balance',$param['with_balance_amount']);

        if(empty($res)){
            throw new Exception('更新会员信息失败',-1);
        }
        return $res;
    }
}