<?php
/**
 * User: Wangsx
 * Date: 2019/4/15
 */

namespace app\catering\controller;


use think\Controller;
use think\Db;
use think\Model;
class Zb extends Controller
{

    //查询我的推荐收入和提现中金额
    public function getMemberInfo(){
        $mem_id = input('post.mem_id');
        $where = "mem_id = '$mem_id'";
        $res = Db::table('cy_members')->field('jifen,balance,stop_time')
            ->where($where)
            ->find();

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }
    //获取会员信息
    public function getUserinfo()
    {
        $param = \input('post.');
        $res = model('Zb')->getUserinfo($param);
        if ($res)
        {
            echo json_encode([
                'statuscode' => 1,
                'result'=>$res
            ]);
            exit();
        }
    }
    //修改会员信息
    public function editUserinfo()
    {
        $param = \input('post.');
        $res = model('Zb')->editUserinfo($param);
        if ($res)
        {
            echo json_encode([
                'statuscode' => 1,
                'result'=>$res
            ]);
            exit();
        }
    }

}