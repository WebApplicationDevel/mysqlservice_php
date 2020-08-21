<?php

/* 
 * 文件：codeService.php
 * 说明：代码服务
 * 作者：李光强
 * 时间：2017/11/27/
 */

include_once ('config.php');
include_once (CACHE_PATH.'code.cache.php');

$requestData = ($_SERVER [FlagEnum::REQUEST_METHOD] === "GET" ? $_GET : $_POST);
if(isset($requestData["CODE_TYPE"]))
{
    $op=$requestData["CODE_TYPE"];
    if(array_key_exists($op, $code))
    {
        echo json_encode($code[$op]);
        return;
    }
}

echo "";





