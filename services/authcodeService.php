<?php
/*
*  文件：authcode.php
 * 说明：验证码生成器
 * 作者：job-hunter团队
*  时间：2017/10/22/
*
* 
 */

error_reporting(0);

include(dirname(dirname(__FILE__))."/config/config.php");
include(dirname(dirname(__FILE__))."/libs/core/verify.class.php");

$capth = new verify($config['code_width'],
        $config['code_height'],
        $config['code_strlength'],
        $config['code_filetype'],
        $config['code_type']);

$capth->entry(); 

?>