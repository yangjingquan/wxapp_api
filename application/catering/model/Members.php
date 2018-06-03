<?php
namespace app\catering\model;
use think\Model;
use think\Db;

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


}