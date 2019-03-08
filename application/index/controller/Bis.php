<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Bis extends Controller{

    //获取店铺列表
    public function getBisList(){
        //获取参数
        $cat_id = input('post.cat_id');
        $page = input('post.page',1,'intval');
        $limit = 10;
        $offset = $limit * ($page - 1);

        if($cat_id == 0){
            $where = "";
        }else{
            $where = "cat_id = $cat_id and";
        }

        $res = Db::table('store_bis')->field('id as bis_id,bis_name,thumb,is_pintuan')
            ->where($where.' status = 1')
            ->limit($offset,$limit)
            ->order('id desc')
            ->select();

        $count = count($res);
        if($count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        $index = 0;
        foreach($res as $val){
            $res[$index]['pro_info'] = $this->getProInfo($val['bis_id']);
            $index ++;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'has_more'    => $has_more
        ));
        exit;
    }

    //获取首页店铺列表
    public function getBisListInHome(){
        $res = Db::table('store_bis')->field('id as bis_id,bis_name,thumb,brand,citys,address')
            ->where('id !=34 and status = 1')
            ->limit(6)
            ->order('id desc')
            ->select();

        $index = 0;
        $bis_res = array();
        foreach($res as $val){
            $bis_res[$index]['bis_id'] = $val['bis_id'];
            $bis_res[$index]['bis_name'] = $val['bis_name'];
            $bis_res[$index]['thumb'] = $val['thumb'];
            $bis_res[$index]['brand'] = $val['brand'];
            $city_arr = explode(',',$val['citys']);
            $province_res = Db::table('store_province')->field('p_name')->where('id = '.$city_arr[0])->find();
            $province_name = $province_res['p_name'];
            $city_res = Db::table('store_city')->field('c_name')->where('id = '.$city_arr[1])->find();
            $city_name = $city_res['c_name'];
            $bis_res[$index]['bis_address'] = $province_name.$city_name.$val['address'];
            $index ++;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $bis_res
        ));
        exit;
    }

    //获取店铺最新商品信息(限制三条)
    public function getProInfo($bis_id){
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where('pro.bis_id = '.$bis_id.' and pro.on_sale = 1 and pro.status = 1 and i.status = 1 and pro.is_jf_product = 0')
            ->limit(3)
            ->order('pro.update_time desc')
            ->select();

        return $res;
    }

    //获取分类信息
    public function getFirstCatInfo(){
        $res = Db::table('store_category')->alias('cat')->field('cat.id as cat_id,cat.cat_name')
            ->where('cat.status = 1 and cat.parent_id = 0')
            ->order('cat.update_time desc')
            ->select();

        $all_arr = [
            'cat_id' => 0,
            'cat_name'  => '全部'
        ];

        array_unshift($res,$all_arr);

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //通过分类获取店铺列表
    public function getBisListByCat(){
        //获取参数
        $cat_id = input('post.cat_id');
        if($cat_id == 0){
            $where = "";
        }else{
            $where = "cat_id = $cat_id and";
        }
        $res = Db::table('store_bis')->field('id as bis_id,bis_name')
            ->where($where.' status = 1')
            ->order('id desc')
            ->select();

        $index = 0;
        foreach($res as $val){
            $res[$index]['pro_info'] = $this->getProInfo($val['bis_id']);
            $index ++;
        }

        $ind = 0;
        foreach($res as $val){
            if(count($res[$ind]['pro_info']) == 0){
                unset($res[$ind]);
            }

            $ind ++;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取banner列表
    public function getBannersInfo(){
        $res = Db::table('store_banners')->where('status = 1')->order('listorder desc')->select();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取单个店铺信息
    public function getBisInfoByBisId(){
        //获取参数
        $bis_id = input('post.bis_id');
        $res = Db::table('store_bis')->field('id as bis_id,bis_name')->where('id = '.$bis_id)->find();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //通过pro_id获取bis_id
    public function getBisIDByProId(){
        //获取参数
        $pro_id = input('post.pro_id');
        $res = Db::table('store_products')->alias('pro')->field('pro.bis_id,bis.is_pay')
            ->join('store_bis bis','pro.bis_id = bis.id','left')
            ->where('pro.id = '.$pro_id)
            ->find();

        $bis_id = $res['bis_id'];
        $is_pay = $res['is_pay'];
        echo json_encode(array(
            'statuscode'  => 1,
            'bis_id'      => $bis_id,
            'is_pay'      => $is_pay
        ));
        exit;
    }

    //通过bis_id判断店铺类型(普通/拼团)
    public function getBisTypeByBisId(){
        //获取参数
        $bis_id = input('post.bis_id');
        $res = Db::table('store_bis')->field('is_pintuan')->where('id = '.$bis_id)->find();
        $is_pintuan = $res['is_pintuan'];
        echo json_encode(array(
            'statuscode'  => 1,
            'is_pintuan' => $is_pintuan
        ));
        exit;
    }

    //小程序内商家注册
    public function xcx_register(){
        //接收参数
        $param = input('post.');

        //验证用户名唯一性
        $username = $param['username'];
        $user_res = Db::table('store_bis_admin_users')->where("username = '$username' and status != -1")->select();
        if($user_res){
            echo json_encode(array(
                'scode'  => 0,
                'message'     => '该用户已存在!'
            ));
            exit;
        }

        $region = $param['region'];
        $p_name = $region[0];
        $c_name = $region[1];
        $a_name = $region[2];

        $p_res = $this->getPInfo($p_name);
        $p_id = $p_res['id'];
        $c_res = $this->getCInfo($p_id,$c_name);

        $c_id = $c_res['id'];

        //设置商家表数据
        $data = [
            'bis_name' => $param['bis_name'],
            'brand' => $param['brand'],
            'leader' => $param['leader'],
            'citys' => $p_id.','.$c_id,
            'address' => $a_name.$param['address'],
            'link_tel' => $param['link_tel'],
            'link_mobile' => $param['link_mobile'],
            'email' => $param['email'],
            'create_time' => date('Y-m-d H:i:s'),
        ];

        if($param['bis_type'] == 0){
            $res = Db::table('store_bis')->insertGetId($data);
        }else{
            $res = Db::table('cy_bis')->insertGetId($data);
        }


        if($res){
            $con = [
                'bis_id'   => $res,
                'username'   => $param['username'],
                'password'   => md5($param['password']),
                'create_time'   => date('Y-m-d H:i:s')
            ];
            if($param['bis_type'] == 0){
                $user_info = Db::table('store_bis_admin_users')->insert($con);
            }else{
                $user_info = Db::table('cy_bis_admin_users')->insert($con);
            }

            if($user_info){
                echo json_encode(array(
                    'scode'  => 1,
                    'message'     => '注册成功!'
                ));
                exit;
            }else{
                echo json_encode(array(
                    'scode'  => 0,
                    'message'     => '注册失败!'
                ));
                exit;
            }
        }else{
            echo json_encode(array(
                'scode'  => 0,
                'message'     => '注册失败!'
            ));
            exit;
        }
    }

    //获取省级信息id
    public function getPInfo($p_name){

        $where = "p_name like '%$p_name%' and status = 1";
        $res = Db::table('store_province')->field('id')->where($where)->find();

        return $res;
    }

    //获取市级信息id
    public function getCInfo($p_id,$c_name){
        $where = "c_name like '%$c_name%' and parent_id = $p_id and status = 1";
        $res = Db::table('store_city')->field('id')->where($where)->find();

        return $res;
    }

    //获取商家分类信息
    public function getCategotyList(){
        $cat_res = Db::table('store_category')->field('id as cat_id,cat_name')->where('status = 1 and parent_id = 0')->select();
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $cat_res
        ));
        exit;
    }

    //获取我的团队成员信息
    public function getMyTeamList(){
        //接收参数
        $openid = input('post.openid');
        $page = input('post.page',1,'intval');

        $limit = 10;
        $offset = $limit * ($page - 1);

        $where = "mem_id = '$openid' and status = 1";
        $res = Db::table('store_members')->field('id as team_mem_id')
            ->where($where)
            ->find();

        $team_mem_id = $res['team_mem_id'];

        $team_res = Db::table('store_members')->field('id as mem_id,nickname,avatarUrl')
            ->where('rec_id = '.$team_mem_id)
            ->limit($offset,$limit)
            ->order('id desc')
            ->select();

        $total_count = Db::table('store_members')->field('id as mem_id,nickname,avatarUrl')
            ->where('rec_id = '.$team_mem_id)
            ->count();

        $count = count($team_res);
        if($count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $team_res,
            'has_more'    => $has_more,
            'total_count'    => $total_count
        ));
        exit;
    }

    //查询佣金订单
    public function getRecOrders(){
        $mem_id = input('post.mem_id');
        $page = input('post.page',1,'intval');

        $limit = 10;
        $offset = ($page - 1) * $limit;

        //查询佣金订单
        $res = Db::table('store_rec_orders')
            ->where('rec_id = '.$mem_id)
            ->order('create_time desc')
            ->limit($offset,$limit)
            ->select();

        //查询佣金订单数量
        $count = Db::table('store_rec_orders')
            ->where('rec_id = '.$mem_id)
            ->count();

        //查询佣金订单收入
        $rec_amount = Db::table('store_rec_orders')
            ->where('rec_id = '.$mem_id)
            ->SUM('rec_amount');

        $single_count = count($res);
        if($single_count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'count'       => $count,
            'result'      => $res,
            'rec_amount'  => $rec_amount,
            'has_more'    => $has_more
        ));
        exit;
    }

    //付款成功后添加积分(多用户版普通商城支付成功后)
    public function editJifenOrg(){
        //接收参数
        $order_id = input('post.order_id');
        $openid = input('post.openid');
        $bis_id = input('post.bis_id');
        //查询该订单产生的积分
        $jifen = Db::table('store_sub_orders')->alias('sub')->field('pro.id as pro_id')
            ->join('store_pro_config con','sub.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where('sub.main_id='.$order_id)
            ->SUM('pro.jifen');

        //获取订单号
        $order_res = Db::table('store_main_orders')->alias('main')->field('main.order_no')
            ->where('main.id='.$order_id)
            ->find();

        //更新会员积分
        $mem_where = "mem_id = '$openid' and status = 1";
        $mem_res = Db::table('store_members')->field('jifen')->where($mem_where)->find();
        $mem_jifen = $mem_res['jifen'];
        $new_mem_jifen['jifen'] = $mem_jifen + $jifen * 5;
        $new_mem_res = Db::table('store_members')->where($mem_where)->update($new_mem_jifen);

        //更新商家积分
        $bis_where = "id = $bis_id and status = 1";
        $bis_res = Db::table('store_bis')->field('jifen')->where($bis_where)->find();
        $bis_jifen = $bis_res['jifen'];
        $new_bis_jifen['jifen'] = $bis_jifen + $jifen * 2;
        $new_bis_res = Db::table('store_bis')->where($bis_where)->update($new_bis_jifen);

        //生成积分明细记录
        $jf_data = [
            'mem_id'  => $openid,
            'changed_jifen'  => $jifen * 5,
            'type'  => 1,
            'remark'  => $order_res['order_no'],
            'create_time'  => date('Y-m-d H:i:s'),
        ];
        $ji_res = Db::table('store_jifen_detailed')->insert($jf_data);

        echo json_encode(array(
            'statuscode'  => 1,
            'message'     => '添加成功!'
        ));
        exit;
    }

    //付款成功后添加积分(多用户版拼团商城支付成功后)
    public function editJifenGroup(){
        //接收参数
        $order_id = input('post.order_id');
        $openid = input('post.openid');
        $bis_id = input('post.bis_id');
        //查询该订单产生的积分
        $jifen = Db::table('store_group_main_orders')->alias('main')->field('pro.id as pro_id')
            ->join('store_products pro','main.pro_id = pro.id','LEFT')
            ->where('main.id='.$order_id)
            ->SUM('pro.jifen');

        //更新会员积分
        $mem_where = "mem_id = '$openid' and status = 1";
        $mem_res = Db::table('store_members')->field('jifen')->where($mem_where)->find();
        $mem_jifen = $mem_res['jifen'];
        $new_mem_jifen['jifen'] = $mem_jifen + $jifen * 5;
        $new_mem_res = Db::table('store_members')->where($mem_where)->update($new_mem_jifen);

        //更新商家积分
        $bis_where = "id = $bis_id and status = 1";
        $bis_res = Db::table('store_bis')->field('jifen')->where($bis_where)->find();
        $bis_jifen = $bis_res['jifen'];
        $new_bis_jifen['jifen'] = $bis_jifen + $jifen * 2;
        $new_bis_res = Db::table('store_bis')->where($bis_where)->update($new_bis_jifen);

        echo json_encode(array(
            'statuscode'  => 1,
            'message'     => '添加成功!'
        ));
        exit;
    }

    //积分兑换购买商品时付款成功后减去积分(多用户版普通商城支付成功后)
    public function subJifenOrg(){
        //接收参数
        $order_id = input('post.order_id');
        $openid = input('post.openid');
        $bis_id = input('post.bis_id');

        //查询该订单产生的积分
        $order_res = Db::table('store_main_orders')->alias('main')->field('main.jifen,main.order_no')
            ->where('main.id='.$order_id)
            ->find();
        $jifen = $order_res['jifen'];

        //更新会员积分
        $mem_where = "mem_id = '$openid' and status = 1";
        $mem_res = Db::table('store_members')->field('jifen')->where($mem_where)->find();
        $mem_jifen = $mem_res['jifen'];
        $new_mem_jifen['jifen'] = $mem_jifen - $jifen;
        $new_mem_res = Db::table('store_members')->where($mem_where)->update($new_mem_jifen);

        //生成积分明细记录
        $jf_data = [
            'mem_id'  => $openid,
            'changed_jifen'  => $jifen,
            'type'  => 2,
            'remark'  => $order_res['order_no'],
            'create_time'  => date('Y-m-d H:i:s'),
        ];
        $ji_res = Db::table('store_jifen_detailed')->insert($jf_data);

        echo json_encode(array(
            'statuscode'  => 1,
            'message'     => '添加成功!'
        ));
        exit;
    }

    //获取个人积分
    public function getPersonJifen(){
        //接收参数
        $openid = input('post.openid');
        $where = "mem_id = '$openid' and status = 1";
        $res = Db::table('store_members')->field('jifen')->where($where)->find();
        $jifen = $res['jifen'];

        echo json_encode(array(
            'statuscode'  => 1,
            'result'     => $jifen
        ));
        exit;
    }

    //积分明细
    public function getJifenDetailed(){
        //接收参数
        $openid = input('post.openid');
        $page = input('post.page',1,'intval');
        $limit = 10;
        $offset = $limit * ($page - 1);

        $where1 = "mem_id = '$openid' and status = 1";
        $jf_res = Db::table('store_members')->field('jifen')->where($where1)->find();
        $jifen = $jf_res['jifen'];

        $where = "mem_id = '$openid' and status = 1";
        $res = Db::table('store_jifen_detailed')
            ->where($where)
            ->limit($offset,$limit)
            ->order('create_time desc')
            ->select();

        $ind = 0;
        foreach($res as $val){
            $res[$ind]['changed_jifen'] = floor($val['changed_jifen']);
            $ind ++;
        }

        $count = count($res);
        if($count < $limit){
            $has_more = false;
        }else{
            $has_more = true;
        }

        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res,
            'jifen'      => $jifen,
            'has_more'    => $has_more
        ));
        exit;
    }

    public function testt(){
        echo phpinfo();
    }
}



