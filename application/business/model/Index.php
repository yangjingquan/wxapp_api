<?php
namespace app\business\model;
use think\Model;
use think\Db;

class Index extends Model{

    //获取新品列表(多用户版)
    public function getNewProInfoMut(){
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.original_price,pro.associator_price,pro.jifen * 5 as jifen')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where('bis_id !=34 and pro.on_sale = 1 and pro.status = 1 and i.status = 1 and pro.is_jf_product = 0')
            ->order('pro.create_time desc')
            ->limit(9)
            ->select();

        $ind = 0;
        foreach($res as $val){
            $jifen = $val['jifen'];
            $res[$ind]['jifen'] = floor($jifen);
            $ind ++;
        }

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'      => '暂无数据'
            ));
            exit;
        }

        return $res;
    }

    //获取推荐商城店铺
    public function getRecommendMallList(){
        $where = [
            'is_recommend'  => 1,
            'status'  => 1,
        ];
        $order = [
            'updated_time'  => 'desc'
        ];

        $res = Db::table('store_bis')->alias('bis')->field('id as bis_id,bis_name,thumb')
            ->where($where)
            ->order($order)
            ->limit(8)
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        return $res;
    }

    //获取推荐餐饮店铺
    public function getRecommendCatList(){
        $where = [
            'bis.is_recommend'  => 1,
            'bis.status'  => 1
        ];
        $order = [
            'bis.updated_time'  => 'desc'
        ];

        $res = Db::table('cy_bis')->alias('bis')->field('bis.id as bis_id,bis.bis_name,img.logo_image')
            ->join('cy_bis_images img','img.bis_id = bis.id','left')
            ->where($where)
            ->order($order)
            ->limit(8)
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        return $res;
    }

    public function getCatRecommendMallList(){
        $where = [
            'is_recommend'  => 1,
            'status'  => 1,
        ];
        $order = [
            'updated_time'  => 'desc'
        ];

        $res = Db::table('cy_bis')->alias('bis')->field('id as bis_id,bis_name,thumb')
            ->where($where)
            ->order($order)
            ->limit(8)
            ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        return $res;
    }
    //获取推荐商品列表(多用户版)
    public function getRecProInfo(){
        $res = Db::table('store_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.original_price,pro.associator_price,pro.jifen * 5 as jifen')
            ->join('store_pro_images i','pro.id = i.p_id','LEFT')
            ->where('pro.on_sale = 1 and pro.is_recommend = 1 and pro.status = 1 and i.status = 1 and pro.is_jf_product = 0')
            ->order('pro.update_time desc')
            ->limit(9)
            ->select();

        return $res;
    }

    public function getProvinceInfo($provinceId){
        $res = Db::table('store_province')->where("id='$provinceId'")->find();
        return $res['p_name'];
    }

    public function getCityInfo($cityId){
        $res = Db::table('store_city')->where("id='$cityId'")->find();
        $c_name = $res['c_name'];
        return $c_name;
    }

    //获取附近店铺
    public function getNearMallsInfo($curLocation,$limit,$offset){
        //获取商城附近店铺
        $mallRes = $this->getNearShopInfo($limit,$offset);
        //获取附近餐饮店铺
        $catRes = $this->getNearCatInfo($limit,$offset);

        if(!$mallRes && !$catRes){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        //整理商城店铺信息
        $index = 0;
        foreach($mallRes as $item){
            $positions = $item['positions'];
            $distanceJson = $this->execUrl($curLocation,$positions);
            $distanceArr = json_decode($distanceJson,true);
            $distance = $distanceArr['results'][0]['distance'];
            $mallRes[$index]['distance'] = $distance >= 1000 ? round(($distance / 1000),1).'km' : $distance.'m';
            $mallRes[$index]['type'] = 1;
            $index ++;
        }

        //整理餐饮店铺信息
        $ind = 0;
        foreach($catRes as $item){
            $location = $item['positions'];
            $locationJson = $this->execUrl($curLocation,$location);
            $locationArr = json_decode($locationJson,true);
            $distance = $locationArr['results'][0]['distance'];
            $catRes[$ind]['distance'] = $distance >= 1000 ? round(($distance / 1000),1).'km' : $distance.'m';
            $catRes[$ind]['type'] = 2;
            $ind ++;
        }

        $res = array_merge($mallRes,$catRes);

        return $res;
    }

    //计算两点间距离
    public function execUrl($curLocation,$positions){
        $url = "http://restapi.amap.com/v3/distance?key=4c9ea4c4b4f719f7e69d625586f8c00d&origins=".$curLocation."&destination=".$positions;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    //获取商城附近店铺
    public function getNearShopInfo($limit,$offset){
        $where = "bis.status = 1 and (bis.positions <> null or bis.positions <> '')";
        $order = [
            'bis.updated_time'  => 'desc'
        ];

        $mallRes = Db::table('store_bis')->alias('bis')->field('bis.id as bis_id,bis.bis_name,bis.citys,bis.address,bis.thumb,bis.positions,bis.brand')
            ->where($where)
            ->order($order)
            ->limit($offset,$limit)
            ->select();

        return $mallRes;
    }

    //获取商城餐饮店铺
    public function getNearCatInfo($limit,$offset){
        $where = "cbis.status = 1 and (cbis.positions <> null or cbis.positions <> '')";
        $order = [
            'cbis.updated_time'  => 'desc'
        ];

        $res = Db::table('cy_bis')->alias('cbis')->field("cbis.id as bis_id,cbis.bis_name,cbis.citys,cbis.address,img.logo_image as thumb,cbis.positions,'餐饮' as brand")
            ->join('cy_bis_images img','img.bis_id = cbis.id','left')
            ->where($where)
            ->order($order)
            ->limit($offset,$limit)
            ->select();

        return $res;
    }

    //获取附近餐饮店铺
    public function getNearCatList($curLocation,$limit,$offset){
        $where = "cbis.status = 1 and (cbis.positions <> null or cbis.positions <> '')";
        $order = [
            'cbis.updated_time'  => 'desc'
        ];

        $res = Db::table('cy_bis')->alias('cbis')->field("cbis.id as bis_id,cbis.bis_name,cbis.citys,cbis.address,img.logo_image as thumb,cbis.positions,'餐饮' as brand")
            ->join('cy_bis_images img','img.bis_id = cbis.id','left')
            ->where($where)
            ->order($order)
            ->limit($offset,$limit)
            ->select();

        //整理餐饮店铺信息
        $ind = 0;
        foreach($res as $item){
            $positions = $item['positions'];
            $locationJson = $this->execUrl($curLocation,$positions);
            $locationArr = json_decode($locationJson,true);
            $distance = $locationArr['results'][0]['distance'];
            $res[$ind]['distance'] = $distance >= 1000 ? round(($distance / 1000),1).'km' : $distance.'m';
            $ind ++;
        }

        return $res;
    }

    //获取商城附近店铺
    public function getNearShopList($curLocation,$limit,$offset){
        $where = "bis.status = 1 and (bis.positions <> null or bis.positions <> '')";
        $order = [
            'bis.updated_time'  => 'desc'
        ];

        $mallRes = Db::table('store_bis')->alias('bis')->field('bis.id as bis_id,bis.bis_name,bis.citys,bis.address,bis.thumb,bis.positions,bis.brand')
            ->where($where)
            ->order($order)
            ->limit($offset,$limit)
            ->select();

        //整理商城店铺信息
        $index = 0;
        foreach($mallRes as $item){
            $positions = $item['positions'];
            $distanceJson = $this->execUrl($curLocation,$positions);
            $distanceArr = json_decode($distanceJson,true);
            $distance = $distanceArr['results'][0]['distance'];
            $mallRes[$index]['distance'] = $distance >= 1000 ? round(($distance / 1000),1).'km' : $distance.'m';
            $index ++;
        }

        return $mallRes;
    }

    //添加餐饮会员信息
    public function addCatMembers($param){
        //获取参数
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        if(!$openid){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '缺少参数'
            ));
            exit;
        }

        //查询会员表中是否存在此会员
        $where = "mem_id = '$openid'";
        $mem_res = Db::table('cy_members')->where($where)->select();
        if($mem_res){
            //设置更新数据
            $up_data = [
                'avatarUrl'  => $param['avatarUrl'],
                'nickname'  => $param['nickname'],
                'username'  => $param['username'],
                'bis_id'  => $param['bis_id'],
            ];

            $up_where = "mem_id = '$openid'";
            Db::table('cy_members')->where($up_where)->update($up_data);
            echo json_encode(array(
                'statuscode'      => $mem_res,
                'message'     => '更新会员成功!'
            ));
            exit;
        }

        //设置数据
        $data = [
            'bis_id'  => $param['bis_id'],
            'mem_id'  => $openid,
            'avatarUrl'  => $param['avatarUrl'],
            'nickname'  => $param['nickname'],
            'username'  => $param['username'],
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

    //添加商城会员信息
    public function addMembers($param){
        //查询会员表中是否存在此会员
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        if(!$openid){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '缺少参数'
            ));
            exit;
        }
        $where = "mem_id = '$openid' and status = 1";
        $mem_res = Db::table('store_members')->field('id,rec_id,create_time')->where($where)->find();

        if($mem_res){
            //设置更新数据
            $up_data = [
                'avatarUrl'  => $param['avatarUrl'],
                'city'  => $param['city'],
                'country'  => $param['country'],
                'sex'  => $param['sex'],
                'nickname'  => $param['nickname'],
                'province'  => $param['province'],
                'bis_id'  => $param['bis_id'],
            ];

            $up_where = "mem_id = '$openid'";
            $res = Db::table('store_members')->where($up_where)->update($up_data);
            echo json_encode(array(
                'statuscode'      => $res,
                'message'     => '更新成功'
            ));
            exit;
        }else{
            //设置数据
            $data = [
                'bis_id'  => $param['bis_id'],
                'mem_id'  => $openid,
                'username'  => $openid,
                'truename'  => $openid,
                'avatarUrl'  => $param['avatarUrl'],
                'city'  => $param['city'],
                'country'  => $param['country'],
                'sex'  => $param['sex'],
                'nickname'  => $param['nickname'],
                'province'  => $param['province'],
                'rec_id'  => 1,
                'create_time'  => date('Y-m-d H:i:s')
            ];

            //添加数据
            $res = Db::table('store_members')->insertGetId($data);

            //返回openid
            echo json_encode(array(
                'statuscode'      => $res,
                'message'     => '创建成功'
            ));
            exit;
        }
    }
}
