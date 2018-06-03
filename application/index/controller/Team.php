<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Team extends Controller {

    //获取组织详细信息
    public function getDetail(){
        $openid = input('post.openid');

        $where = "mem_id = '$openid'";
        $mem_res = Db::table('store_members')->field('id')->where($where)->find();

        $id = $mem_res['id'];

        $res = Db::table('store_teams')->alias('t')->field('t.id as t_id,t.a,mem1.nickname as a_name,t.b1,mem2.nickname as b1_name,t.b2,mem3.nickname as b2_name,t.c1,mem4.nickname as c1_name,t.c2,mem5.nickname as c2_name,t.c3,mem6.nickname as c3_name,t.c4,mem7.nickname as c4_name,mem8.nickname as rec_name')
            ->join('store_members mem1','mem1.id = t.a','LEFT')
            ->join('store_members mem2','mem2.id = t.b1','LEFT')
            ->join('store_members mem3','mem3.id = t.b2','LEFT')
            ->join('store_members mem4','mem4.id = t.c1','LEFT')
            ->join('store_members mem5','mem5.id = t.c2','LEFT')
            ->join('store_members mem6','mem6.id = t.c3','LEFT')
            ->join('store_members mem7','mem7.id = t.c4','LEFT')
            ->join('store_members mem8','mem8.id = t.rec_id','LEFT')
            ->where('t.a = '.$id.' and t.status = 0')
            ->order('t.create_time desc')
            ->find();

        $res1 = [
            't_id'  => $res['t_id'],
            'a'  => $res['a'],
            'a_name'  => $res['a_name'],
            'b1'  => $res['b1'] ? $res['b1'] : '--',
            'b1_name'  => $res['b1_name'] ? $res['b1_name'] : '--',
            'b2'  => $res['b2'] ? $res['b2'] : '--',
            'b2_name'  => $res['b2_name'] ? $res['b2_name'] : '--',
            'c1'  => $res['c1'] ? $res['c1'] : '--',
            'c2'  => $res['c2'] ? $res['c2'] : '--',
            'c3'  => $res['c3'] ? $res['c3'] : '--',
            'c4'  => $res['c4'] ? $res['c4'] : '--',
            'c1_name'  => $res['c1_name'] ? $res['c1_name'] : '--',
            'c2_name'  => $res['c2_name'] ? $res['c2_name'] : '--',
            'c3_name'  => $res['c3_name'] ? $res['c3_name'] : '--',
            'c4_name'  => $res['c4_name'] ? $res['c4_name'] : '--'
        ];

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res1
        ));
        exit;
    }

    //监测该会员是否加入营销体系
    public function checkUser(){
        $openid = input('post.openid');
        $where = "mem_id = '$openid'";
        $mem_res = Db::table('store_members')->field('team_status')->where($where)->find();

        if($mem_res['team_status'] == 0){
            echo json_encode(array(
                'statuscode'  => 1,
                'result'      => 0
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 1,
                'result'      => 1
            ));
            exit;
        }
    }

    //获取指定会员为首的团队信息
    public function getTeamInfo(){
        $mem_id = input('post.mem_id');

        $res1 = Db::table('store_teams')->field('a,b1,b2,c1,c2,c3,c4')->where('a = '.$mem_id.' and status = 0')->order('create_time desc')->select();
        $res2 = Db::table('store_teams')->field('a,b1,b2,c1,c2,c3,c4')->where('a = '.$mem_id.' and status = 1')->order('create_time desc')->select();

        $index1 = 0;
        $index2 = 0;
        foreach($res1 as $val){
            $res1[$index1]['b1'] = $val['b1'] ? $val['b1'] : '--';
            $res1[$index1]['b2'] = $val['b2'] ? $val['b2'] : '--';
            $res1[$index1]['c1'] = $val['c1'] ? $val['c1'] : '--';
            $res1[$index1]['c2'] = $val['c2'] ? $val['c2'] : '--';
            $res1[$index1]['c3'] = $val['c3'] ? $val['c3'] : '--';
            $res1[$index1]['c4'] = $val['c4'] ? $val['c4'] : '--';
            $index1 ++;
        }

        foreach($res2 as $val){
            $res2[$index1]['b1'] = $val['b1'] ? $val['b1'] : '--';
            $res2[$index1]['b2'] = $val['b2'] ? $val['b2'] : '--';
            $res2[$index1]['c1'] = $val['c1'] ? $val['c1'] : '--';
            $res2[$index1]['c2'] = $val['c2'] ? $val['c2'] : '--';
            $res2[$index1]['c3'] = $val['c3'] ? $val['c3'] : '--';
            $res2[$index1]['c4'] = $val['c4'] ? $val['c4'] : '--';
            $index2 ++;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result1'      => $res1,
            'result2'      => $res2
        ));
        exit;
    }

}
