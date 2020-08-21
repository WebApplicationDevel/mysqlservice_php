<?php
//是否为调试模式
$_DEBUG=true;
/**
 * 系统配置参数
 */
$sys_config = array(
    
    //访问服务的TOKEN
    "service_token"=>"12345678",
    //是否跨域
    "cross_domain" => "1",
   
    
    //网站上传目录
    "upload_path" => "data/upload/",
    //允许上传的文件类型
    "file_type"=>array('gif', 'jpg', 'jpeg', 'png', 'bmp',
    'swf', 'flv',
    'swf', 'flv', 'mp3', 'mp4', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb',
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'htm', 'html', 'txt',
    'zip', 'rar', 'gz', 'bz2', 'json', 'dwg', 'tif')
    
  );


/**
 * 数据库连接配置
 */
 
  $db_config = array(
      'dbtype'=>'mysql',                        		//数据库类型
      'dbhost'=>'localhost:3306',               		//数据库服务器及端口
      'dbuser'=>'root',                         		//连接帐号
      'dbpass'=>'root',                  						//连接密码
      'dbname'=>'test',                         		//数据名称
      'def'=>'',                                 		//数据表前缀
      'charset'=>'utf8',                          	//数据库编码
      'timezone'=>'PRC',                            //时区
      'coding'=>'e1262e01f5ce075e2df2e433f23c0b4e', //生成cookie加密
    );
  