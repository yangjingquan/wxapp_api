<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Announcement extends Controller{

    //获取平台公告(前5条)
    public function getHomePreManyAnnouncement(){
        $res = Db::table('store_home_announcement')->where('status = 1')->limit(5)->order('create_time desc')->select();

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取店铺公告(前5条)
    public function getPreManyAnnouncement(){
        $bis_id = input('post.bis_id');
        $res = Db::table('store_announcement')->where('bis_id = '.$bis_id.' and status = 1')->limit(5)->order('create_time desc')->select();

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取平台公告(分页显示)
    public function getHomePreAnnouncement(){
        $page = input('post.page',1,'intval');
        $limit = 10;
        $offset = $limit * ($page - 1);
        $res = Db::table('store_home_announcement')
            ->where('status = 1')
            ->limit($offset,$limit)
            ->order('create_time desc')
            ->select();

        $count = count($res);

        if($count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more
        ));
        exit;
    }

    //获取平台公告(分页显示)
    public function getPreAnnouncement(){
        $page = input('post.page',1,'intval');
        $bis_id = input('post.bis_id');
        $limit = 10;
        $offset = $limit * ($page - 1);
        $res = Db::table('store_announcement')
            ->where('bis_id = '.$bis_id.' and status = 1')
            ->limit($offset,$limit)
            ->order('create_time desc')
            ->select();

        $count = count($res);

        if($count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more
        ));
        exit;
    }

    //公告详情
    public function getDetail(){
        //获取参数
        $a_id = input('post.id');
        $res = Db::table('store_announcement')->where('id = '.$a_id)->find();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //公告详情(平台)
    public function getHomeDetail(){
        //获取参数
        $a_id = input('post.id');
        $res = Db::table('store_home_announcement')->where('id = '.$a_id)->find();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }
}



