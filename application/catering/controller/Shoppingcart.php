<?php
namespace app\catering\controller;
use think\Controller;
use think\Db;

class Shoppingcart extends Controller{

    //添加积分商品到购物车(普通版)
    public function addJfProIntoCart(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->addJfProIntoCart($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'  => $res
        ));
        exit;
    }

    //获取购物车信息(单用户版)
    public function getShoppingCartInfo(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->getShoppingCartInfo($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res

        ));
        exit;
    }

    //更改单条信息选中状态
    public function updateSelectedStatus(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->updateSelectedStatus($param);
        echo json_encode(array(
            'statuscode'  => 1

        ));
        exit;
    }

    //获取选中的价格信息(单用户版)
    public function getSelectedTotalPrice(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->getSelectedTotalPrice($param);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res

        ));
        exit;
    }


    //更改全部选中状态(单用户版)
    public function updateAllSelectedStatus(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->updateAllSelectedStatus($param);
        echo json_encode(array(
            'statuscode'  => 1

        ));
        exit;
    }


    //更改单个商品选中数量((普通商城版))
    public function updateSelectedCount(){
        //获取参数
        $param = input('post.');
        $res = model('ShoppingCart')->updateSelectedCount($param);
        echo json_encode(array(
            'statuscode'  => 1
        ));
        exit;
    }


    //独立版--点击"去结算"返回选中的购物车内信息
    public function getSelectedCartInfo(){
        $wx_id = input('post.openid');
        $bis_id = input('post.bis_id');
        $res = model('ShoppingCart')->getSelectedCartInfo($wx_id,$bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //获取购物车内选中的积分商品
    public function getJfProSelectedInfo(){
        $openid = input('post.openid');
        $bis_id = input('post.bis_id');
        $res = model('ShoppingCart')->getJfProSelectedInfo($openid,$bis_id);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //判断购物车内是否存在勾选的产品
    public function checkSelectedPro(){
        $wx_id = input('post.openid');
        $cart_type = input('post.cart_type',1);
        $res = model('ShoppingCart')->checkSelectedPro($wx_id,$cart_type);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //根据id删除购物车
    public function deleteCartById(){
        $cart_id = input('post.cart_id');
        $data['status'] = -1;
        $res = Db::table('cy_shopping_carts')->where('id = '.$cart_id)->update($data);
        if($res){
            echo json_encode(array(
                'statuscode'  => 1
            ));
            exit;
        }else{
            echo json_encode(array(
                'statuscode'  => 0
            ));
            exit;
        }
    }

    //获取当前店铺的物流种类相关
    public function getTransportType(){
        //获取参数
        $bis_id = input('post.bis_id');
        $province = input('post.province');
        $res = model('ShoppingCart')->getTransportType($bis_id,$province);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //更改购物车指定店铺内商品选中状态
    public function updateBisStatus(){
        //获取参数
        $openid = input('post.openid');
        $bis_id = input('post.bis_id');
        $bis_status = input('post.bis_status');
        $res = model('ShoppingCart')->updateBisStatus($openid,$bis_id,$bis_status);
        echo json_encode(array(
            'statuscode'  => 1,
            'result'      => $res
        ));
        exit;
    }

    //判断积分是否足够
    public function checkJifenEnough(){
        //获取参数
        $openid = input('post.openid');
        $jifen_amount = input('post.jifen_amount');
        $where = "mem_id = '$openid'";
        $res = Db::table('cy_members')->field('jifen')->where($where)->find();

        if($res['jifen'] < $jifen_amount){
            echo json_encode(array(
                'statuscode'  => 1,
                'result'      => -1
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
}



