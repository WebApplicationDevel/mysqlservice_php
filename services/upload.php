<?php

/* * **********************************************************************************
 * PHP文件：upload.php
 * 说      明：接收单文件上传并保存
 * 作      者：李光强
 * 时      间：2016/12/25/
 * *********************************************************************************** */
include_once dirname(dirname(__FILE__)) . "/config/config.php";

$php_path = dirname(dirname(__FILE__)) . '/';
//$php_url = dirname($_SERVER['PHP_SELF']);
//$php_url = str_replace("services", "", $php_url);

//文件保存目录路径
$save_path = $php_path . $_CONFIG["upload_path"];//"data/upload/";
//文件保存目录URL
//$save_url = $php_url . 'data/upload/';
$save_url =  $_CONFIG["upload_path"];  //'data/upload/';
//定义允许上传的文件扩展名
$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp',
    'swf', 'flv',
    'swf', 'flv', 'mp3', 'mp4', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb',
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'htm', 'html', 'txt',
    'zip', 'rar', 'gz', 'bz2', 'json', 'dwg', 'tif');
//最大文件大小
$max_size = $_CONFIG["upload_max_size"];
//$save_path = realpath($save_path) . '/';
//有上传文件时
if (empty($_FILES) === false) {
    //原文件名
    $file_name = $_FILES['uploadFile']['name'];
    //服务器上临时文件名
    $tmp_name = $_FILES['uploadFile']['tmp_name'];
    //文件大小
    $file_size = $_FILES['uploadFile']['size'];
    //存储路径
    if (isset($_POST['path'])) {
        $path = $_POST['path'];
        if (!empty($path)) {
            $save_path = $save_path . $path;
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            $save_url .= $path . "/";
        }
    }
    $save_path = realpath($save_path) . '/';
    //检查文件名
    if (!$file_name) {
        show_json(40001, "请选择文件");
        exit();
    }
    //检查目录
    if (@is_dir($save_path) === false) {
        show_json(40002, "上传目录不存在。");
        exit();
    }
    //检查目录写权限
    if (@is_writable($save_path) === false) {
        show_json(40003, "上传目录没有写权限。");
        exit();
    }
    //检查是否已上传
    if (@is_uploaded_file($tmp_name) === false) {
        show_json(40004, "上传失败。");
        exit();
    }
    //检查文件大小
    if ($file_size > $max_size) {
        show_json(40005, "上传文件大小不能超过100M。");
        exit();
    }
//	//检查目录名
//	$dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
//	if (empty($ext_arr[$dir_name])) {
//		show_json(false,"目录名不正确。");
//	}
    //获得文件扩展名
    $temp_arr = explode(".", $file_name);
    $file_ext = array_pop($temp_arr);
    $file_ext = trim($file_ext);
    $file_ext = strtolower($file_ext);
    //检查扩展名
    if (in_array($file_ext, $ext_arr) === false) {
        show_json(40006, "上传文件扩展名是不允许的扩展名。");
        exit();
    }
    //创建文件夹
//	if ($dir_name !== '') {
//		$save_path .= $dir_name . "/";
//		$save_url .= $dir_name . "/";
//		if (!file_exists($save_path)) {
//			mkdir($save_path);
//		}
//	}
    $ymd = date("Ym");  //date('Ymd');
    $save_path .= $ymd . "/";
    $save_url .= $ymd . "/";
    if (!file_exists($save_path)) {
        mkdir($save_path);
    }
    //新文件名
    $new_file_name = time() . '.' . $file_ext;
    //移动文件
    $file_path = $save_path . $new_file_name;
    if (move_uploaded_file($tmp_name, $file_path) === false) {
        show_json(40007, "上传文件失败。");
        exit();
    }
    @chmod($file_path, 0644);
    $file_url = $save_url . $new_file_name;

    header('Content-type: text/html; charset=UTF-8');
    //echo "SUCCESS:";
//	$json = new Services_JSON();
    echo show_json(true, '',$file_url);
    exit;
}
 /**
     * 向客户端发送数据结果jsonÏ
     * @param int $code 状态码，0--正常，1--出错
     * @param int $count 当前查询记录总数，用于客户端分页
     * @param type $data 当前查询数据集
     * @param type $msg 提示信息，正常时为空，出错时返回出错信息
     */
    function show_json($code = 0,$msg="", $data = '') {
        if($code===true) $code=0;
        else if($code===false) $code=1;
        
        $result =array( "code"=>$code,"count"=> $count,"msg"=> $msg,"data"=>$data); 
        //$result=urlencode($result);
        header('content-type:application:json;charset=utf-8');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $return = json_encode($result);
        echo $return;
        //exit;
    }

?>