<?php
namespace app\api\controller;
use think\Controller;
use think\cache\driver\Redis;

class Token extends Controller{

    public function createToken(){
        $randChar = self::getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $tokenSalt = config('token_salt');
        $token =  md5($randChar . $timestamp . $tokenSalt);
        return $token;
    }

    public static function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0;$i < $length;$i++) {
            $str .= $strPol[rand(0, $max)];
        }

        return $str;
    }

    public function saveToRedis($token){

    }
}