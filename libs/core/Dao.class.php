<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once dirname(dirname(__FILE__))."/mysql/MyPdo.class.php";

/**
 * 数据访问类
 *
 * @author Administrator
 */
class Dao extends MyPDO{
    
    /**
     * 获取单条记录
     * @param type $table 数据表
     * @param type $columns 字段列表
     * @param type $where 条件 
     * @return boolean
     */
    public function getModel($table,$columns,$where)
    {
        $data=$this->select($table, $columns, $where);
        if(is_array($data) && count($data)>0)
            return $data[0];
        else
            return false;
    }
   
    /** 
     * 添加/更新数据
     * @param string $table 数据表
     * @param array $data 数据集
     * @return type
     */
    public function merge($table,$data)
    {
        $id=null;
        if(!is_array($data))
            $data=(array)$data;
        
        //先检查是否有id，如果有则将id作为主码，否则检查uid
        if(isset($data["id"]))
            $id= $data["id"];
       
            
    if($id==null || $id==0)  //添加
        {
            return $this->insert($table,$data);
        }
        else if($id!=null && $id>0)//更新
        {
            return $this->update($table,$data);
        }
        else
            return -1;
    }
}
