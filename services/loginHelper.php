<?php

include_once dirname(dirname(__FIle__)) . "/libs/core/LoginedInfo.class.php";
include_once dirname(dirname(__FIle__)) . "/libs/core/public.function.php";
include_once(dirname(dirname(__FILE__)) . '/libs/core/Dao.class.php');
include_once(dirname(dirname(__FILE__)) . '/libs/model/Session.model.php');

/**
 * 文件：loginHelper.php
 * 说明：登录助手
 * 作者：李光强
 * 时间：2017/10/31/
 */
class loginHelper {

    protected $session;
    
    protected $requestData;
    
    protected $guid;
    
     function __construct()
     {
        $this->requestData = ($_SERVER ["REQUEST_METHOD"] === "GET" ? $_GET : $_POST);        
     }
    
    /**
     * 登录
     */
    public function login() {
        if(!isset($this->requestData["UID"]))
        {
            $this->showInfo("用户ID错误！", 1000);
            return false;
        }
        
        $this->guid=$this->requestData["UID"];
        
        $account = false;      //用户名
        $password = false;       //用户密码
        $auth_code = false;       //验证码        

        if (isset($this->requestData["ACCOUNT"]))
            $account = $this->requestData["ACCOUNT"];
        else {
            $this->showInfo("请输入登录帐号！", 1001);
            return false;
        }

        if (isset($this->requestData["PASSWORD"]))
            $password = $this->requestData["PASSWORD"];
        else {
            $this->showInfo("请输入登录密码！", 1002);
            return false;
        }

        if (isset($this->requestData["AUTHCODE"]))
            $auth_code = $this->requestData["AUTHCODE"];
        else {
            $this->showInfo("请输入验证码！", 1003);
            return false;
        }

        $msg = $this->checkCode();
        if ($msg != "") {
            $this->showInfo($msg, 1005);
            return false;
        }

        global $dao,$APP_CONFIG;// = $this->session->dao; //利用已创建的连接 //new Dao( );
        $field = "id,user_name,account,password,salt,dept_name,dept_id,position,role_id,status,type";
        $where = "account='" . $account . "'";
        //如果使用手机验证码，则只能使用手机用户登录
        if($this->requestData["AUTHCODE"]==$APP_CONFIG["app_authcode"])
            $where=$where." AND role_id=1";
        
        $user = $dao->getModel("sys_user", $field, $where);

        if (is_array($user)) {
            $pass = md5($password . $user['salt']);
            if ($user['password'] != $pass) {
                $this->showInfo("密码不正确！", 1006);
                return false;
            }
            if ($user['status'] != 1) {
                $this->showInfo("您的账号已被锁定!", 1007);
                return false;
            }


            //将用户登录信息存入session
            $li = new LoginedUser(
                    $user["id"]
                    , $user["user_name"]
                    , $user["account"]
                    , $user["role_id"]
                    , $user["role_name"]
                    , $user["dept_id"]
                    , $user["dept_name"]);
            LoginedInfo::saveSession($this->guid,$li);

            //更新用户表中的登录信息
            $dao->update("sys_user", array("id" => $user["id"], "#last_login_time" => "NOW()", "#login_count"=>"login_count+1"));
            //写入登录日志
            $data = array("user_name" => $user["user_name"]
                , "user_id" => $user["id"]
                , "#op_time" => "now()"
                , "mod_name" => "系统登录"
                , "description" => "WEB系统登录"
                , "op_host" => getClientIp()); //获取客户IP
            $dao->insert("sys_log", $data);
            $this->showInfo('登录成功', 0, $li);
            return true;
        } else {
            $this->showInfo("用户名不存在", 1009);
            return false;
        }
    }

    /** 注销 */
    public function logout()
    {
        LoginedInfo::logou($this->guid);
        
        echo $this->showInfo('', 0);
    }
    
    /**
     * 检查当前用户是否登录，并向客户端发送JSON
     * @return boolean
     */
    public function checkLoginedInfo()
    {
         $loginedInfo = LoginedInfo::getLoginedUser($this->guid);
        
        if ($loginedInfo) {
            echo $this->showInfo('', 0, json_encode($loginedInfo));
            return true;
        }
        else
            echo $this->showInfo('没有登录', 1010);
    }

    /**
     * 获取当前用户登录信息
     * @return boolean
     */
    public function getLoginedInfo()
    {
         $loginedInfo = LoginedInfo::getLoginedUser($this->guid);
        
        return $loginedInfo;
    }
    
    /**
     * 检查验证码是否正确
     */
    protected function checkCode() {
        $code = $this->requestData["AUTHCODE"];
        global $config,$APP_CONFIG;
        if ($code==$APP_CONFIG["app_authcode"])//手机端使用固定的验证码
            return "";
        
        if ($config["cross_domain"] == 1) {
            $requestData = ($_SERVER ["REQUEST_METHOD"] === "GET" ? $_GET : $_POST);
            if (!isset($requestData["UID"])) {
                return "用户ID错误！";
            }
            $session = new Session($requestData["UID"], "_AUTH_CODE_");
            $data = $session->get();
            if (empty($data))
                return "非法登录";
            if ($data == $code)//手机端使用固定的验证码
                    return "";
            else
                    return "验证码不正确";
            return "";
        }
        else {

            session_start();
            if (isset($_SESSION["_AUTH_CODE_"])) {
                if ($_SESSION["_AUTH_CODE_"] == $code)
                    return "";
                else
                    return "验证码不正确";
            } else
                return "非法登录";
        }
    }

    /**
     * 返回登录结果 
     * @param type $msg 错误描述
     * @param type $url 跳转URL
     * @param type $code 错误代码,0--登录成功，1--登录失败
     */
    protected function showInfo($msg = '', $code = 1, $data = null) {
        echo json_encode(array("msg" => $msg, "code" => $code, "data" => $data));
        die;
    }

}
