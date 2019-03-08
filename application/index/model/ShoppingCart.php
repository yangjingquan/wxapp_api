<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ShoppingCart extends Model{

    //添加商品到购物车(普通版)
    public function addProIntoCart($param){
        //获取参数
        $pro_id = !empty($param['pro_id']) ? $param['pro_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $count = !empty($param['count']) ? $param['count'] : '';
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';

        $data = [
            'bis_id'  => $bis_id,
            'pro_id'  => $pro_id,
            'wx_id'  => $wx_id,
            'count'  => $count,
            'selected'  => 1,
            'cart_type'  => 1,
            'create_time'  => date('Y-m-d H:i:s'),
            'update_time'  => date('Y-m-d H:i:s'),
        ];

        $res = Db::table('store_shopping_carts')->insertGetId($data);

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加失败'
            ));
            exit;
        }

        return $res;
    }

    //添加商品到购物车(拼团版)
    public function addGroupProIntoCart($param){
        //获取参数
        $pro_id = !empty($param['pro_id']) ? $param['pro_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $count = !empty($param['count']) ? $param['count'] : '';
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';

        $data = [
            'bis_id'  => $bis_id,
            'pro_id'  => $pro_id,
            'wx_id'  => $wx_id,
            'count'  => $count,
            'selected'  => 1,
            'cart_type'  => 2,
            'create_time'  => date('Y-m-d H:i:s'),
            'update_time'  => date('Y-m-d H:i:s'),
        ];

        $res = Db::table('store_shopping_carts')->insertGetId($data);

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加失败'
            ));
            exit;
        }

        return $res;
    }

    //添加积分商品到购物车(多用户版)
    public function addJfProIntoCart($param){
        //获取参数
        $pro_id = !empty($param['pro_id']) ? $param['pro_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $count = !empty($param['count']) ? $param['count'] : '';
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';

        $data = [
            'bis_id'  => $bis_id,
            'pro_id'  => $pro_id,
            'wx_id'  => $wx_id,
            'count'  => $count,
            'selected'  => 1,
            'cart_type'  => 3,
            'create_time'  => date('Y-m-d H:i:s'),
            'update_time'  => date('Y-m-d H:i:s'),
        ];

        $res = Db::table('store_shopping_carts')->insertGetId($data);

        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '添加失败'
            ));
            exit;
        }

        return $res;
    }

    //获取购物车信息(单用户版)
    public function getShoppingCartInfo($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $where = "carts.bis_id = ".$bis_id." and carts.wx_id = '$wx_id' and carts.status = 1 and carts.cart_type = 1 and con.status = 1 and img.status = 1";
        $res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,pro.p_name,con.con_content1,con_content2,con.price as associator_price,carts.count,carts.selected,img.thumb')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('carts.create_time desc')
            ->select();
        if(!$res){
            echo json_encode(array(
                'statuscode'  => 0,
                'message'     => '暂无数据'
            ));
            exit;
        }

        $index = 0;
        foreach($res as $val){
            $res[$index]['selected'] = $val['selected']  == 1 ? true : false;
            $index ++;
        }
        return $res;
    }

    //获取购物车信息(多用户版)
    public function getShoppingCartInfoMulti($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';

        $where = "carts.wx_id = '$wx_id' and carts.status = 1 and carts.cart_type = 1 and con.status = 1 and img.status = 1";
        $res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,carts.bis_id')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('carts.create_time desc')
            ->select();

        $bisArr = array();
        foreach($res as $val){
            if(!in_array($val['bis_id'],$bisArr)){
               array_push($bisArr,$val['bis_id']) ;
            }
        }

        $index = 0;
        $cart_res = array();
        foreach($bisArr as $val){
            //获取店铺信息
            $bis_res = Db::table('store_bis')->field('id as bis_id,bis_name,thumb as bis_thumb')->where('id = '.$val)->find();
            $cart_res[$index]['bis_id'] = $bis_res['bis_id'];
            $cart_res[$index]['bis_status'] = $this->checkBisStatus($wx_id,$bis_res['bis_id']);
            $cart_res[$index]['bis_name'] = $bis_res['bis_name'];
            $cart_res[$index]['bis_thumb'] = $bis_res['bis_thumb'];
            $cart_res[$index]['pro_info'] = $this->getCartProInfo($wx_id,$bis_res['bis_id']);
            $index ++;
        }

        return $cart_res;
    }

    //获取购物车内指定店铺商品信息
    public function getCartProInfo($wx_id,$bis_id){
        $where = "carts.wx_id = '$wx_id' and carts.bis_id = $bis_id and carts.status = 1 and carts.cart_type = 1 and con.status = 1 and img.status = 1";
        $res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,pro.p_name,con.con_content1,con_content2,con.price as associator_price,carts.count,carts.selected,img.thumb')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('carts.create_time desc')
            ->select();
        $index = 0;
        foreach($res as $val){
            $res[$index]['selected'] = $val['selected']  == 1 ? true : false;
            $index ++;
        }
        return $res;
    }

    //检验指定店铺购物车选中状态
    public function checkBisStatus($wx_id,$bis_id){
        $where = "carts.wx_id = '$wx_id' and carts.bis_id = $bis_id and carts.status = 1 and carts.cart_type = 1";
        $res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,carts.selected')
            ->where($where)
            ->order('carts.create_time desc')
            ->select();

        foreach($res as $val){
            if($val['selected'] == 1){
                continue;
            }else{
                return false;
            }
        }
        return true;

    }

    //更改单条信息选中状态
    public function updateSelectedStatus($param){
        //获取参数
        $cart_id = !empty($param['cart_id']) ? $param['cart_id'] : '';
        $selected_status = !empty($param['selected_status']) ? $param['selected_status'] : '';
        $data['selected'] = $selected_status;
        $res = Db::table('store_shopping_carts')->where('id = '.$cart_id)->update($data);

        return $res;
    }

    //获取选中的价格信息(单用户版)
    public function getSelectedTotalPrice($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $where = "carts.bis_id = ".$bis_id." and carts.wx_id = '$wx_id' and carts.status = 1 and carts.selected = 1";
        $res = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where)
            ->SUM('con.price * carts.count');
        if(!$res){
            $res = 0;
        }

        return $res;
    }

    //获取选中的价格信息(多用户版)
    public function getSelectedTotalPriceMulti($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $where = "carts.wx_id = '$wx_id' and carts.status = 1 and carts.cart_type = 1 and carts.selected = 1";
        $res = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where)
            ->SUM('con.price * carts.count');
        if(!$res){
            $res = 0;
        }

        return $res;
    }

    //更改全部选中状态(单用户版)
    public function updateAllSelectedStatus($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $bis_id = !empty($param['bis_id']) ? $param['bis_id'] : '';
        $selected_status = !empty($param['selected_status']) ? $param['selected_status'] : '';
        $data['selected'] = $selected_status;
        $where = "bis_id = ".$bis_id." and wx_id = '$wx_id'";
        $res = Db::table('store_shopping_carts')->where($where)->update($data);
        return $res;
    }

    //更改全部选中状态(多用户版)
    public function updateAllSelectedStatusMulti($param){
        //获取参数
        $wx_id = !empty($param['wx_id']) ? $param['wx_id'] : '';
        $selected_status = !empty($param['selected_status']) ? $param['selected_status'] : '';
        $data['selected'] = $selected_status;
        $where = "wx_id = '$wx_id' and cart_type = 1";
        $res = Db::table('store_shopping_carts')->where($where)->update($data);
        return $res;
    }

    //更改单个商品选中数量(普通商城版)
    public function updateSelectedCount($param){
        //获取参数
        $cart_id = !empty($param['cart_id']) ? $param['cart_id'] : '';
        $selected_status = !empty($param['selected_status']) ? $param['selected_status'] : '';
        $selectedcount = !empty($param['selectedcount']) ? $param['selectedcount'] : '';
        $type = !empty($param['type']) ? $param['type'] : '';

        //设置修改内容
        $con = '';
        if($selected_status && $selected_status == 1){
            $con = ",selected = 1";
        }

        if($type == 'sub'){
            $newselectedcount = $selectedcount - 1;
        }else{
            $newselectedcount = $selectedcount + 1;
        }

        $sql = "UPDATE store_shopping_carts SET count = ".$newselectedcount.$con." WHERE id = ".$cart_id;
        $res = Db::execute($sql);

        return $res;
    }

    //更改单个商品选中数量(拼团商城单独购买版)
    public function updateProCountBySingle($param){
        //获取参数
        $cart_id = !empty($param['cart_id']) ? $param['cart_id'] : '';
        $selectedcount = !empty($param['selectedcount']) ? $param['selectedcount'] : '';
        $type = !empty($param['type']) ? $param['type'] : '';

        if($type == 'sub'){
            $newselectedcount = $selectedcount - 1;
        }else{
            $newselectedcount = $selectedcount + 1;
        }

        $data['count'] = $newselectedcount;
        //更新数量
        Db::table('store_shopping_carts')->where('id = '.$cart_id)->update($data);
        $res = Db::table('store_shopping_carts')->field('count')->where('id = '.$cart_id)->find();

        return $res['count'];
    }

    //独立版--点击"去结算"返回选中的购物车内信息
    public function getSelectedCartInfo($wx_id,$bis_id){
        $where = "carts.wx_id = '$wx_id' and carts.status = 1 and con.status = 1 and img.status = 1 and carts.selected = 1";
        $pro_res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,con.id as pro_id,pro.p_name,con.con_content1,con_content2,con.price as associator_price,carts.count,carts.selected,img.thumb,pro.rec_rate')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('carts.create_time desc')
            ->select();
        $where1 = "carts.wx_id = '$wx_id' and carts.status = 1 and con.status = 1 and carts.selected = 1";
        $pro_amount = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where1)
            ->SUM('con.price * carts.count');

        $total_weight = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where1)
            ->SUM('pro.weight * carts.count');

        $address_res = Db::table('store_address')->field('id as a_id,rec_name,mobile,province,city,area,address,idno')
            ->where("mem_id = '$wx_id' and status = 1 and is_default = 1")
            ->find();


        if(!$address_res){
            $address_array = array();
            $transportType = array();
            $transportInfo = array();
        }else{
            $address_array = [
                'address_id'  => $address_res['a_id'],
                'rec_name'  => $address_res['rec_name'],
                'mobile'  => $address_res['mobile'],
                'idno'  => $address_res['idno'],
                'address'  => $address_res['province'].$address_res['city'].$address_res['area'].$address_res['address'],
                'province'  => $address_res['province']
            ];
            $transportType = $this->getTransportType($bis_id,$address_res['province']);
            $transportInfo = $this->getTransportInfo($bis_id,$address_res['province']);
        }

        return array(
            'address_info' => $address_array,
            'pro_info' => $pro_res,
            'pro_amount' => $pro_amount,
            'total_weight' => $total_weight,
            'transportType' => $transportType,
            'transportInfo' => $transportInfo
        );
    }

    //总站--点击"去结算"返回选中的购物车内信息
    public function getSelectedCartInfoMulti($wx_id){
        //获取店铺信息
        $where = "carts.wx_id = '$wx_id' and carts.cart_type = 1 and carts.status = 1 and con.status = 1 and img.status = 1 and carts.selected = 1";
        $bis_res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,carts.bis_id')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('carts.create_time desc')
            ->select();

        $bisArr = array();
        foreach($bis_res as $val){
            if(!in_array($val['bis_id'],$bisArr)){
                array_push($bisArr,$val['bis_id']);
            }
        }

        $index = 0;
        $cart_res = array();
        //地址信息
        $address_info = $this->getAddressInfoMulti($wx_id);
        foreach($bisArr as $val){
            //获取店铺信息
            $bis_res = Db::table('store_bis')->field('id as bis_id,bis_name,thumb as bis_thumb')->where('id = '.$val)->find();
            $cart_res[$index]['bis_id'] = $bis_res['bis_id'];
            $cart_res[$index]['bis_name'] = $bis_res['bis_name'];
            $cart_res[$index]['bis_thumb'] = $bis_res['bis_thumb'];
            $cart_res[$index]['pro_info'] = $this->getSelectedCartProInfoMulti($wx_id,$val);
            $total_weight = $this->getTotalWeightMulti($wx_id,$val);
            $pro_amount = $this->getProAmountMulti($wx_id,$val);
            $cart_res[$index]['pro_amount'] = number_format($pro_amount,2,".","");
            $cart_res[$index]['total_weight'] = $total_weight;
            $transportType = $this->getTransportType($val,$address_info['province']);
            $cart_res[$index]['transport_type'] = $transportType;
            $transportInfo = $this->getTransportInfo($val,$address_info['province']);
            $cart_res[$index]['transport_info'] = $transportInfo;
            $bisTransportTypeRes = $this->getBisTransportAbout($val);
            $bisTransportType = $bisTransportTypeRes['transport_type'];
            $logistics_status = $bisTransportTypeRes['logistics_status'];
            $cart_res[$index]['transportType'] = $bisTransportType;
            $cart_res[$index]['logistics_status'] = $logistics_status;

            if($logistics_status == 1){
                if($bisTransportType == 1){
                    if(count($transportInfo) == 0){
                        $cart_res[$index]['showFreightView'] = false;
                        $cart_res[$index]['total_amount'] = '0.00';
                        $cart_res[$index]['transport_fee'] = '0.00';
                        $cart_res[$index]['selected_transport_type'] = '';
                        $cart_res[$index]['buttonUsable'] = true;
                    }else{
                        $cart_res[$index]['showFreightView'] = true;
                        $first_heavy = $transportInfo[0]['first_heavy'];
                        $continue_heavy = $transportInfo[0]['continue_heavy'];
                        $continue_stage = $transportInfo[0]['continue_stage'];
                        $transport_fee = number_format($first_heavy + ($continue_heavy * (($total_weight - 1) / $continue_stage)),2,".","");
                        $cart_res[$index]['transport_fee'] = $transport_fee;
                        $total_amount = number_format(($pro_amount + $transport_fee),2,".","");
                        $cart_res[$index]['total_amount'] = $total_amount;
                        $cart_res[$index]['selected_transport_type'] = $transportInfo[0]['mode_id'];
                        $cart_res[$index]['buttonUsable'] = false;
                    }
                }else{
                    $ykj_price = $bisTransportTypeRes['ykj_price'];
                    $total_amount = number_format(($pro_amount + $ykj_price),2,".","");
                    $cart_res[$index]['showFreightView'] = false;
                    $cart_res[$index]['total_amount'] = $total_amount;
                    $cart_res[$index]['transport_fee'] = $ykj_price;
                    $cart_res[$index]['selected_transport_type'] = '';
                    $cart_res[$index]['buttonUsable'] = false;
                }
            }else{
                $total_amount = number_format($pro_amount,2,".","");
                $cart_res[$index]['showFreightView'] = false;
                $cart_res[$index]['total_amount'] = $total_amount;
                $cart_res[$index]['transport_fee'] = '0.00';
                $cart_res[$index]['selected_transport_type'] = '';
                $cart_res[$index]['buttonUsable'] = false;
            }

            $cart_res[$index]['selectedIndex'] = 0;
            $cart_res[$index]['showTransportFeeDetail'] = false;
            $index ++;
        }


        $total_amount = 0;
        foreach($cart_res as $val){
            $single_amount = $val['total_amount'];
            $total_amount = number_format(($total_amount + $single_amount),2,".","");
        }

        return array(
            'address_info' => $address_info,
            'cart_info' => $cart_res,
            'total_amount' => $total_amount
        );
    }

    //获取选中的商品信息
    public function getSelectedCartProInfoMulti($openid,$bis_id){
        $where = "carts.wx_id = '$openid' and carts.bis_id = $bis_id and carts.cart_type = 1 and carts.status = 1 and con.status = 1 and img.status = 1 and carts.selected = 1";
        $pro_res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,con.id as pro_id,pro.p_name,con.con_content1,con_content2,con.price as associator_price,carts.count,carts.selected,img.thumb,pro.rec_rate')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->order('carts.create_time desc')
            ->select();
        return $pro_res;
    }

    //获取用户选中的地址
    public function getAddressInfoMulti($wx_id){
        $address_res = Db::table('store_address')->field('id as a_id,rec_name,mobile,province,city,area,address,idno')
            ->where("mem_id = '$wx_id' and status = 1 and is_default = 1")
            ->find();
        return $address_res;
    }

    //获取购物车内指定店铺下商品总重量
    public function getTotalWeightMulti($wx_id,$bis_id){
        $where1 = "carts.wx_id = '$wx_id' and carts.bis_id = $bis_id and carts.status = 1 and con.status = 1 and carts.selected = 1";
        $total_weight = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where1)
            ->SUM('pro.weight * carts.count');

        return $total_weight;
    }

    //获取购物车内指定店铺下商品总价格
    public function getProAmountMulti($wx_id,$bis_id){
        $where1 = "carts.wx_id = '$wx_id' and carts.bis_id = $bis_id and carts.cart_type = 1 and carts.status = 1 and con.status = 1 and carts.selected = 1";
        $pro_amount = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where1)
            ->SUM('con.price * carts.count');

        return $pro_amount;
    }

    //获取店铺的运费模式及一口价运费
    public function getBisTransportAbout($bis_id){
        $res = Db::table('store_bis')->field('logistics_status,transport_type,ykj_price')->where('id = '.$bis_id)->find();
        return $res;
    }

    //单独购买(拼团版)
    public function getSelectedCartInfoBySingle($cart_id,$openid,$bis_id,$from){
        $where = "carts.id = '$cart_id' and con.status = 1 and img.status = 1";
        $where1 = "carts.id = '$cart_id' and carts.status = 1 and con.status = 1";
        if($from == 'single'){
            $pro_res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,con.id as pro_id,pro.p_name,con.con_content1,con_content2,con.price as associator_price,carts.count,carts.selected,img.thumb,pro.rec_rate')
                ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
                ->join('store_products pro','con.pro_id = pro.id','LEFT')
                ->join('store_pro_images img','img.p_id = pro.id','LEFT')
                ->where($where)
                ->find();

            $pro_amount = Db::table('store_shopping_carts')->alias('carts')
                ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
                ->join('store_products pro','con.pro_id = pro.id','LEFT')
                ->where($where1)
                ->SUM('con.price * carts.count');
        }else{
            $pro_res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,con.id as pro_id,pro.p_name,con.con_content1,con_content2,con.group_price as associator_price,carts.count,carts.selected,img.thumb,pro.rec_rate')
                ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
                ->join('store_products pro','con.pro_id = pro.id','LEFT')
                ->join('store_pro_images img','img.p_id = pro.id','LEFT')
                ->where($where)
                ->find();

            $pro_amount = Db::table('store_shopping_carts')->alias('carts')
                ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
                ->join('store_products pro','con.pro_id = pro.id','LEFT')
                ->where($where1)
                ->SUM('con.group_price * carts.count');
        }

        $total_weight = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where1)
            ->SUM('pro.weight * carts.count');

        $address_res = Db::table('store_address')->field('id as a_id,rec_name,mobile,province,city,area,address,idno')
            ->where("mem_id = '$openid' and status = 1 and is_default = 1")
            ->find();


        if(!$address_res){
            $address_array = array();
            $transportType = array();
            $transportInfo = array();
        }else{
            $address_array = [
                'address_id'  => $address_res['a_id'],
                'rec_name'  => $address_res['rec_name'],
                'mobile'  => $address_res['mobile'],
                'idno'  => $address_res['idno'],
                'address'  => $address_res['province'].$address_res['city'].$address_res['area'].$address_res['address'],
                'province'  => $address_res['province']
            ];
            $transportType = $this->getTransportType($bis_id,$address_res['province']);
            $transportInfo = $this->getTransportInfo($bis_id,$address_res['province']);
        }

        return array(
            'address_info' => $address_array,
            'pro_info' => $pro_res,
            'pro_amount' => $pro_amount,
            'total_weight' => $total_weight,
            'transportType' => $transportType,
            'transportInfo' => $transportInfo
        );

    }

    //获取购物车内选中的积分商品
    public function getJfProSelectedInfo($cart_id,$openid,$bis_id){
        $where = "carts.id = '$cart_id' and con.status = 1 and img.status = 1";
        $where1 = "carts.id = '$cart_id' and carts.status = 1 and con.status = 1";

        $pro_res = Db::table('store_shopping_carts')->alias('carts')->field('carts.id as cart_id,con.id as pro_id,pro.p_name,pro.weight,con.con_content1,con_content2,con.price as associator_price,con.ex_jifen,carts.count,carts.selected,img.thumb,pro.rec_rate')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->join('store_pro_images img','img.p_id = pro.id','LEFT')
            ->where($where)
            ->find();
        if($pro_res['associator_price'] < '0.01'){
            $pro_res['jf_price'] = $pro_res['ex_jifen'].'积分';
        }else{
            $pro_res['jf_price'] = $pro_res['ex_jifen'].'积分'.' + '.$pro_res['associator_price'].'元';
        }


        $jifen_amount = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where1)
            ->SUM('con.ex_jifen * carts.count');

        $price_amount = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where1)
            ->SUM('con.price * carts.count');

        $total_weight = Db::table('store_shopping_carts')->alias('carts')
            ->join('store_pro_config con','carts.pro_id = con.id','LEFT')
            ->join('store_products pro','con.pro_id = pro.id','LEFT')
            ->where($where1)
            ->SUM('pro.weight * carts.count');

        $address_res = Db::table('store_address')->field('id as a_id,rec_name,mobile,province,city,area,address,idno')
            ->where("mem_id = '$openid' and status = 1 and is_default = 1")
            ->find();


        if(!$address_res){
            $address_array = array();
            $transportType = array();
            $transportInfo = array();
        }else{
            $address_array = [
                'address_id'  => $address_res['a_id'],
                'rec_name'  => $address_res['rec_name'],
                'mobile'  => $address_res['mobile'],
                'idno'  => $address_res['idno'],
                'address'  => $address_res['province'].$address_res['city'].$address_res['area'].$address_res['address'],
                'province'  => $address_res['province']
            ];
            $transportType = $this->getTransportType($bis_id,$address_res['province']);
            $transportInfo = $this->getTransportInfo($bis_id,$address_res['province']);
        }

        return array(
            'address_info' => $address_array,
            'pro_info' => $pro_res,
            'jifen_amount' => $jifen_amount,
            'price_amount' => $price_amount,
            'total_weight' => $total_weight,
            'transportType' => $transportType,
            'transportInfo' => $transportInfo
        );

    }

    //判断购物车内是否存在勾选的产品
    public function checkSelectedPro($wx_id){
        $where = "wx_id = '$wx_id' and selected = 1 and status = 1";
        $res = Db::table('store_shopping_carts')->where($where)->count();

        if($res > 0){
            return 1;
        }else{
            return 0;
        }
    }

    //获取当前店铺的物流种类
    public function getTransportType($bis_id,$province){
        $where = "tem.bis_id = '$bis_id' and tem.province like '%$province%' and tem.status = 1";
        $res = Db::table('store_logistics_template')->alias('tem')->field('tem.id as tem_id,mode.post_mode')
            ->join('store_post_mode mode','tem.mode_id = mode.id','LEFT')
            ->where($where)
            ->select();

        $tt_res = array();
        foreach($res as $val){
            array_push($tt_res,$val['post_mode']);
        }

        return $tt_res;
    }


    //获取当前店铺的物流种类相关
    public function getTransportInfo($bis_id,$province){
        $where = "tem.bis_id = '$bis_id' and tem.province like '%$province%' and tem.status = 1";
        $res = Db::table('store_logistics_template')->alias('tem')->field('tem.id as tem_id,mode.post_mode,tem.first_heavy,tem.continue_heavy,mode.id as mode_id,mode.continue_stage')
            ->join('store_post_mode mode','tem.mode_id = mode.id','LEFT')
            ->where($where)
            ->select();

        return $res;
    }

    public function updateBisStatus($openid,$bis_id,$bis_status){
        $where = "wx_id = '$openid' and bis_id = $bis_id";
        if($bis_status == 1){
            $data['selected'] = 0;
        }else{
            $data['selected'] = 1;
        }
        $res = Db::table('store_shopping_carts')->where($where)->update($data);
        return $res;
    }
}