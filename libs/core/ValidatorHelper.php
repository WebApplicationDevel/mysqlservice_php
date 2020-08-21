<?php

/**
 * 文件：validatorHelper.php
 * 说明：有效检测助手
 * 作者：李光强
 * 时间：2017/10/31/
 */
class validatorHelper {
    
    public static function stringfilter($string) {
        $str = yun_iconv("utf-8", "gbk", trim($string));

        $regex = "/\\$|\'|\\\|/";
        $str = preg_replace($regex, "", $str);
        return $str;
    }

    public static function CheckMoblie($moblie) {
        if (!preg_match("/1[34578]{1}\d{9}$/", trim($moblie))) {
            return false;
        } else {
            return true;
        }
    }

    public static function CheckRegUser($str) {
        $str = iconv('gbk', 'utf-8', $str);
        if (!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u", $str)) {
            return false;
        } else {
            return true;
        }
    }

    public static function CheckRegCompany($str) {
        $str = iconv('gbk', 'utf-8', $str);
        if (!preg_match("/[\x80-\xff]{6,30}/", $str)) {
            return false;
        } else {
            return true;
        }
    }

    public static function CheckRegEmail($email) {
        if (!preg_match('/^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/', $email)) {
            return false;
        } else {
            return true;
        }
    }

    public static function CheckIdCard($idcard) {
        if (!preg_match("/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/", $idcard)) {
            return false;
        } else {
            return true;
        }
    }

    public static function CheckTell($idcard) {
        if (preg_match("/\d{3}-\d{8}|\d{4}-\d{7}/", $idcard) == 0) {
            return false;
        } else {
            return true;
        }
    }
}
