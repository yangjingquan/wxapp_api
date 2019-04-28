<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class Activity extends Controller{

    //获取活动信息
    public function getActivityInfo(){
        //获取参数
        $bis_id = input('post.bis_id');
        $total_amount = input('post.total_amount');
        $isNewMember = input('post.isNewMember');
        $res = model('Activity')->getActivityInfo($bis_id,$total_amount,$isNewMember = false);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }


}
