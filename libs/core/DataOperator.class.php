<?php

/*
 * 文件：DataOperator.class.php
 * 说明:数据操作信息缓存
 * 作者：李光强
 * 时间：2017/11/22/
 */



require_once dirname(dirname(__FIle__)) . '/model/DataOperator.model.php';

/** 数据表操作缓存类
 * 
 *
 * @author admin
 */
class DataOperator {

    /**
     * 向session中存入信息
     */
    public static function saveSession($dp) {
        session_start();
        $save = serialize($dp);
        $_SESSION["_DATA_OP_"] = $save;
    }

    /**
     * 读取信息
     */
    public static function getSession() {
        session_start();
        if (isset($_SESSION["_DATA_OP_"])) {
            $my = unserialize($_SESSION["_DATA_OP_"]);
            return $my;
        } else
            return false;
    }

}
