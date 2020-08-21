<?php

/*
 * 文件：public.function.php
 * 说明：通常函数库
 * 作者：job-hunter团队
 * 时间：2017/10/24/
 * 
 */

/**
 * 清除客户POST和GET清求缓存
 */
function clearRequestData() {
    if (count($_POST) > 0) {
        $_POST = array();
    }

    if (count($_GET) > 0) {
        $_GET = array();
    }
}

/**
 * 在web前端显示消息框,示例:msg("falied","/",10)或 msg("ok");
 * @param string $message 要显示的消息
 * @param string $url 消息显示完成以后自动跳转的URL
 * @param int $time 消息框显示的时长
 */
function show_msg($message, $url = null, $time = 5) {
    echo<<<END

	<style>
	#msgbox{width:400px;border: 1px solid #ddd;font-family:微软雅黑;color:888;font-size:13px;margin:0 auto;margin-top:150px;}
	#msgbox #title{background:#3F9AC6;color:#fff;line-height:30px;height:30px;padding-left:20px;font-weight:800;}
	#msgbox #message{text-align:center;padding:20px;}
	#msgbox #info{height:50px;line-height:50px;text-align:center;padding:5px;border-top:1px solid #ddd;background:#f2f2f2;color:#888;}
	</style>

	<div id="msgbox">
	<div id="title">提示信息</div>
	<div id="message">$message</div>
	<div id="info"><span id="time" style="color:#f00">$time</span> 秒后自动跳转，如不想等待可 <a href='$url'>点击这里</a></div></center>

END;
    echo<<<END
             <script>
                setInterval(ChangeTime, 1000);

                function ChangeTime() {
                  var time;
                  time = $("#time").text();
                  time = parseInt(time);
                  time--;
                  if (time <= 0) {
END;
    if ($url)
        echo 'location.href="' . $url . '";';
    else
        echo "history.back()";
    echo<<<END
                  } else {
                    $("#time").text(time);
                  }
                }
             </script>
END;
    exit;
}

/**
 * 把数组生成字符串
 */
function ArrayToString($obj, $withKey = true, $two = false) {
    if (empty($obj))
        return array();
    $objType = gettype($obj);
    if ($objType == 'array') {
        $objstring = "array(";
        foreach ($obj as $objkey => $objv) {
            if ($withKey)
                $objstring .= "\"$objkey\"=>";
            $vtype = gettype($objv);
            if ($vtype == 'integer') {
                $objstring .= "$objv,\r\n";
            } else if ($vtype == 'double') {
                $objstring .= "$objv,\r\n";
            } else if ($vtype == 'string') {
                $objv = str_replace('"', "\\\"", $objv);
                $objstring .= "\"" . $objv . "\",\r\n";
            } else if ($vtype == 'array') {
                $objstring .= "" . ArrayToString($objv, $withKey, $two) . ",\r\n";
            } else if ($vtype == 'object') {
                $objstring .= "" . ArrayToString($objv, $withKey, $two) . ",\r\n";
            } else {
                $objstring .= "\"" . $objv . "\",\r\n";
            }
        }
        $objstring = substr($objstring, 0, -1) . "";
        return $objstring . ")\n";
    }
}

/**
 * 将数据中所有非打印字符去除
 * @param type $data
 * @return type
 */
function post_trim($data) {
    foreach ($data as $d_k => $d_v) {
        if (is_array($d_v)) {
            $data[$d_k] = $this->post_trim($d_v);
        } else {
            $data[$d_k] = trim($data[$d_k]);
        }
    }
    return $data;
}

/**
 * 获取客户端IP
 * @return type
 */
function getClientIp() {
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else
    if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else
    if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
        $ip = getenv("REMOTE_ADDR");
    } else
    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = "unknown";
    }
//    $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
//    if (preg_match($preg, $ip)) {
    return ($ip);
//    }
}

/**
 * 获取上传图片
 * @param type $content
 * @param type $count
 * @return type
 */
function getUploadPic($content, $count = 0) {
    $content = str_replace('"', '', $content);
    $content = str_replace('\'', '', $content);
    $content = str_replace('>', ' width="">', $content);
    $pattern = preg_match_all('/<img[^>]+src=(.*?)\s[^>]+>/im', $content, $match);
    if ($match[1]) {
        if ($count > 0) {
            $i = 0;
            foreach ($match[1] as $v) {
                if (!empty($v)) {
                    $pic[] = $v;
                    $i++;
                    if ($i >= $count) {
                        break;
                    }
                }
            }
            return $pic;
        }
        return $match[1];
    }
    return array();
}

/**
 * ??
 * @param type $default
 * @return type
 */
function dreferer($default = '') {
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (strpos('a' . $referer, Url('user', 'login'))) {
        $referer = $default;
    } else {
        $referer = substr($referer, -1) == '?' ? substr($referer, 0, -1) : $referer;
    }
    return $referer;
}

/**
 * 获取文件信息
 * @param type $file_path
 * @return boolean|int
 */
function file_mode_info($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    $mark = 0;
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        $test_file = $file_path . '/cf_test.txt';

        if (is_dir($file_path)) {

            $dir = @opendir($file_path);
            if ($dir === false) {
                return $mark;
            }
            if (@readdir($dir) !== false) {
                $mark ^= 1;
            }
            @closedir($dir);
        }
    }
    return $mark;
}

/**
 * 获取周边坐标范围
 * @param type $lat
 * @param type $lon
 * @param type $raidus
 * @return type
 */
function getAround($lat, $lon, $raidus) {
    $PI = 3.14159265;
    $latitude = $lat;
    $longitude = $lon;
    $degree = (24901 * 1609) / 360.0;
    $raidusMile = $raidus;
    $dpmLat = 1 / $degree;
    $radiusLat = $dpmLat * $raidusMile;
    $minLat = $latitude - $radiusLat;
    $maxLat = $latitude + $radiusLat;
    $mpdLng = $degree * cos($latitude * ($PI / 180));
    $dpmLng = 1 / $mpdLng;
    $radiusLng = $dpmLng * $raidusMile;
    $minLng = $longitude - $radiusLng;
    $maxLng = $longitude + $radiusLng;
    return array($minLat, $maxLat, $minLng, $maxLng);
}

/**
 * 用户代理 
 * @return boolean
 */
function UserAgent() {
    $user_agent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
    if ((preg_match("/(iphone|ipod|android)/i", strtolower($user_agent)))
            AND strstr(strtolower($user_agent), 'webkit')) {
        return true;
    } else if (trim($user_agent) == ''
            OR preg_match("/(nokia|sony|ericsson|mot|htc|samsung|sgh|lg|philips|lenovo|ucweb|opera mobi|windows mobile|blackberry)/i", strtolower($user_agent))) {
        return true;
    } else {
        return true;
    }
}

/**
 * 写入缓存文件
 * @param type $dir
 * @param type $array
 * @param type $config
 * @return type
 */
function made_web($dir, $array, $config) {
    $content = "<?php \n";
    $content .= "\$$config=" . $array . ";";
    $content .= " \n";
    $content .= "?>";
//    $fpindex = @fopen($dir, "w+");
//    @fwrite($fpindex, $content);
//    @fclose($fpindex);
    if (is_file($dir)) {  //如果文件存在则删除之
        @unlink($dir);
    }
    $fw = file_put_contents($dir, $content);
    return $fw;
}

/**
 * web前端缓存
 * @param type $dir
 * @param type $array
 * @return type
 */
function made_web_array($dir, $array) {
    $content = "<?php \n";
    if (is_array($array)) {
        foreach ($array as $key => $v) {
            if (is_array($v)) {
                $content .= "\$$key=array(";
                $content .= made_string($v);
                $content .= ");";
            } else {
                $v = str_replace("'", "\\'", $v);
                $v = str_replace("\"", "'", $v);
                $v = str_replace("\$", "", $v);
                $content .= "\$$key=" . $v . ";";
            }
            $content .= " \n";
        }
    }
    $content .= "?>";
    //$fpindex = @fopen($dir, "w+");
    //$fw = @fwrite($fpindex, $content);
    //@fclose($fpindex);
    if (is_file($dir)) {  //如果文件存在则删除之
        @unlink($dir);
    }
    $fw = file_put_contents($dir, $content);
    return $fw;
}

/**
 * 删除文件或目录
 * @param type $delfiles
 * @return boolean
 */
function delfiledir($delfiles) {
    $delfiles = stripslashes($delfiles);
    $delfiles = str_replace("../", "", $delfiles);
    $delfiles = str_replace("./", "", $delfiles);
    $delfiles = "../" . $delfiles;
    $p_delfiles = path_tidy($delfiles);
    if ($p_delfiles != $delfiles) {
        die;
    }
    if (is_file($delfiles)) {
        @unlink($delfiles);
    } else {
        $dh = @opendir($delfiles);
        while ($file = @readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $delfiles . "/" . $file;
                if (@is_dir($fullpath)) {
                    delfiledir($fullpath);
                } else {
                    @unlink($fullpath);
                }
            }
        }
        @closedir($dh);
        if (@rmdir($delfiles)) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 删除图片
 * @param type $pic
 */
function unlink_pic($pic) {

    $pictype = getimagesize($pic);
    if ($pictype[2] == '1' || $pictype[2] == '2' || $pictype[2] == '3') {
        @unlink($pic);
    }
}

function pylode($string, $array) {

    if (is_array($array)) {
        $str = @implode($string, $array);
    } else {
        $str = $array;
    }

    if (!preg_match("/^[0-9a-zA-Z" . $string . "]+$/", $str)) {
        $str = 0;
    }
    return $str;
}

/** 写入系统日志文件 * */
function write_log($log) {
//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个
    $file = ROOT_PATH . '/log/log-' . date("Y-m-d") . '.log';

    date_default_timezone_set("Asia/Shanghai");
//echo "当前时间是 " . date("Y-m-d h:i:sa");
    $log = iconv("gbk", "utf-8", $log);
    file_put_contents($file
            , date("Y-m-d h:i:sa") . "    " . $log . "\r\n"
            , FILE_APPEND);
}

//<editor-fold defaultstate="collapsed" desc="SQL语句处理函数">

function FormatOptions($Options) {
    if (!is_array($Options)) {
        return array('field' => '*', 'where' => '');
    }
    $WhereStr = '';
    if ($Options['field']) {
        $Field = $Options['field'];
        unset($Options['field']);
    } else {
        $Field = '*';
    }
    if ($Options['special']) {
        $special = $Options['special'];
        unset($Options['special']);
    }
    if ($Options['groupby']) {
        $WhereStr .= ' group by ' . $Options['groupby'];
    }
    if ($Options['orderby']) {
        $WhereStr .= ' order by ' . $Options['orderby'];
    }

    if ($Options['desc']) {
        $WhereStr .= " " . $Options['desc'];
    }
    if ($Options['limit']) {
        $WhereStr .= ' limit ' . $Options['limit'];
    }
    return array('field' => $Field, 'order' => $WhereStr, "special" => $special);
}

function FormatWhere($Where) {
    $WhereStr = '1';
    foreach ($Where as $k => $v) {
        if (is_numeric($k)) {
            if ((substr(trim($v), 0, 3) == 'and') || (substr(trim($v), 0, 2) == 'or')) {
                $WhereStr .= ' ' . $v;
            } elseif ($v) {
                $WhereStr .= ' and ' . $v;
            }
        } else {
            if (strpos($k, '<>') > 0) {
                $position = strpos($k, '<>');
                $fieldName = trim(substr($k, 0, $position));
                $WhereStr .= ' and `' . $fieldName . '` <> \'' . $v . '\'';
            } elseif (strpos($k, '<') > 0) {
                $position = strpos($k, '<');
                $fieldName = trim(substr($k, 0, $position));
                $WhereStr .= ' and `' . $fieldName . '` < \'' . $v . '\'';
            } elseif (strpos($k, '>') > 0) {
                $position = strpos($k, '>');
                $fieldName = trim(substr($k, 0, $position));
                $WhereStr .= ' and `' . $fieldName . '` > \'' . $v . '\'';
            } else {
                $WhereStr .= ' and `' . $k . '`=\'' . $v . '\'';
            }
        }
    }
    return $WhereStr;
}

function FormatValues($Values) {
    $ValuesStr = '';
    foreach ($Values as $k => $v) {
        if (preg_match("/^[a-zA-Z0-9_]+$/", $k)) {
            if (preg_match('/^[0-9]+$/', $k)) {
                $FiledList = @explode(',', $v);
                if (is_array($FiledList)) {
                    foreach ($FiledList as $Fv) {
                        $FvList = @explode('=', $Fv);
                        if ($FvList[1]) {
                            if (strpos($FvList[1], '+') > 0) {
                                $FiledV = @explode('+', $FvList[1]);
                                $ValuesStr .= ',`' . str_replace("`", '', $FvList[0]) . '`=`' . str_replace("`", '', $FiledV[0]) . '`+\'' . intval(str_replace("'", '', $FiledV[1])) . '\'';
                            }
                            if (strpos($FvList[1], '-') > 0) {
                                $FiledV = @explode('-', $FvList[1]);
                                $ValuesStr .= ',`' . str_replace("`", '', $FvList[0]) . '`=`' . str_replace("`", '', $FiledV[0]) . '`-\'' . intval(str_replace("'", '', $FiledV[1])) . '\'';
                            }
                        }
                    }
                }
            } else {
                $ValuesStr .= ',`' . $k . '`=\'' . $v . '\'';
            }
        }
    }
    return substr($ValuesStr, 1);
}

//</editor-fold>
?>