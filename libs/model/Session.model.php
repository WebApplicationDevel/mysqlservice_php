<?php

include_once(dirname(dirname(__FILE__)).'/core/Dao.class.php');

/* 
 * PHP文件：Session.model.php
 * 说   明：数据库session实体
 * 作   者：李光强
 * 时   间：2018/5/9/
 */

class Session
{
    
    public $id,$guid,$key,$val;
    
    public $dao;
    
    function __construct($guid=null,$key=null,$val=null)
    {
        $this->guid=$guid;
        $this->key=$key;
        $this->val=$val;
        global $dao;
        $this->dao=$dao;//new Dao();
    }
    
    /** 保存SESSION */
    public function save()
    {        
        $data=array("guid"=>$this->guid,"key"=>$this->key,"value"=>$this->val
                ,'#expire_time'=>"ADDDATE(now(),INTERVAL 1 HOUR)");  //添加MYSQL#表示MYSQL函数或对象
        $this->dao->insert("sys_session", $data);
    }
    
    /** 读取未过时的SESSION */
    public function get()
    {       
        $data=$this->dao->select("sys_session"
                , "`value`"
                ,"`guid`='".$this->guid."' and `key`='".$this->key."' and expire_time>now()");
        if(count($data)>0)
            return $data[count($data)-1]["value"];  //返回最后一个记录
        return false;
    }
    
    /** 删除SESSION */
    public function delete()
    {
        $this->dao->delete("sys_session"
                , "`guid`='".$this->guid."' AND (`key`='".$this->key."' OR expire_time<now()");
    }
    
    /**
     * 清除所有会话
     */
    public function cleanup()
    {
        $this->dao->delete("sys_session"
                , "`guid`='".$this->guid."'");
    }
}