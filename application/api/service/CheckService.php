<?php
namespace app\api\service;
use think\Exception;

class CheckService{
    public static function checkEmpty($value,$name='数据')
    {
        if(empty($value)){
            throw new Exception("$name 不能为空!",-1);
        }
        return true;
    }

    public static function checkArray($value,$name='数据',$from='default')
    {
        if(!is_array($value)){
            throw new Exception("$name 格式不对!",-1);
        }
        return true;
    }

    public static function checkPositiveInt($value,$name='参数')
    {
        if(!is_numeric($value) || (int)$value != $value || $value <= 0){
            throw new Exception("$name 必须为正整数!",-1);
        }
        return true;
    }

    public static function checkNumber($value, $name='参数')
    {
        if (!is_numeric($value)) {
            throw new Exception("{$name}必须为数字!", -1);
        }
        return true;
    }

    public static function checkNonNegativeInt($value, $name = '参数')
    {
        if(!is_numeric($value) || (int)$value != $value || $value < 0){
            throw new Exception("$name $value 必须为非负整数!",-1);
        }
        return true;
    }

    public static function checkDateTime($value,$format='Y-m-d H:i:s')
    {
        if (date($format,strtotime($value)) != $value) {
            throw new Exception("日期格式不对!",-1);
        }
        return true;
    }
}