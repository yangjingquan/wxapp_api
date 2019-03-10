<?php
namespace app\index\controller;
use app\api\service\CheckService;
use think\Cache;
use think\Controller;
use think\Db;
use think\cache\driver\Redis;
use think\Exception;
use think\Loader;
use think\Log;

class Test extends Base{

    //测试接口
    public function test(){
//        $x = 5;
//        $y = 6;
//        $x ^= $y;
//        $y ^= $x;
//        $x ^= $y;
//
//        echo $x.'  '.$y;


//        $a = 9;
//        $b = 5;
//        echo sprintf("%b", $a^$b)."\n"; //二进制
//        echo sprintf("%d", $a^$b)."\n"; //十进制

//        $filename = "dir/upload.image.jpg";
//        echo strrchr($filename, '.');

//        $url = "http://www.phpmianshiti.com/abc/de/fg.php?id=1";
//        $arr = parse_url($url);
//
//        $pathArr = pathinfo($arr['path']);
//        dump($pathArr);
//        die;
//        echo $pathArr['extension'];


//        $n = 10;
//        $m = 7;
//        $mokey = range(1, $n);
//        $i = 0;
//
//        while (count($mokey) >1) {
//            $i += 1;
//            $head = array_shift($mokey);//一个个出列最前面的猴子
//            if ($i % $m !=0) {
//                #如果不是m的倍数，则把猴子返回尾部，否则就抛掉，也就是出列
//                array_push($mokey,$head);
//            }
//
//            // 剩下的最后一个就是大王了
//
//        }
//        echo $mokey[0];

//        $r = 0;
//        for ($i=2; $i <= $m ; $i++) {
//            $r = ($r + $m) % $i;
//        }
//
//        echo $r+1;

//        $a = range(0, 1000);
//        var_dump(memory_get_usage());
//
//        // 定义变量b，将a变量的值赋值给b
//        // COW Copy On Write
//        $b = $a;
//        var_dump(memory_get_usage());
//
//        // 对a进行修改
//        $a = range(0, 1000);
//        var_dump(memory_get_usage());

//        $data = ['a', 'b', 'c','d'];
//
//        foreach ($data as $key=>$val)
//        {
//            $val = &$data[$key];
//            dump($data);
//        }
//
//        dump($data);
//        $a = 0;
//        $b = 0;
//
//        if ($a = 3 > 0 || $b = 3 > 0)
//        {
//            $a++;
//            $b++;
//            echo $a. "\n";
//            echo $b. "\n";
//        }

//        $arr = ["trs"=>9,"jy"=>94,"aer"=>2,"gth"=>15];
//        $new_arr = array_reverse($arr);
//        dump($new_arr);

//        echo mktime(0,0,0,12,36,2001) . "<br>";
//        echo date("M-d-Y",mktime(0,0,0,14,1,2001)) . "<br>";
//        echo date("M-d-Y",mktime(0,0,0,1,1,2001)) . "<br>";
//        echo date("M-d-Y",mktime(0,0,0,1,1,99)) . "<br>";
//        echo microtime(true);

//        $a="abc";
//        $b="de";
//        echo '交换前 $a:'.$a.',$b:'.$b.'<br />';
//        $a.=$b;
//        $b=substr($a,0,(strlen($a)-strlen($b)));
//        $a=substr($a, strlen($b));
//        echo '交换后$a:'.$a.',$b:'.$b.'<br />';
//
//        echo '-----------------------<br/>';

//        $a = array(1,2,'a'=>3,'b'=>4);
//        $b = array('a'=>3,4,'b'=>5,6);
//        $c = array_merge($a,$b);
//        dump($c);
//        echo count('abc');

        $n=10;
        for($i=0;$i<$n;$i++){
            for($j=0;$j<=$i;$j++){
                if($j==0||$i==$j){
                    $arr[$i][$j]=1;
                }else {
                    $arr[$i][$j]=$arr[$i-1][$j]+$arr[$i-1][$j-1];
                }
                echo $arr[$i][$j]."\t";
            }
            echo "<br>";
        }
    }


    //冒泡排序
    public function testMpSort(){
        //冒泡排序
        $setarray = array('3','8','1','4','11','7');
        $getlenght = count($setarray);

        for($i=0;$i<$getlenght;$i++){
            for($j=0;$j<$getlenght-$i-1;$j++){
                if($setarray[$j] > $setarray[$j + 1]){
                    $temp = $setarray[$j + 1];
                    $setarray[$j + 1] = $setarray[$j];
                    $setarray[$j] = $temp;
                }
            }
        }

        dump($setarray);
    }

    //************************************************

    //测试归并排序
    public function test_gb_sort(){
        $arr = array(4, 7, 6, 3, 9, 5, 8);
        $len = count($arr);
        $new_arr = $this->mSort($arr, 0, $len-1);
        dump($new_arr);
    }

    /**
     * 实际实现归并排序的程序
     * @param &$arr array 需要排序的数组
     * @param $left int 子序列的左下标值
     * @param $right int 子序列的右下标值
     */
    function mSort(&$arr, $left, $right) {

        if($left < $right) {
            //说明子序列内存在多余1个的元素，那么需要拆分，分别排序，合并
            //计算拆分的位置，长度/2 去整
            $center = floor(($left+$right) / 2);
            //递归调用对左边进行再次排序：
            $this->mSort($arr, $left, $center);
            //递归调用对右边进行再次排序
            $this->mSort($arr, $center+1, $right);
            //合并排序结果
            return $this->mergeArray($arr, $left, $center, $right);
        }
    }

    /**
     * 将两个有序数组合并成一个有序数组
     * @param &$arr, 待排序的所有元素
     * @param $left, 排序子数组A的开始下标
     * @param $center, 排序子数组A与排序子数组B的中间下标，也就是数组A的结束下标
     * @param $right, 排序子数组B的结束下标（开始为$center+1)
     */
    function mergeArray(&$arr, $left, $center, $right) {
        //设置两个起始位置标记
        $a_i = $left;
        $b_i = $center+1;
        while($a_i<=$center && $b_i<=$right) {
            //当数组A和数组B都没有越界时
            if($arr[$a_i] < $arr[$b_i]) {
                $temp[] = $arr[$a_i++];
            } else {
                $temp[] = $arr[$b_i++];
            }
        }
        //判断 数组A内的元素是否都用完了，没有的话将其全部插入到C数组内：
        while($a_i <= $center) {
            $temp[] = $arr[$a_i++];
        }
        //判断 数组B内的元素是否都用完了，没有的话将其全部插入到C数组内：
        while($b_i <= $right) {
            $temp[] = $arr[$b_i++];
        }

        $len = count($temp);
        //将$arrC内排序好的部分，写入到$arr内：
        for($i=0; $i<$len; $i++) {
            $arr[$left+$i] = $temp[$i];
        }

        return $arr;

    }


    //************************************************


    //一个数组内连续8个非零数字的个数
    public function getNotZeroCount(){
        $arr = array(1,0,2,5,9,33,5,151,845,151,2,0,1848,415,18,151,81581,515,5,51,5,51,51,5,51,0,1);
        $arr_count = count($arr);
        $count = 0;
        $new_array = array();
        if($arr_count < 8){
            echo '0';
        }else{
            for($i=0;$i<$arr_count;$i++){
                if($arr[$i] != 0){
                    array_push($new_array,$arr[$i]);
                    $new_arr_count = count($new_array);
                    if($new_arr_count == 8){
                        $new_array = array();
                        $count ++;
                    }
                }else{
                    $new_array = array();
                }
            }
        }
        echo $count;
    }

    //数组去重
    public function arrRemoveRepetition(){
        $arr = array(1,2,2,5,9,33,5,151,845,151,2,1848,415,18,151,81581,515,5,51,5,51,51,5,51,1);
        $new_arr = array();
        foreach($arr as $val){
            $new_arr[$val] = 1;
        }
        dump($new_arr);
    }

    //查找第一个只出现一次的字符
    public function findFirstOnceAppearStr(){
        $str = 'abaccdeff';

        $len = strlen($str);
        $new_arr = array();
        for($i=0;$i<$len;$i++){
            if(array_key_exists(substr($str,$i,1),$new_arr)){
                $new_arr[substr($str,$i,1)] ++;
            }else{
                $new_arr[substr($str,$i,1)] = 1;
            }
        }

        foreach($new_arr as $key => $val){
            if($val == 1){
                echo $key;
                break;
            }
        }
    }

    //打印字符串的所有排列(未完成)
    public function printStrAllSort($str){
        $new_str = substr_replace($str,'q',0,1);
//        $len = strlen($str);
//        $left = substr($str,0,1);
//        $right = substr($str,1,-1);
//        for($i=1;$i<$len;$i++){
//
//        }
        echo $new_str;
    }

    public function testPrintStrAllSort(){
        $str = 'abcdefg';
        $this->printStrAllSort($str);
    }

    //斐波那契数列
    public function getFibomacci($n){
        $arr = array(0,1);
        if($n <= 2){
            dump($arr[$n]);
        }else{
            $fOne = 1;
            $fTwo = 0;
            $fibN = 0;
            for($i=2;$i<$n;$i++){
                $fibN = $fOne + $fTwo;
                array_push($arr,$fibN);
                $fTwo = $fOne;
                $fOne = $fibN;
            }

            dump($arr);
        }
    }

    public function testGetFibomacci(){
        $n = 10;
        $this->getFibomacci($n);
    }

    //**********************************************************
    //快速排序
    function QuickSort(){
        $arr = array(6,3,8,6,4,2,9,5,1);
        $low = 0;
        $high = count($arr) - 1;
        $this->QSort($arr,$low,$high);
        dump($arr);
    }


    function QSort(array &$arr,$low,$high){
        if($low < $high){
            $pivot = $this->Partition($arr,$low,$high);  //将$arr[$low...$high]一分为二，算出枢轴值
            $this->QSort($arr,$low,$pivot - 1);   //对低子表进行递归排序
            $this->QSort($arr,$pivot + 1,$high);  //对高子表进行递归排序
        }
    }

    function swap(array &$arr,$a,$b){
        $temp = $arr[$a];
        $arr[$a] = $arr[$b];
        $arr[$b] = $temp;
    }

    function Partition(array &$arr,$low,$high){
        $pivot = $arr[$low];   //选取子数组第一个元素作为枢轴
        while($low < $high){  //从数组的两端交替向中间扫描
            while($low < $high && $arr[$high] >= $pivot){
                $high --;
            }
            $this->swap($arr,$low,$high);	//终于遇到一个比$pivot小的数，将其放到数组低端
            while($low < $high && $arr[$low] <= $pivot){
                $low ++;
            }
            $this->swap($arr,$low,$high);	//终于遇到一个比$pivot大的数，将其放到数组高端
        }
        return $low;   //返回high也行，毕竟最后low和high都是停留在pivot下标处
    }


    //*********************************************************

    public function baoshu(){
        $inCir = array();

        // 如果有10个人, 每个人都在圆圈内
        for ($i=1; $i<=10; $i++) {
            $inCir[$i] = 1;
        }

        $countPeople = count($inCir);

        // 开始报数
        $callNo = 0;

        // 从第一个人开始报数
        $peopleNo = 1;

        // 出去的人人数
        $outCir = 0;

        // 当只有一个人的时候，停止循环
        while($outCir !== $countPeople - 1) {

            // 如果此人么有出去，则继续报号
            if ($inCir[$peopleNo] == 1) {
                $callNo++ ;
            }

            //如果此人报数为3 则设置为已经出去
            if ($callNo === 3) {
                $inCir[$peopleNo] = 0;
                $outCir++;
                // 重新开始报号
                $callNo = 0;
            }

            // 该下一个人报号
            $peopleNo = $peopleNo + 1;

            // 如果到第10个人，则有重新回到第一个人
            if ($peopleNo > $countPeople) {
                $peopleNo = 1;
            }
        }



        for ($i=1; $i<=$countPeople; $i++) {
            if ($inCir[$i] == 1) {
                echo $i;
            }
        }

    }

    public function wash_card($card_num){
        $cards = $tmp = array();
        for($i = 0;$i < $card_num;$i++){
            $tmp[$i] = $i;
        }

        for($i = 0;$i < $card_num;$i++){
            $index = rand(0,$card_num-$i-1);
            $cards[$i] = $tmp[$index];
            unset($tmp[$index]);
            $tmp = array_values($tmp);
        }
        return $cards;
    }

    public function test_wash_card(){
        $card_num = 54;
        $arr = $this->wash_card($card_num);
        dump($arr);
    }

    public function test_array_values(){
        $arr = array(
            ['id'=>"1","name"=>"小一","age"=>12],
            ['id'=>"2","name"=>"小二","age"=>12],
            ['id'=>"3","name"=>"小三","age"=>12],
            ['id'=>"4","name"=>"小四","age"=>12],
        );

        $arr_name = array_column($arr,'name');
        dump($arr_name);
    }

    //*************************************************
    //二分法查找数字
    public function test_findNum(){
        $arr = array(1,3,4,6,8,9,12);
        $num = 13;
        $loc = $this->binarySearch($arr,$num);
        echo $loc;
    }

    public function binarySearch(Array $arr, $target) {
        $low = 0;
        $high = count($arr) - 1;

        while($low <= $high) {
            $mid = floor(($low + $high) / 2);
            #找到元素
            if ($arr[$mid] == $target) {
                return $mid;
            }
            #中元素比目标大,查找左部
            if ($arr[$mid] > $target) {
                $high = $mid - 1;
            }
            #中元素比目标小,查找右部
            if ($arr[$mid] < $target) {
                $low = $mid + 1;
            }
        }
        #查找失败
        return 'not exist';
     }
    //***********************************************

    //***********************************************
    //学生分数排名问题
    public function scoreRank(){
        $scoreArr = ['A'=>60, 'B'=>70, 'C'=>80, 'D'=>70];

        //获取去重后的成绩
        $scores = array_unique(array_values($scoreArr));

        //按照成绩降序排列
        rsort($scores);
        $rank = array();
        foreach($scoreArr as $key => $val){
            $rank[$key] = array_search($val,$scores) + 1;
        }

        // 按照排名升序排序
        asort($rank);
        dump($rank);

    }


    //***********************************************
    public function testObject(){
        $person = new stdClass();
        $person->name = 'tom';
        $person->age = 20;

        $persons[] = $person;

        $person->name = 'jack';
        $person->age = 19;

        $persons[] = $person;

        var_dump($persons);
    }

    public function testRedis(){
        $redis = new Redis();
        $bis_id = input('get.bis_id');
        $res = model('Recommend')->getBanners($bis_id);
        $redis_key = "test_banners_list".$bis_id;
        $json = json_encode($res);
        $redis->set($redis_key,$json);
        print_r($json);
    }

    public function testGetRedis(){
        $redis = new Redis();
        $bis_id = input('get.bis_id');
        $redis_key = "test_banners_list".$bis_id;
        $json = $redis->get($redis_key);
        print_r($json);
        die;
    }

    public function testException(){
        echo phpinfo();
        die;
        try {

            //业务处理 错误时抛出异常。
            $age = '110';
            CheckService::checkEmpty($age);
            $res = [
                'age' => $age,
                'name'  => 'lilei'
            ];
            if ($age > 120) {
                throw new Exception('年龄不能大于120岁。', 1001);
            }
        } catch (Exception $e) {
            return $this->render(false,$e->getCode(),$e->getMessage(),$e->getFile(),$e->getLine());
        }
        return $this->render($res);
    }
}
