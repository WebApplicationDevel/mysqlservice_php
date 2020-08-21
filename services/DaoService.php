<?php

include_once(dirname(dirname(__FILE__)).'/libs/enums/FlagEnum.php');
include_once(dirname(dirname(__FILE__)).'/libs/core/Dao.class.php');
include_once(dirname(dirname(dirname(__FILE__))) . "/config/config.php");

/* * **************************************************************
 * PHP文件：ServiceBase.php
 * 说   明：服务基类
 * 作   者：LYP
 * 时   间：2016.11.12
 * ************************************************************* */

class DaoService {
    //<editor-fold defaultstate="collaplsed" desc="成员变量">

    /** 数据访问类 * */
    protected $dao;

    /** 操作码 * */
    protected $op;

    /** id码 * */
    protected $id = null;

    /** 操作数据表 * */
    protected $table = null;

    /** 索引字段列表 */
    protected $columns = "*";
    
    /** 要存入的数据 */
    protected $data=null;

    /** 提交的查询条件 */
    protected $whereJson = null;

    /** 查询条件语句 * */
    protected $where = false;

    /** 排序字段 */
    protected $orderField = null;

    /** 分页序号 * */
    protected $pageIndex = 1;

    /** 分页大小 * */
    protected $pageSize = 1000000;

    /** 前端请求传递数据 */
    protected $requestData=false;
    
    /** 前端发来且需要返回的数据，用于前端响应处理结果
     *  例如，前端需要知道id=1的数据是否存入完成，则在处理完以后，
     *        需要将该id返回给ajax调用者
     * @var type 
     */
    protected $returnData=0;

    /** 会话令牌 * */
    protected $token="";
    /** 系统TOKEN */
    protected $TOKEN="";

    /** 要执行的SQL */
    protected $sql = "";

    //</editor-fold>

    /**
     * 构造函数
     */
    public function __construct() {
        global $sys_config;
        $this->TOKEN=$sys_config["service_token"];
    }

    public function __destruct() {
        $this->dao = null;
    }

    /**
     * 执行
     */
    public function run() {

        if (!$this->getParameter())
            exit;

        //跳过系统参数表查询
        if ($this->TOKEN!="") {
            if (!$this->checkToken()) {
                exit;
            }
        }

        switch ($this->op) {
            case FlagEnum::OP_GET_MODEL : // 获取列表json
                $this->getModel();
                break;
            case FlagEnum::OP_GET_LIST : // 获取列表json
                $this->getAll();
                break;
            case FlagEnum::OP_GET_PAGE : // 获取页面json
                $this->getPage();
                break;
            case FlagEnum::OP_MERGE : // 添加或更新
                $this->merge();
                break;
            case FlagEnum::OP_DELETE : // 删除
                $this->delete();
                break;
            case FlagEnum::OP_GET_COUNT://记录数目
                echo $this->count();
                break;
            case FlagEnum::OP_CALL_STORAGE: //执行存储过程
                $this->callStorage();
                break;
            //屏蔽危险的SQL语句操作
//            case FlagEnum::OP_EXECUTE_NATIVESQL:
//                $this->executeNativeSql();
//                break;
//            case FlagEnum::OP_EXECUTE_QUERY:
//                $this->executeQuery();
//                break;
            default :
                echo FlagEnum::ERROR_EXCEPTION;
                exit();
        }
    }

    /** 检查会话令牌 */
    private function checkToken() {
        //$des = new MyDES();
        //$key = trim($des->decrypt(TOKEN, $this->token));
        //echo $this->token;
        if ($this->token == $this->TOKEN)
            return true;
        else {
            //$code = 0, $count=0,$data = '',$msg=""
            $this->show_json(10003, 0,'',FlagEnum::ERROR_NO_TOKEN);
            return false;
        }
    }

    //<editor-fold defaultstate="collapsed" desc="请求数据预处理">
    /**
     * 读取参数
     * @return boolean
     */
    private function getParameter() {
        $this->op = '';
        $this->requestData = ($_SERVER [FlagEnum::REQUEST_METHOD] === "GET" ? $_GET : $_POST);
        $this->token= isset($this->requestData[FlagEnum::PARAMETER_TOKEN])
                ?$this->requestData[FlagEnum::PARAMETER_TOKEN]:"";
        
        //$dc = DataOperator::getSession();
        if (!isset($this->requestData [FlagEnum::PARAMETER_OPERATOR])) {
            $this->show_json(10001,0,'', FlagEnum::ERROR_NO_OP);
            return false;
        }

        $this->op = $this->requestData [FlagEnum::PARAMETER_OPERATOR];
        //如果不是执行存储过程，则需要查询数据表名是否存在
        if ($this->op!=FlagEnum::OP_CALL_STORAGE) {
            if (!isset($this->requestData [FlagEnum::PARAMETER_TABLE])) { {

                    $this->show_json(10002,0,'', FlagEnum::ERROR_NO_TABLE);
                    return false;
                }
            }
            $this->table = $this->requestData [FlagEnum::PARAMETER_TABLE];
        } 
      

        if (isset($this->requestData [FlagEnum::PARAMETER_ID])) {
            $this->id = $this->requestData [FlagEnum::PARAMETER_ID];
        }
        
        //处理传过来的要存入数据库中的数据
        if (isset($this->requestData [FlagEnum::PARAMETER_DATA])) {
            $this->data =$this->requestData [FlagEnum::PARAMETER_DATA];
            if(gettype($this->data)=="string")
            { 
                $data=rawurldecode($this->requestData [FlagEnum::PARAMETER_DATA]);
                $this->data= json_decode($data);
            }
            else if(gettype($this->data)=="object")
                
            $this->data=(array)$this->data;
        }
        else if($this->op== FlagEnum::OP_MERGE)  //如果存入数据，则需要生成data
        {
            $flags=[];
            $flagObj=new FlagEnum();
            $flags=$flagObj->getConstList(true);
            
            $this->data=[];
           foreach($this->requestData as $key => $value){
                if(in_array($key, $flags) && $key!='id') continue;
                $this->data[$key]=rawurldecode($value);
            }
        }

        /** 处理需要返回 的数据标识 */
        if (isset($this->requestData [FlagEnum::PARAMETER_RETURN])) {
            $this->returnData=$this->requestData [FlagEnum::PARAMETER_RETURN];
        }
        if (isset($this->requestData [FlagEnum::PARAMETER_WHERE])) {
            $this->parseWhereClause();
        }
        if (isset($this->requestData [FlagEnum::PARAMETER_ORDERFIELD])) {
            $this->orderField = rawurldecode($this->requestData [FlagEnum::PARAMETER_ORDERFIELD]);
        }
        if (isset($this->requestData [FlagEnum::PARAMETER_COLUMNS])) {
            $this->columns = rawurldecode($this->requestData [FlagEnum::PARAMETER_COLUMNS]);
        }

        if (isset($this->requestData[FlagEnum::PARAMETER_PAGESIZE]))
            $this->pageSize = $_GET[FlagEnum::PARAMETER_PAGESIZE];
        if (isset($this->requestData[FlagEnum::PARAMETER_PAGEINDEX]))
            $this->pageIndex = $_GET[FlagEnum::PARAMETER_PAGEINDEX];        

        try {
            $this->dao = new Dao();
            return true;
        } catch (Exception $e) {
            $this->show_json(10002,0,'', $e->getMessage());
        }
    }

    /**
     * @param string $str unicode and ulrencoded string
     * @return string decoded string
     */
    private function utf8_urldecode($str) {
        $smth = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", rawurldecode($smth));
        $smth = html_entity_decode($smth, null, 'UTF-8');
        return $smth;
    }

    /**
     * 解析where语名
     * @param string $this->whereJson where的JSON
     */
    private function parseWhereClause() {
        $this->where = rawurldecode($this->requestData[FlagEnum::PARAMETER_WHERE]);
    }

    /**
     * 解析sql语名
     */
    private function parseSql() {
        $this->sql = rawurldecode($this->requestData[FlagEnum::PARAMETER_SQL]);
    }

    //</editor-fold>
    
    //<editor-fold defaultstate="collapse" desc="数据操作函数">
    private function count() {
        return $this->dao->count($this->table, $this->where);
    }

    private function delete() {
        if ($this->id != null)
            $this->where = "id=" . $this->id;
        else if ($this->uid != null)
            $this->where = "uid=" . $this->uid;
        else if ($this->where === "") {
            $this->show_json(20001,0,'', FlagEnum::ERROR_UnvalidateParameters);
        }


        $this->deleteByWhere();
    }

    private function deleteByWhere() {
        if ($this->where === "") {
            $this->show_json(20001,0,'', FlagEnum::ERROR_UnvalidateParameters);
        } else {
            $this->show_json($this->dao->delete($this->table, $this->where) > 0, "");
        }
    }

    private function exists() {
        echo $this->dao->exists($this->table, $this->where);
    }

    private function find($isEcho = true) {
        $data = $this->dao->select($this->table, $this->columns, $this->where, $this->orderField);
        if ($isEcho)
            $this->show_json(true, count ($data), $data);
        else
            return $data;
    }
    /** 执行存储过程 */
    private function callStorage() {
        if(!isset($this->requestData[FlagEnum::PARAMETER_STORAGE]))
        {
            $this->show_json(20002, 0,'',"存储过程名称不能为空");
            exit;
        } else {
            $sp="call ".$this->requestData[FlagEnum::PARAMETER_STORAGE];
            if(isset($this->requestData[FlagEnum::PARAMETER_PARAM]))
            {
                $sp=$sp."(".rawurldecode($this->requestData[FlagEnum::PARAMETER_PARAM]).")";
            }
            $i = $this->dao->execSql($sp);
            if($i)$this->show_json(true);
            else $this->show_json(20003,0,"",$i);
        }
    }
    /** 执行SQL返回影响记录数 */
    private function executeNativeSql() {
        if ($this->sql === "") {
            $this->show_json(20004, 0,'',"SQL语句为空");
            exit;
        } else {
            $i = $this->dao->execSql($this->sql);
            $this->show_json(true, $i);
        }
    }

    /** 执行SQL返回记录集 */
    private function executeQuery() {
        if ($this->sql == "") {
            $this->show_json(20004, 0,'',"SQL语句为空");
            exit;
        } else {
            $r = $this->dao->querySql($this->sql);
            $this->show_json(true, $r);
        }
    }

    /**
     * 生成TableJson
     * @return type
     */
    private function getTableJson() {
        $row_num = 0;
        $data = [];
        if ($this->pageIndex == null) {  //如果不分页
            $data = $this->dao->select($this->table, $this->columns, $this->where, $this->orderField);
            if ($data != null && count($data) > 0) {
                $row_num = count($data);
            }
        } else { //如果分页
            $data = $this->dao->selectPage($this->table, $this->columns, $this->pageIndex, $this->pageSize, $this->where, $this->orderField);
            $row_num = $this->count();
        }
        $result = array("total" => $row_num, "rows" => ($row_num > 0 ? $data : []));
        $this->toJson($result);
    }

    /**
     * 生成TableJson
     * @return type
     */
    private function getNativeJson() {
        $data = $this->dao->select($this->table, $this->columns, $this->where, $this->orderField);
        $this->toJson($data);
    }

    /**
     * 获取页面
     */
    private function getPage() {
        $data = $this->dao->selectPage($this->table
                , $this->pageIndex
                , $this->pageSize
                , $this->columns
                , $this->where
                , $this->orderField);

        $this->toJson($data);
    }

    private function getAll() {
         $data = $this->dao->select($this->table, $this->columns, $this->where, $this->orderField);
        if($data===null)
        {            
            $this->show_json(20010, 0, null,"查询出错，可能不存在要查询的数据表!");
        }
        else
        {
            $count=$this->count();
            $this->show_json(0, $count,$data);
        }
    }

    private function getModel() {
        $where = false;
        if ($this->id)
            $where = "id=" . $this->id;
        else if ($this->uid)
            $where = "uid=" . $this->uid;

        if ($this->where && $where)
            $where = $where . " AND " . $this->where;

        $data = $this->dao->select($this->table, $this->columns, $where);
        if (is_array($data))
            $data = $data[0];
        if($data)
            $this->show_json(true, 1,$data);
        else 
            $this->show_json (20011,0,'','没有相应记录');
    }

    private function merge() {
        $r = $this->dao->merge($this->table, $this->data);
        if ($r > 0) {
            $this->show_json(true,0, $r); //返回id
        } else if ($r <= 0) {
            $this->show_json(20021, 0,'',FlagEnum::ERROR_UnvalidateParameters);
        } else {                                                                                                                                                                                                      
            $this->show_json(20020, 0,'',FlagEnum::ERROR_EXCEPTION);
        }
    }

    //</editor-fold>

    //<editor-fold defaultstate="collaplsed" desc="向客户端发送结果">
    /**
     * 向客户端发送数据结果json
     * @param int $code 状态码，0--正常，1--出错
     * @param int $count 当前查询记录总数，用于客户端分页
     * @param type $data 当前查询数据集
     * @param type $msg 提示信息，正常时为空，出错时返回出错信息
     */
    private function show_json($code = 0, $count=0,$data = '',$msg="") {
        if($code===true) $code=0;
        else if($code===false) $code=1;
        
        $result =array( "code"=>$code,"count"=> $count,"msg"=> $msg
                ,"data"=>$data
                ,"return"=>$this->returnData); 
        //$result=urlencode($result);
        header('content-type:application:json;charset=utf-8');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $return = json_encode($result);
        echo $return;
        //exit;
    }

    private function toJson($data, $status = true) {

        $json = json_encode($data);
        //$result=array("status"=>$status,"info"=>$json);

        header('content-type:application:json;charset=utf-8');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        echo $json;
    }

    private function toPageJson($data, $count = null) {
        if (!$count)
            $count = count($data);
        $result = array("total" => $count,
            "rows" => $data);
        $json = json_encode($result);
        header('content-type:application:json;charset=utf8');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        echo $json;
    }
    //</editor-fold>
    
    //<editor-fold defaultstate="collaplsed" desc="ztree操作">
     /** 生成ZTree的json
     * 
     */
    public function getZtreeJson($selectedids) {
        $deptDao=new DepartmentDao();
        $departments=$deptDao->find("*","parentid<>0","orderinparent");
        $list = $this->find("*", null, "vip_order");
        $rows = array();
        $did = 0;
        
        foreach ($departments as $key => $val) {
            if ($did != $val ["id"]) {
                $did = $val ["id"];
                $children = $this->getChildrenForZtree($list, $did,$selectedids);
                if ($children != null) {
                    $row = array();
                    $row ["id"] = $did;
                    $row["userid"]=null;
                    $row["name"]=$val["departmentname"];                    
                    $row ["children"] = $children;
                    $row["isParent"] = true;
                    $rows [] = $row;
                }
            }
        }
        return $rows;
    }

    /** 遍历数据记录，获取子结点 * */
    private function getChildrenForZtree($data, $departmentId,$selectedids) {
        $rows = array();
        $i = 1000 * $departmentId;
        foreach ($data as $val) {
            if ($val ["departmentId"] === $departmentId) {
                if (isset($val ["name"])) {
                    $row = array();
                    $row["id"] = ($i++);
                    $row ["name"] = $val ["name"];
                    $row ["departmentname"] = $val ["departmentname"];
                    $row ["userid"] = $val ["userid"];
                    if($selectedids!=null)
                    {
                        $row["checked"]=(stripos($selectedids,$val["userid"])===false?false:true);
                    }
                    else
                        $row ["checked"]=false;
                    $rows [] = $row;
                }
            }
        }

        if (count($rows) > 0)
            return $rows;
        else
            return null;
    }

}   //</editor-fold>
