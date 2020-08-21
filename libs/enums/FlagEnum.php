<?php

include_once dirname(__FILE__)."/EnumAbstract.php";

class FlagEnum extends EnumAbstract {

    const SUCCESS = "SUCCESS";
    const FAILED= "FAILED";

    /** 常用错误提示 **/
    const ERROR_EXITS_NAME          ="名称已存在";
    const ERROR_EXITS_TITLE         ="标题已存在";
    const ERROR_EXCEPTION           ="未知错误";
    const ERROR_UnvalidateParameters="参数不正确";
    const ERROR_VERIFY_CODE         ="验证码错误";
    const ERROR_USER_PASSWORD       ="用户名称或密码错误";
    const ERROR_NO_SQL              ="没有SQL语句";
    const ERROR_NO_OP               ="缺少操作码";
    const ERROR_NO_TABLE            ="缺少数据表名";
    const ERROR_NO_TOKEN            ="缺少令牌或令牌错误";


    /*--------- 操作码 ------------------*/
    /** 获取验证码及图片 **/
    const OP_GET_CHECKCODE="GET_CHECKCODE";
    /** 添加操作符 **/
    const OP_ADD="ADD";
    /** 添加/更新记录 **/
    const OP_MERGE="MERGE";
    /** 删除操作符 **/
    const OP_DELETE="DELETE";
    /** 更新操作符 **/
    const OP_UPDATE="UPDATE";
    /** 获取记录数目 **/
    const OP_GET_COUNT="GET_COUNT";
    /** 获取实体JSON **/
    const OP_GET_MODEL="GET_MODEL";
    /** 获取实体集json **/
    const OP_GET_LIST="GET_LIST";
    /** 获取页集合json **/
    const OP_GET_PAGE="GET_PAGE";
    /** 获取Table所需json **/
    const OP_GET_TABLE_JSON="GET_TABLE_JSON";
    /** 获取ztree所需json **/
    const OP_GET_ZTREE_JSON="GET_ZTREE_JSON";
    /**  获取菜单ztree数据 */
    const OP_GET_MENU_ZTREE="GET_MENU_ZTREE";
    /** 执行SQL,返回影响行数 **/
    const OP_EXECSQL="EXECUTE_NATIVESQL";
    /** 执行SQL,返回影响行数 **/
    const OP_CALL_STORAGE="CALL_STORAGE";
    /** 执行SQL,返回数据集 **/
    const OP_QUERYSQL="EXECUTE_QUERY";
    /** 执行PDFTOPNG **/
    const OP_PDFTOPNG="EXECUTE_PDFTOPNG";
    /** 同步微企通讯录*/
    const OP_SYNC_USER="SYNC_USER";
    /** 创建考勤二维码 */
    const OP_CREATE_QRCODE="CREATE_QRCODE";
    /** 设置阅读次数 */
    const OP_SET_BROWSE_TIME="SET_BROWSE_TIME";
    /** 获取部门用户ztree json数据 */
    const OP_GET_DEPARTMENT_USER_ZTREE="GET_DEPARTMENT_USER_ZTREE";
    
    /////////APP 请求参数 ////////////////
    /** 参与活动 **/
    const OP_ADD_ACTIVITY="ADD_ACTIVITY";
    /** 留言 **/
    const OP_ADD_BBS ="ADD_BBS";

    /*-------------------常用GET/POST参数--------*/
    /** 请求类型**/
    const REQUEST_METHOD="REQUEST_METHOD";
    /** 令牌 */
     const PARAMETER_TOKEN="TOKEN";
    /** 操作码 **/
    const PARAMETER_OPERATOR="OP";
    /** SQL语句 **/
    const PARAMETER_SQL="SQL";
    /** 存储过程名称 **/
    const PARAMETER_STORAGE="STORAGE";
    /** 参数 **/
    const PARAMETER_PARAM="PARAM";
    /** 表名 **/
    const PARAMETER_TABLE="TABLE";
    /** 数据 */
    const PARAMETER_DATA="DATA";
    /** ajax需要返回的数据 */
    const PARAMETER_RETURN="RETURN";
    /** 字段列表 **/
    const PARAMETER_COLUMNS="COLUMNS";
    /** 查询条件 */
    const PARAMETER_WHERE="WHERE";
    /** 排序字段 */
    const PARAMETER_ORDERFIELD="ORDERFIELD";
    /** 分页序号 **/
    const PARAMETER_PAGEINDEX="PAGE";
    /** 分页大小 **/
    const PARAMETER_PAGESIZE="LIMIT";
    
    /** where语句连接符**/
    const PARAMETER_CONJUNTION="conjuntion";
/** 角色id**/
    const PARAMETER_ROLEID="ROLEID";
    /** PDF文件  */
    const PARAMETER_PDF="PDF";
    /** 已选中的ids  */
    const PARAMETER_SELECTEDIDS="SELECTEDIDS";
    /** ID字段名 */
    const PARAMETER_ID="id";

    //-------------------- 常用标识符 --------------/
    /** 系统名称标识 **/
    const FLAG_SYS_NAME="";
    /** 系统开发单位标识 **/
    const FLAG_SYS_POWERED="";
    /** 系统版本标识 **/
    const FLAG_SYS_VERSION="1.0 Builder20170408";
    /**验证码标识 **/
    const FLAG_VERIFY_CODE="VERIFY_CODE";
    /** 用户名称标识 **/
    const FLAG_SYS_USER="SYS_USER";
    /** 用户部门标识 **/
    const FLAG_SYS_DEPARTMENT="SYS_DEPARTMENT";
    /** 当前登录状态 **/
    const FLAG_SYS_LOGINED="SYS_LOGINED";
    /** 当前会话时间 */
    const FLAG_SESSION_LAST_ACCESS="SESSION_LAST_ACCESS";
    
    

}