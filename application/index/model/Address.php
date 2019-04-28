<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Address extends Model{
    //获取地址信息
    public function getAddressInfo($openid){
        $res = DB::table('store_address')->field('id as a_id,rec_name,mobile,province,city,area,address,idno')->where("mem_id = '$openid' and status = 1 ")->order('create_time desc')->select();

        $index = 0;
        $result = array();
        foreach($res as $val){
            $result[$index]['a_id'] = $val['a_id'];
            $result[$index]['rec_name'] = $val['rec_name'];
            $result[$index]['mobile'] = $val['mobile'];
            $result[$index]['address'] = $val['province'].$val['city'].$val['area'].$val['address'];
            $result[$index]['idno'] = $val['idno'];
            $index ++;
        }
       return $result;
    }

    //添加地址
    public function addAddress($param){
        //获取参数
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        $rec_name = !empty($param['receiver']) ? $param['receiver'] : '';
        $mobile = !empty($param['contact']) ? $param['contact'] : '';
        $address = !empty($param['detail_address']) ? $param['detail_address'] : '';
        $province = !empty($param['address'][0]) ? $param['address'][0] : '';
        $city = !empty($param['address'][1]) ? $param['address'][1] : '';
        $area = !empty($param['address'][2]) ? $param['address'][2] : '';
        $idno = !empty($param['idno']) ? $param['idno'] : '';

        $data = [
            'mem_id' => $openid,
            'rec_name' => $rec_name,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'area' => $area,
            'address' => $address,
            'idno' => $idno,
            'is_default' => 0,
            'create_time' => date('Y-m-d H:i:s')
        ];

        $res = DB::table('store_address')->insert($data);

        return $res;
    }

    //编辑地址(返回地址信息)
    public function getAddressInfoById($a_id){
        $res = DB::table('store_address')->field('id as a_id,rec_name,mobile,province,city,area,address,idno')->where("id = $a_id")->find();

        return $res;
    }

    //更新地址信息
    public function updateAddress($param){
        //获取参数
        $aid = !empty($param['aid']) ? $param['aid'] : '';
        $rec_name = !empty($param['receiver']) ? $param['receiver'] : '';
        $mobile = !empty($param['contact']) ? $param['contact'] : '';
        $address = !empty($param['detail_address']) ? $param['detail_address'] : '';
        $province = !empty($param['address'][0]) ? $param['address'][0] : '';
        $city = !empty($param['address'][1]) ? $param['address'][1] : '';
        $area = !empty($param['address'][2]) ? $param['address'][2] : '';
        $idno = !empty($param['idno']) ? $param['idno'] : '';

        $data = [
            'rec_name' => $rec_name,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'area' => $area,
            'address' => $address,
            'idno' => $idno,
            'is_default' => 0
        ];

        $res = DB::table('store_address')->where("id = $aid")->update($data);
        return $res;
    }

    //下单时选择地址
    public function chooseAddress($param){
        //获取参数
        $openid = !empty($param['openid']) ? $param['openid'] : '';
        $selected_id = !empty($param['selected_id']) ? $param['selected_id'] : '';
        //更改该用户所有地址默认状态为0
        $where = "mem_id = '$openid' and status = 1";
        $data1['is_default'] = 0;
        $default_res = Db::table('store_address')->where($where)->update($data1);

        //设置选中的地址is_default为1
        $con = "id = $selected_id";
        $data2['is_default'] = 1;
        $res = Db::table('store_address')->where($con)->update($data2);

        if($res){
            return 1;
        }else{
            return 0;
        }
    }
}