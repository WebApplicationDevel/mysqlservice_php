<?php

/* * **************************************************************
 * PHP文件：myService.php
 * 说   明：信息服务主入口
 * 作   者：LYP
 * 时   间：2016.11.12
 * ************************************************************* */

//echo "Service";
include_once (dirname(__FILE__) . '/init.php');
include_once (dirname(__FILE__) . "/libs/core/LoginedInfo.class.php");
header('Access-Control-Allow-Origin:*');

$requestData = ($_SERVER ["REQUEST_METHOD"] === "GET" ? $_GET : $_POST);
$s = "dao";
$user=null;

if (isset($requestData["s"]))
    $s = $requestData["s"];



switch ($s) {
    case "dao":
        require_once (dirname(__FILE__) . '/' . 'services/DaoService.php');
        $service = new DaoService();
        $service->run();
        break;
    case "app_login":
    case "login":  //登录服务       
        require_once (dirname(__FILE__) . '/' . 'services/loginHelper.php');
        //利用loginHelper类实现登录检测
        $lh = new loginHelper();
        $lh->login();
        break;
     case "logout":  //注销服务       
        if (!isset($requestData["UID"])) {
            echo json_encode(array("code" => 1011, "msg" => "缺少GUID"));
            die();
        }
        $guid = $requestData["UID"];
        loginedInfo::logou($guid);
        echo json_encode(array("code" => 0, "msg" => "注销成功"));
        break;
    case "getLoginInfo"://获取登录信息
        if (!isset($requestData["UID"])) {
            echo json_encode(array("code" => 1011, "msg" => "缺少GUID"));
            die();
        }
        $guid = $requestData["UID"];
        $user = LoginedInfo::getLoginedUser($guid);
        if(!empty($user))
            echo json_encode($user);
        else
            echo '';
        break;
    case "authcode":    //生成验证码图片
        //include(dirname(__FILE__) . "/config/config.php");
        require_once(dirname(__FILE__) . "/libs/core/Verify.class.php");
        $capth = new verify($config['code_width'], $config['code_height'], $config['code_strlength'], $config['code_filetype'], $config['code_type']);
        $capth->entry();
        break;
    //读取json文件
    case "getJson":
        if(!isset($requestData["FILE"]))
        {
            die(json_encode(array("code" => 2001, "msg" => "缺少JSON文件名称")));
        }
          $json_string = file_get_contents(dirname(__FILE__).'/config/'.$requestData["FILE"].".json");
          if(!$json_string)
              echo json_encode(array("code"=>2002,"msg"=>"JSON文件不存在","data"=>""));
          else
            echo json_encode(array("code"=>0,"data"=>$json_string));
        break;
        //获取手机参数
    case "getAppConfig":
        global $APP_CONFIG;
        echo json_encode(array("code"=>0,"data"=>$APP_CONFIG,"time"=>time()));
        break;
     case "getMapConfig":
        global $APP_MAP;
        echo json_encode(array("code"=>0,"data"=>$APP_MAP));
        break;
    case "getGpsConfig":
        global $APP_GPS_OPTIONS;
        echo json_encode(array("code"=>0,"data"=>$APP_GPS_OPTIONS));
        break;
    case "getForm":
        require_once (dirname(__FILE__) . '/' . 'services/FormService.php');
        FormService::createForm();
        break;
}

/**
 * 检查是否登录，如果登录则返回当前登录用户，否则返回null
 * @global type $requestData
 * @return type
 */
function isLogined() {
    global $requestData;
    if (!isset($requestData["UID"])) {
        die(json_encode(array("code" => 1011, "msg" => "缺少GUID")));
    }

    $guid = $requestData["UID"];
    $user = LoginedInfo::getLoginedUser($guid);
    return $user;
}
