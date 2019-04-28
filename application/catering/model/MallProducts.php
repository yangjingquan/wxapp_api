<?php
namespace app\catering\model;
use think\Model;
use think\Db;

class MallProducts extends Model{

    //获取积分商品列表
    public function getJfProductInfo($bis_id,$limit,$offset){
        $res = Db::table('cy_mall_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,i.thumb,pro.original_price,pro.associator_price,ex_jifen,pro.supply_pro_id')
            ->join('cy_mall_pro_images i','pro.id = i.p_id','LEFT')
            ->where('pro.bis_id = '.$bis_id.' and pro.is_jf_product = 1 and pro.on_sale = 1 and pro.status = 1 and i.status = 1')
            ->order('pro.update_time desc')
            ->limit($offset,$limit)
            ->select();

        $ind = 0;
        $result = array();
        foreach($res as $val){
            $result[$ind]['pro_id'] = $val['pro_id'];
            $result[$ind]['p_name'] = $val['p_name'];
            $result[$ind]['thumb'] = $val['thumb'];
            $result[$ind]['original_price'] = $val['original_price'];
            if($val['associator_price'] < '0.01'){
                $result[$ind]['ex_jifen'] = $val['ex_jifen'].'积分';
            }else{
                $result[$ind]['ex_jifen'] = $val['ex_jifen'] .'积分' .' + '.$val['associator_price'] .'元';
            }
            $result[$ind]['is_supply_pro'] = !empty($val['supply_pro_id']) ? 1 : 0 ;
            $ind ++;
        }

        return $result;
    }

    //获取积分商品详情
    public function getJfProDetail($pro_id){
        $res = Db::table('cy_mall_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,pro.brand,pro.ex_jifen,i.thumb,i.image,i.config_image1,i.config_image2,i.config_image3,i.config_image4,pro.original_price,pro.associator_price,pro.wx_introduce,i.wx_config_image1,i.wx_config_image2,i.wx_config_image3,i.wx_config_image4,i.wx_config_image5,i.wx_config_image6,i.wx_config_image7,i.wx_config_image8,i.wx_config_image9,i.wx_config_image10')
            ->join('cy_mall_pro_images i','pro.id = i.p_id','LEFT')
            ->where('pro.id = '.$pro_id)
            ->find();

        //设置详情页轮播图
        $images_info = array();
        array_push($images_info,$res['image']);
        if($res['config_image1'] && $res['config_image1'] != ''){
            array_push($images_info,$res['config_image1']);
        }
        if($res['config_image2'] && $res['config_image2'] != ''){
            array_push($images_info,$res['config_image2']);
        }
        if($res['config_image3'] && $res['config_image3'] != ''){
            array_push($images_info,$res['config_image3']);
        }
        if($res['config_image4'] && $res['config_image4'] != ''){
            array_push($images_info,$res['config_image4']);
        }

        //设置商品详情部分图片
        $des_images_info = array();
        if($res['wx_config_image1'] && $res['wx_config_image1'] != ''){
            array_push($des_images_info,$res['wx_config_image1']);
        }
        if($res['wx_config_image2'] && $res['wx_config_image2'] != ''){
            array_push($des_images_info,$res['wx_config_image2']);
        }
        if($res['wx_config_image3'] && $res['wx_config_image3'] != ''){
            array_push($des_images_info,$res['wx_config_image3']);
        }
        if($res['wx_config_image4'] && $res['wx_config_image4'] != ''){
            array_push($des_images_info,$res['wx_config_image4']);
        }
        if($res['wx_config_image5'] && $res['wx_config_image5'] != ''){
            array_push($des_images_info,$res['wx_config_image5']);
        }
        if($res['wx_config_image6'] && $res['wx_config_image6'] != ''){
            array_push($des_images_info,$res['wx_config_image6']);
        }
        if($res['wx_config_image7'] && $res['wx_config_image7'] != ''){
            array_push($des_images_info,$res['wx_config_image7']);
        }
        if($res['wx_config_image8'] && $res['wx_config_image8'] != ''){
            array_push($des_images_info,$res['wx_config_image8']);
        }
        if($res['wx_config_image9'] && $res['wx_config_image9'] != ''){
            array_push($des_images_info,$res['wx_config_image9']);
        }
        if($res['wx_config_image10'] && $res['wx_config_image10'] != ''){
            array_push($des_images_info,$res['wx_config_image10']);
        }

        //设置规格信息
        $org_guige_info = DB::table('cy_mall_pro_config')
            ->field('content1_name,con_content1')
            ->where('pro_id = '.$res['pro_id'].' and status = 1')
            ->select();

        $guige_info = array();
        if(!$org_guige_info){
            $guige_info = array();
        }else{
            $con_content1_array = array();
            foreach($org_guige_info as $val){
                if(!in_array($val['con_content1'],$con_content1_array)){
                    array_push($con_content1_array,$val['con_content1']);
                }
            }

            $con_content1 = '';
            for($i=0;$i < count($con_content1_array);$i++){
                $con_content1 .=  $con_content1_array[$i] . '/';
            }
            $con_content1 = substr($con_content1,0,-1);

            $aa = [
                'content1_name'  => $org_guige_info[0]['content1_name'],
                'con_content1'   => $con_content1
            ];

            array_push($guige_info,$aa);
        }

        if($res['associator_price'] < '0.01'){
            $ex_jifen = $res['ex_jifen'] .'积分';
        }else{
            $ex_jifen = $res['ex_jifen'] .'积分'.' + '.$res['associator_price'].'元';
        }

        $result = [
            'pro_id' => $res['pro_id'],
            'p_name' => $res['p_name'],
            'brand' => $res['brand'],
            'ex_jifen' => $ex_jifen,
            'original_price' => $res['original_price'],
            'wx_introduce' => $res['wx_introduce'],
            'images'  => $images_info,
            'config_info' => $guige_info,
            'des_images' => $des_images_info
        ];

        return $result;
    }

    //获取积分商品配置信息
    public function getJfProConfigInfo($pro_id){
        $pro_res = Db::table('cy_mall_products')->alias('pro')->field('pro.id as pro_id,pro.p_name,pro.brand,i.thumb,pro.original_price,pro.associator_price,pro.ex_jifen')
            ->join('cy_mall_pro_images i','pro.id = i.p_id','LEFT')
            ->where('pro.id = '.$pro_id.' and pro.on_sale = 1 and pro.status = 1 and i.status = 1')
            ->find();

        //查询规格信息
        $org_guige_info = DB::table('cy_mall_pro_config')
            ->field('id as con_id,content1_name,con_content1')
            ->where('pro_id = '.$pro_res['pro_id'].' and status = 1')
            ->select();

        //设置规格数组内容
        $config1_info_array = array();
        $temp_config1_info_array = array();
        $config1_info_array['content_name'] = $org_guige_info[0]['content1_name'];
        $index = 0;
        foreach($org_guige_info as $val){
            $temp_config1_info_array[$index]['conid'] = $val['con_id'];
            $temp_config1_info_array[$index]['con_content'] = $val['con_content1'];
            $index ++;
        }

        $config1_info_count = count($temp_config1_info_array);
        $new_count1 = ceil($config1_info_count / 5);
        $new_res1 = array();
        for($i = 0; $i < $new_count1; $i ++){
            for($j=0;$j<5;$j++){
                $new_num = $i*5+$j;
                if($new_num < $config1_info_count){
                    $new_res1[$i][] = $temp_config1_info_array[$new_num];
                }else{
                    break;
                }

            }
        }

        $config1_info_array['con_content'] = $new_res1;

        if($pro_res['associator_price'] < '0.01'){
            $ex_jifen = $pro_res['ex_jifen'] .'积分';
        }else{
            $ex_jifen = $pro_res['ex_jifen'] .'积分'.' + '.$pro_res['associator_price'].'元';
        }

        $result = [
            'pro_id' => $pro_res['pro_id'],
            'p_name' => $pro_res['p_name'],
            'brand' => $pro_res['brand'],
            'thumb' => $pro_res['thumb'],
            'original_price' => $pro_res['original_price'],
            'associator_price' => $ex_jifen,
            'config1_info' => $config1_info_array
        ];
        return $result;
    }

    //根据一级名称获取积分商品信息
    public function getJfConById($pro_id){
        $res = DB::table('cy_mall_pro_config')->field('ex_jifen,price')
            ->where('id = '.$pro_id)
            ->find();

        if($res['price'] < '0.01'){
            $res['jf_price'] = $res['ex_jifen'].'积分';
        }else{
            $res['jf_price'] = $res['ex_jifen'].'积分'.' + '.$res['price'].'元';
        }
        return $res;
    }
}
