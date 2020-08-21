<?php

/*
 * 文件：loginService.php
 * 说明：登录服务
 * 作者：李光强
 * 时间：2017/10/31/
 * 
 */
include_once 'config.php';

header('Access-Control-Allow-Origin:*');
session_start(); 
include_once (LIB_SERVICE_PATH.'loginHelper.php');
//利用loginHelper类实现登录检测
$lh=new loginHelper();
$lh->login();
