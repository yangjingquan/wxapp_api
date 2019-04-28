<?php
namespace app\catering\model;
use think\Model;
use think\Db;

class Activity extends Model{
    //获取活动信息
    public function getActivityInfo($bis_id,$total_amount,$isNewMember){
        $res = Db::table('cy_activitys')->field('id as act_id,type,activity_name,max,lose')
                ->where('bis_id = '.$bis_id.' and status = 1')
                ->order('id asc')
                ->select();

        if(!$res){
            echo json_encode(array(
                'statuscode'  => -1,
                'massage'      => '该商家暂未设置活动'
            ));
            exit;
        }

        $typeArr = array();
        foreach($res as $val){
            if(!in_array($val['type'],$typeArr)){
               array_push($typeArr,$val['type']);
            }
        }

        $act_res = $act_res1 = $act_res2 = $sort_act_res1 = $new_act_res1 = array();
        if($isNewMember){
            if(in_array(2,$typeArr)){
                foreach($res as $val){
                    if($val['type'] == 2){
                        $act_res2 = $val;
                        $total_amount = $total_amount - $val['lose'];
                    }else{
                        array_push($act_res1,$val);
                    }
                }
                $sort_act_res1 = $this->list_sort_by($act_res1, 'max');
                foreach($sort_act_res1 as $val){
                    if($total_amount >= $val['max']){
                        $total_amount = $total_amount - $val['lose'];
                        $new_act_res1 = $val;
                        break;
                    }
                }
                array_push($act_res,$act_res2);
                array_push($act_res,$new_act_res1);
            }else{
                $sort_act_res1 = $this->list_sort_by($res, 'max');
                foreach($sort_act_res1 as $val){
                    if($total_amount >= $val['max']){
                        $total_amount = $total_amount - $val['lose'];
                        $new_act_res1 = $val;
                        break;
                    }
                }
                array_push($act_res,$new_act_res1);
            }
        }else{
            foreach($res as $val){
                if($val['type'] == 1){
                    array_push($act_res1,$val);
                }
            }

            $sort_act_res1 = $this->list_sort_by($act_res1, 'max');

            foreach($sort_act_res1 as $val){
                if($total_amount >= $val['max']){
                    $total_amount = $total_amount - $val['lose'];
                    $new_act_res1 = $val;
                    break;
                }
            }
             if(count($new_act_res1) == 0){
                 $act_res = $new_act_res1;
             }else{
                 array_push($act_res,$new_act_res1);
             }

        }
        $return_res = [
            'total_amount'  => $total_amount,
            'act_res'  => $act_res
        ];

        return $return_res;
    }

    //对查询结果集进行逆向排序
    function list_sort_by($list, $field){
        if (is_array($list)){
            $refer = $resultSet = array();
            foreach ($list as $i => $data)
            {
                $refer[$i] = &$data[$field];
            }
            arsort($refer);
            foreach ($refer as $key => $val)
            {
                $resultSet[] = &$list[$key];
            }
            return $resultSet;
        }
        return false;
    }
}
