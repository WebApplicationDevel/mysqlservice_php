<?php

/*
 * 文件：LoginedUser.class.php
 * 说明：登录用户信息类
 * 作者：李光强
 * 时间：2017/11/14/
 */

/**
 * 当前登录用户类
 */
class LoginedUser {
    
    /**
     *用户id
     * @var type 
     */
    public $id;
    /**
     *用户登录帐号
     * @var type 
     */
    public $account;
    /**
     *用户真实姓名
     * @var type 
     */
    public $user_name;
    /**
     *用户角色id,多个角色使用逗号分隔
     * @var type 
     */
    public $role_id;
    
    /**
     *角色名称,多个角色使用逗号分隔
     * @var type 
     */
    public $role_name;
    
    /**
     *所在部门id
     * @var type 
     */
    public $dept_id;
    
    /**
     *所在部门名称 
     * @var type 
     */
    public $dept_name;
        
    /**
     * 构造函数
     * @param type $id 用户id
     * @param type $user_name 用户姓名 
     * @param type $account 登录帐号
     * @param type $role_id 角色id
     * @param type $role 角色名
     * @param type $dept_id 部门id
     * @param type $dept_name 部门名称 
     */
    public function __construct(
             $id=null
            ,$user_name=null
            ,$account=null
            ,$role_id=null
            ,$role=null
            ,$dept_id=null
            ,$dept_name=null) {
        $this->id=$id;
        $this->user_name=$user_name;
        $this->account=$account;
        $this->role_id=$role_id;
        $this->role_name=$role;
        $this->dept_id=$dept_id;
        $this->dept_name=$dept_name;
    }
    
}
