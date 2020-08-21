<?php
/*******************************************************
 * PHP文件：config.php
 * 说   明：系统配置参数
 * 作   者：LYP
 * 时   间：2016.11.8.
 ******************************************************/

@date_default_timezone_set(@date_default_timezone_get());
@set_time_limit(6000);//10min pathInfoMuti,search,upload,download...
@ini_set('session.cache_expire',6000);
@ini_set("display_errors","off");
//@error_reporting(E_ERROR|E_WARNING|E_PARSE);
@error_reporting(E_ALL);

define('APP_PATH',        (dirname(__FILE__)).'/');
define('CONFIG_PATH',     APP_PATH.'config/'); 
define('LIB_PATH',        APP_PATH.'libs/');
define('CORE_PATH',       LIB_PATH.'core/');
define('ENUMS_PATH',      LIB_PATH.'enums/');
define('SERVICE_PATH',   APP_PATH.'services/');

/******常用文件 *************************/
//include_once(BASIC_PATH."EnumAbstract.php");
include_once(ENUMS_PATH.'FlagEnum.php');
include_once(LIB_PATH.'public.function.php');
include_once(CORE_PATH.'Dao.class.php');
include_once(CONFIG_PATH."config.php");

$dao=new Dao();

/** 是否需要检查TOKEN **/
define("TOKEN", "d250c1fa727d1e0bae115845e9643247");
