<?php
/**
 * User: wsx
 * Date: 2019/3/27
 */

namespace app\market\controller;


use think\Controller;
use think\Db;

class Dzpcy extends Controller
{

    //获取大转盘信息
    public function index()
    {
        $bis_id = input('get.bis_id');
        $openid = input('get.openid');
        $where = "mem_id = '$openid' and bis_id= '$bis_id' ";
        $res1 = Db::table('cy_members')->field('id')
            ->where($where)
            ->find();


        $id = $res1['id'];

        $res = Db::table('cy_members')
            ->field('id,bis_id,mem_id,nickname,dzp_ci')
            ->where(['id'=>$id,'bis_id'=>$bis_id])
            ->select();

        $res_jp = Db::table('cy_dzp_jiangpin')
            ->field('id,jp_id,jp_name,jiaodu')
            ->where(['status'=>1,'bis_id'=>$bis_id])
            ->select();

         $res_bis = Db::table('cy_bis')
            ->field('dzp')
            ->where(['id'=>$bis_id])
            ->select();

        return view('index',['res'=>$res,'res_jp'=>$res_jp,'res_bis'=>$res_bis]);

    }


    //提交奖品信息
    public function save()
    {

        $bis_id = input('get.bis_id');
        $id = input('get.id');
        $dzp_ci = input('get.dzp_ci');
        $jiangpin = input('get.jiangpin');

        $data = [
            'dzp_ci' => $dzp_ci,
            'jiangpin' => $jiangpin
        ];

        //查询会员信息
        $where1 = ['id'=>$id];
        $nick_u = Db::table('cy_members')->field('nickname,mem_id')->where($where1)->find();
        $mem_id = $nick_u['mem_id'];

        //查询奖品信息
        $where2 = ['jp_id'=>$jiangpin];
        $jp_u = Db::table('cy_dzp_jiangpin')->field('jp_name')->where($where2)->find();


        $dat = [
            'userid' => $id,
            'bis_id' => $bis_id,
            'jiangpin_id' => $jiangpin,
            'nickname' => $nick_u['nickname'],
            'jiangpin_name' => $jp_u['jp_name'],
            'c_time'=>date('Y-m-d H:i:s',time())
        ];
           
            $where = ['id'=>$id];
            $res = Db::table('cy_members')->where($where)->update($data);

            Db::table('cy_dzp_user_jiangpin')->insert($dat);


            if ($res) {
                return redirect("index",array("openid"=>$mem_id,"bis_id"=>$bis_id,));
            } else {
                $this->error('系统失败');
            }

    }

}