<?php

/*
 * 文件：LoginedInfo.class.php
 * 说明:登录信息助手类
 * 作者：李光强
 * 时间：2017/11/15/
 */


include_once(dirname(dirname(__FILE__)) . '/model/Session.model.php');
require_once dirname(dirname(__FIle__)) . '/model/LoginedUser.model.php';

/** 登录信息助手类
 * 
 *
 * @author admin
 */
class LoginedInfo {

    /**
     * 向session中存入登录用户信息
     */
    public static function saveSession($guid, $user) {
        $saveUser = serialize($user);
        global $config;
        if ($config["cross_domain"]==1) {
            $session = new Session($guid, "_LOGINED_USER_", $saveUser);
            $session->cleanup();    //清除以前会话
            $session->save();
        } else {
            session_start();
            $_SESSION["_LOGINED_INFO_"] = $saveUser;
        }
    }
    
    /** 当前所有会话 */
    public static function logou($guid)
    {
         global $config;
        if ($config["cross_domain"]==1) {
            $session = new Session($guid, "_LOGINED_USER_", $saveUser);
            $session->cleanup();    //清除以前会话
        } else {
           session_destroy();
        }
    }

    /**
     * 读取登录用户信息
     */
    public static function getLoginedUser($guid) {
         global $config;
        if ($config["cross_domain"]==1) {
            $session = new Session($guid, "_LOGINED_USER_");
            $myUser = $session->get();
            if ($myUser) {
                $myUser = unserialize($myUser);
                return $myUser;
            } else
                return null;
        }
        else {
            session_start();
            if (isset($_SESSION["_LOGINED_INFO_"])) {
                $myUser = $_SESSION["_LOGINED_INFO_"];
                $myUser = unserialize($myUser);
                return $myUser;
            } else
                return null;
        }
    }

}
