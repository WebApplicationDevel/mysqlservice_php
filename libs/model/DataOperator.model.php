<?php

/*
 * 文件：DataOperator.model.php
 * 说明：当前要操作的数据信息
 * 作者：李光强
 * 时间：2017/11/26/
 * 备注：为了提升前端操作的安全性，所有用户即将对数据库的操作都缓存到此实体类中，
 *      不需要前端传递相关参数。
 */

/**
 * 当前要操作的数据信息
 *
 * @author admin
 */
class DataOperatorModel {
    
    /**
     * 数据表
     * @var type 
     */
    public $table=false;
    /**
     * 主码字段
     * @var type 
     */
    public $key=false;
    /**
     * 主码值
     * @var type 
     */
    public $keyValue=false;
    /**
     * 操作类型:GET_MODEL/GET_LIST/MERGE/DELETE
     * @var DataOpEnum 
     */
    public $op=FlagEnum::OP_GET_LIST;
    
    /**
     * 构造函数
     * @param string $table 数据表名
     * @param string $key 主码名称
     * @param string $kv 主码值
     * @param int $op 操作码，0--选择，1--插入或更新，2--删除
     */
    public function __construct($table=false,$key=false,$kv=false,$op=false) {
        $this->table=$table;
        $this->key=$key;
        $this->keyValue=$kv;
        $this->op=$op;
    }
    
}
