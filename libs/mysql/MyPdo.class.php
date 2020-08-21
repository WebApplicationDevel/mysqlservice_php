<?php

/*
 * !
 * MyPdo database framework
 * 
 * Modified by Li Giangqiang, 2016/11/10/
 */

include_once(dirname(dirname(dirname(__FILE__))) . "/config/config.php");

/**
 * PDO框架
 */
class MyPDO {

    // General
    public $database_type;
    public $charset = "utf8";
    public $database_name;
    // For MySQL, MariaDB, MSSQL, Sybase, PostgreSQL, Oracle
    public $server;
    public $username;
    public $password;
    // For SQLite
    public $database_file;
    // For MySQL or MariaDB with unix_socket
    public $socket;
    // Optional
    public $port;
    public $prefix='';
    public $option = array();
    // Variable
    public $logs = array();
    public $debug_mode = false;

    public function __construct($options = null) {
        try {
            if (!$options) {
                global $db_config;
                $options = array(
                    'database_type' => $db_config['dbtype'],
                    'database_name' => $db_config['dbname'],
                    'server' => explode(":", $db_config['dbhost'])[0],
                    'port' => explode(":", $db_config['dbhost'])[1],
                    'username' => $db_config['dbuser'],
                    'password' => $db_config['dbpass'],
                    'prefix'=>$db_config["def"],
                    "charset" => $db_config['charset']  //处理汉字需要
                );                
            }

            $commands = array();
            $dsn = '';

            if (is_array($options)) {
                foreach ($options as $option => $value) {
                    $this->$option = $value;
                }
            } else {
                return false;
            }

            if (isset($this->port) && is_int($this->port * 1)) {
                $port = $this->port;
            }

            $type = strtolower($this->database_type);
            $is_port = isset($port);

            if (isset($options ['prefix'])) {
                $this->prefix = $options ['prefix'];
            }
            if (isset($options ['charset'])) {
                $this->charset = $options ['charset'];
            }

            switch ($type) {
                case 'mariadb' :
                    $type = 'mysql';

                case 'mysql' :
                    if ($this->socket) {
                        $dsn = $type . ':unix_socket=' . $this->socket . ';dbname=' . $this->database_name;
                    } else {
                        $dsn = $type . ':host=' . $this->server . ($is_port ? ';port=' . $port : '') . ';dbname=' . $this->database_name;
                    }

                    // Make MySQL using standard quoted identifier
                    $commands [] = 'SET SQL_MODE=ANSI_QUOTES';
                    break;

                case 'pgsql' :
                    $dsn = $type . ':host=' . $this->server . ($is_port ? ';port=' . $port : '') . ';dbname=' . $this->database_name;
                    break;

                case 'sybase' :
                    $dsn = 'dblib:host=' . $this->server . ($is_port ? ':' . $port : '') . ';dbname=' . $this->database_name;
                    break;

                case 'oracle' :
                    $dbname = $this->server ? '//' . $this->server . ($is_port ? ':' . $port : ':1521') . '/' . $this->database_name : $this->database_name;

                    $dsn = 'oci:dbname=' . $dbname . ($this->charset ? ';charset=' . $this->charset : '');
                    break;

                case 'mssql' :
                    $dsn = strstr(PHP_OS, 'WIN') ? 'sqlsrv:server=' . $this->server . ($is_port ? ',' . $port : '') . ';database=' . $this->database_name : 'dblib:host=' . $this->server . ($is_port ? ':' . $port : '') . ';dbname=' . $this->database_name;

                    // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
                    $commands [] = 'SET QUOTED_IDENTIFIER ON';
                    break;

                case 'sqlite' :
                    $dsn = $type . ':' . $this->database_file;
                    $this->username = null;
                    $this->password = null;
                    break;
            }

            if (in_array($type, array(
                        'mariadb',
                        'mysql',
                        'pgsql',
                        'sybase',
                        'mssql'
                    )) && $this->charset) {
                $commands [] = "SET NAMES '" . $this->charset . "'";
            }

            $this->pdo = new PDO($dsn, $this->username, $this->password, $this->option);

            foreach ($commands as $value) {
                $this->pdo->exec($value);
            }
        } catch (PDOException $e) {
            write_log("[MyPdo]" . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        $this->pdo = null;
    }

    /**
     * 执行sql语句，返回影响行数
     * */
    public function execSql($sql) {
        try {
            $results = $this->pdo->query($sql);
            if ($results) {
                return $results;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 执行sql查询，返回查询结果集
     * @param $querySql 查询语句
     * */
    public function querySql($sql) {
        try {
            $queryResults = $this->pdo->query($sql);
            $results = false;
            if ($queryResults)
                $results = $queryResults->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                return $results;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 条件查询 
     *
     * @param string $table 数据表名
     * @param string $columns 字段列表，如a,b,...
     * @param string $where 查询条件
     * @return boolean
     */
    public function selectByWhere(
    $table
    , $columns = null
    , $where = null
    , $groupField = null
    , $orderField = null) {
        if ($columns == null)
            $columns = "*";

        $sql = "SELECT " . $columns . " from " . $this->prefix . $table;
        if (!empty($where))
            $sql = $sql . " WHERE " . $where;
        if (!empty($groupField))
            $sql = $sql . " GROUP BY " . $groupField;
        if (!empty($orderField))
            $sql = $sql . " ORDER BY " . $orderField;

        $query = $this->pdo->query($sql);
        $stack = array();
        $index = 0;

        if (!$query) {
            return null;
        }

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 分页查询
     * @param string $table 数据表
     * @param string $columns 查询字段列表
     * @param type $pageIndex 页号
     * @param type $pageSize 页面大小
     * @param type $where 条件
     * @param type $orderField 排序字段
     * @return array
     */
    public function selectPage(
      $table
    , $pageIndex = 1
    , $pageSize = 20
    , $columns = null
    , $where = null
    , $orderField = null) {
        
        $count = $this->count($table,$where);
        if($count==0)return $result = array("code"=>0,"count" => 0, "data" => null);
        
        if ($columns == null)
            $columns = "*";

        $sql = "SELECT " . $columns . " from " .$this->prefix . $table;
        if (!empty($where))
            $sql = $sql . " WHERE " . $where;
        if (!empty($orderField))
            $sql = $sql . " ORDER BY " . $orderField;

        $sql = $sql . " LIMIT " . (($pageIndex-1) * $pageSize) ;
        if($count>($pageIndex)*$pageSize)        
            $sql.="," . $pageSize; 
        else
            $sql.="," .$count;
            
        //echo "page sql:{$sql}<br/>";
        $query = $this->pdo->query($sql);

        if (!$query) {
            return null;
        }

        $data= $query->fetchAll(PDO::FETCH_ASSOC);
        return array("code"=>0,"count" => $count, "data" =>$data);
    }

    /**
     * 查询
     * @param string $table 数据表名
     * @param string $columns 字段列表，如a,b,c...
     * @param string $where 条件
     * @param string $orderField 排序字段
     * @return boolean
     */
    public function select($table, $columns = null, $where = null, $orderField = null) {
        $sql = "SELECT " . ($columns == null ? "*" : $columns) . " FROM " . $this->prefix . $table
                . ($where == null ? "" : " WHERE " . $where)
                . ($orderField == null ? "" : " ORDER BY " . $orderField);

        $query = $this->pdo->query($sql);
        if (!$query) {
            return null;
        }

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 查询前n个记录
     * @param string $table 数据表名
     * @param string $columns 字段列表，如a,b,c...
     * @param string $where 条件
     * @param string $orderField 排序字段
     * @return boolean
     */
    public function selectByLimit($table
            , $limit = 1
            , $columns = null
            , $where = null
            , $orderField = null) {
        $sql = "SELECT " . ($columns == null ? "*" : $columns) . " FROM " . $this->prefix . $table
                . ($where == null ? "" : " WHERE " . $where)
                . ($orderField == null ? "" : " ORDER BY " . $orderField)
                . (" LIMIT " . $limit);

        $query = $this->pdo->query($sql);
        if (!$query) {
            return null;
        }

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 插入数据
     * @param string $table
     * @param array $datas
     * @return int
     */
    public function insert($table, $datas) {
        $lastId = array();

        // Check indexed or associative array
        if (!isset($datas [0])) {
            $datas = array(
                $datas
            );
        }

        foreach ($datas as $data) {
            $values = array();
            $columns = array();

            foreach ($data as $key => $value) {
                
                if($key=="id") continue; //跳过id
                $columns [] = '`' . preg_replace("/^(\(JSON\)\s*|#)/i", "", $key) . '`';                

                switch (gettype($value)) {
                    case 'NULL' :
                        $values [] = 'NULL';
                        break;

                    case 'object' :
                    case 'array' :
                        $fields [] = $column . " = '" . json_encode($value)."'";
                        break;

                    case 'boolean' :
                        $values [] = ($value ? '1' : '0');
                        break;

                    case 'integer':                 
                    case 'double' :
                        $values [] =$value;
                        break;
                    case 'string' :
                        $values [] = $this->fn_quote($key, $value);
                        break;
                }
            }
            $sql = 'INSERT INTO `' . $this->prefix . $table . '` (' . implode(', ', $columns) . ') VALUES (' . implode($values, ', ') . ')';
            //if($table==="Files") echo "sql:".$sql;

            $this->pdo->exec($sql);

            $lastId [] = $this->pdo->lastInsertId();
        }

        return count($lastId) > 1 ? $lastId : $lastId [0];
    }

    /**
     * 更新数据
     * @param type $table 数据表
     * @param type $data 数据集数组
     * @param type $where 条件，当条件为空时，则从$data中读取id值
     * @return type
     */
    public function update($table, $data, $where = null) {
        $fields = array();
        $sql = false;

        //如果是数组，则根据数组生成sql
        if (is_array($data)) {
            foreach ($data as $key => $value) { 
                
                if($key=="id") continue; //跳过id
                preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);

                if (isset($match [3])) {
                    if (is_numeric($value)) {
                        $fields [] = $match [1] . ' = ' . $match [1] . ' ' . $match [3] . ' ' . $value;
                    }
                } else {
                    $column = preg_replace("/^(\(JSON\)\s*|#)/i", "", $key);
                    $column = '`' . $column . '`';

                    switch (gettype($value)) {
                        case 'NULL' :
                            $fields [] = $column . ' = NULL';
                            break;

                        case 'object' :
                        case 'array' :
                            $fields [] = $column . " = '" . json_encode($value)."'";
                            break;

                        case 'boolean' :
                            $fields [] = $column . ' = ' . ($value ? '1' : '0');
                            break;

                        case 'integer' :
                        case 'double' :
                            $values [] =$value;
                            break;
                        case 'string' :
                            $fields [] = $column . ' = ' . $this->fn_quote($key, $value);
                            break;
                    }
                }
            }
            //当$where为空，则读取data中的主码
            if (empty($where) && array_key_exists("id", $data))
                $where = "id=" . $data ["id"];
            else if (empty($where) && array_key_exists("uid", $data))
                $where = "uid=" . $data ["uid"];
            
            $sql = 'UPDATE ' . $this->prefix . $table . ' SET ' . implode(', ', $fields) . (!empty($where) ? " WHERE " . $this->where_clause($where) : "");
        }
        else {  //如果$data不是数组，是"field=value"字符串
            $sql = 'UPDATE ' . $this->prefix . $table . ' SET '.$data ." WHERE ".$where;
        }

        if (!$where)
            return 0;  //返回失败代码0

        $r = $this->pdo->exec($sql);
        return $r;
    }

    public function delete($table, $where) {
        if (!$where)
            return 0;
        return $this->pdo->exec('DELETE FROM ' . $this->prefix . $table . " " . (empty($where) ? "" : " WHERE " . $where));
    }

    /**
     * 获取记录数
     * @param string $table 数据表名
     * @param string $where 条件
     * @return number
     */
    public function count($table, $where = null) {
        try {
            $sql = "SELECT COUNT(*) CNT FROM " . $this->prefix . $table . " " . ($where ? " WHERE " . $where : "");
            //echo "count: sql=".$sql;
            $cnt = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            return $cnt [0]["CNT"];
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * 判断记录是否存在
     * @param string $table 数据表名称
     * @param string $where 查询条件
     * @return bool
     */
    public function exists($table, $where) {
        $n = $this->count($table, $where);
        //echo "exists: n=" . $n;
        return $n > 0;
    }

    /**
     * 读取字段最大值
     * @param type $table 数据表
     * @param type $column 字段
     * @param type $where 条件 
     * @return type
     */
    public function max($table, $column = null, $where = null) {

        $sql = "SELECT MAX(`" . $column . "`) FROM ". $this->prefix .$table  . ($where ? "WHERE $where" : "");

        $query = $this->pdo->query($sql);

        if ($query) {
            $max = $query->fetchColumn();

            return is_numeric($max) ? $max + 0 : $max;
        } else {
            return null;
        }
    }

    /**
     * 取字段最小值
     * @param type $table 数据表
     * @param type $column 字段
     * @param type $where 条件 
     * @return type
     */
    public function min($table, $column = null, $where = null) {
        $sql = "SELECT MIN(`" . $column . "`) FROM ". $this->prefix .$table . ($where ? "WHERE $where" : "");

        $query = $this->pdo->query($sql);

        if ($query) {
            $min = $query->fetchColumn();

            return is_numeric($min) ? $min + 0 : $min;
        } else {
            return null;
        }
    }

    /**
     * 取字段均值
     * @param type $table 数据表
     * @param type $column 字段
     * @param type $where 条件 
     * @return type
     */
    public function avg($table, $column = null, $where = null) {
        $sql = "SELECT AVG(`" . $column . "`) FROM ". $this->prefix .$table . ($where ? "WHERE $where" : "");

        $query = $this->pdo->query($sql);

        if ($query) {
            $avg = $query->fetchColumn();

            return is_numeric($avg) ? $avg + 0 : $avg;
        } else {
            return null;
        }
    }

    /**
     * 取字段和 
     * @param type $table 数据表
     * @param type $column 字段
     * @param type $where 条件 
     * @return type
     */
    public function sum($table, $column = null, $where = null) {
        $sql = "SELECT SUM(`" . $column . "`) FROM ". $this->prefix .$table  . ($where ? "WHERE $where" : "");

        $query = $this->pdo->query($sql);

        if ($query) {
            $s = $query->fetchColumn();

            return is_numeric($s) ? $s + 0 : $s;
        } else {
            return null;
        }
    }

    public function action($actions) {
        if (is_callable($actions)) {
            $this->pdo->beginTransaction();

            $result = $actions($this);

            if ($result === false) {
                $this->pdo->rollBack();
            } else {
                $this->pdo->commit();
            }
        } else {
            return null;
        }
    }

    public function debug() {
        $this->debug_mode = true;

        return $this;
    }

    /** 错误信息 */
    public function getError() {
        return $this->pdo->errorInfo();
    }

    public function last_query() {
        return end($this->logs);
    }

    public function log() {
        return $this->logs;
    }

    public function info() {
        $output = array(
            'server' => 'SERVER_INFO',
            'driver' => 'DRIVER_NAME',
            'client' => 'CLIENT_VERSION',
            'version' => 'SERVER_VERSION',
            'connection' => 'CONNECTION_STATUS'
        );

        foreach ($output as $key => $value) {
            $output [$key] = $this->pdo->getAttribute(constant('PDO::ATTR_' . $value));
        }

        return $output;
    }

    // <editor-fold defaultstate="collapsed" desc="保护方法">
    protected function quote($string) {
        return $this->pdo->quote($string);
    }

    protected function table_quote($table) {
        return '"' . $this->prefix . $table . '"';
    }

    protected function column_quote($string) {
        preg_match('/(\(JSON\)\s*|^#)?([a-zA-Z0-9_]*)\.([a-zA-Z0-9_]*)/', $string, $column_match);

        if (isset($column_match [2], $column_match [3])) {
            return '"' . $this->prefix . $column_match [2] . '"."' . $column_match [3] . '"';
        }

        return '"' . $string . '"';
    }

    protected function column_push(&$columns) {
        if ($columns == '*') {
            return $columns;
        }

        if (is_string($columns)) {
            $columns = array(
                $columns
            );
        }

        $stack = array();

        foreach ($columns as $key => $value) {
            if (is_array($value)) {
                $stack [] = $this->column_push($value);
            } else {
                preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $value, $match);

                if (isset($match [1], $match [2])) {
                    $stack [] = $this->column_quote($match [1]) . ' AS ' . $this->column_quote($match [2]);

                    $columns [$key] = $match [2];
                } else {
                    $stack [] = $this->column_quote($value);
                }
            }
        }

        return implode($stack, ',');
    }

    protected function array_quote($array) {
        $temp = array();

        foreach ($array as $value) {
            $temp [] = is_int($value) ? $value : $this->pdo->quote($value);
        }

        return implode($temp, ',');
    }

    protected function inner_conjunct($data, $conjunctor, $outer_conjunctor) {
        $haystack = array();

        foreach ($data as $value) {
            $haystack [] = '(' . $this->data_implode($value, $conjunctor) . ')';
        }

        return implode($outer_conjunctor . ' ', $haystack);
    }

    protected function fn_quote($column, $string) {      
            return (strpos($column, '#') === 0 ) ? $string : "'" . $string . "'";
    }

    protected function data_implode($data, $conjunctor, $outer_conjunctor = null) {
        $wheres = array();

        foreach ($data as $key => $value) {
            $type = gettype($value);

            if (preg_match("/^(AND|OR)(\s+#.*)?$/i", $key, $relation_match) && $type == 'array') {
                $wheres [] = 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) ? '(' . $this->data_implode($value, ' ' . $relation_match [1]) . ')' : '(' . $this->inner_conjunct($value, ' ' . $relation_match [1], $conjunctor) . ')';
            } else {
                preg_match('/(#?)([\w\.\-]+)(\[(\>|\>\=|\<|\<\=|\!|\<\>|\>\<|\!?~)\])?/i', $key, $match);
                $column = $this->column_quote($match [2]);

                if (isset($match [4])) {
                    $operator = $match [4];

                    if ($operator == '!') {
                        switch ($type) {
                            case 'NULL' :
                                $wheres [] = $column . ' IS NOT NULL';
                                break;

                            case 'array' :
                                $wheres [] = $column . ' NOT IN (' . $this->array_quote($value) . ')';
                                break;

                            case 'integer' :
                            case 'double' :
                                $wheres [] = $column . ' != ' . $value;
                                break;

                            case 'boolean' :
                                $wheres [] = $column . ' != ' . ($value ? '1' : '0');
                                break;

                            case 'string' :
                                $wheres [] = $column . ' != ' . $this->fn_quote($key, $value);
                                break;
                        }
                    }

                    if ($operator == '<>' || $operator == '><') {
                        if ($type == 'array') {
                            if ($operator == '><') {
                                $column .= ' NOT';
                            }

                            if (is_numeric($value [0]) && is_numeric($value [1])) {
                                $wheres [] = '(' . $column . ' BETWEEN ' . $value [0] . ' AND ' . $value [1] . ')';
                            } else {
                                $wheres [] = '(' . $column . ' BETWEEN ' . $this->quote($value [0]) . ' AND ' . $this->quote($value [1]) . ')';
                            }
                        }
                    }

                    if ($operator == '~' || $operator == '!~') {
                        if ($type != 'array') {
                            $value = array(
                                $value
                            );
                        }

                        $like_clauses = array();

                        foreach ($value as $item) {
                            $item = strval($item);
                            $suffix = mb_substr($item, - 1, 1);

                            if (preg_match('/^(?!(%|\[|_])).+(?<!(%|\]|_))$/', $item)) {
                                $item = '%' . $item . '%';
                            }

                            $like_clauses [] = $column . ($operator === '!~' ? ' NOT' : '') . ' LIKE ' . $this->fn_quote($key, $item);
                        }

                        $wheres [] = implode(' OR ', $like_clauses);
                    }

                    if (in_array($operator, array(
                                '>',
                                '>=',
                                '<',
                                '<='
                            ))) {
                        if (is_numeric($value)) {
                            $wheres [] = $column . ' ' . $operator . ' ' . $value;
                        } elseif (strpos($key, '#') === 0) {
                            $wheres [] = $column . ' ' . $operator . ' ' . $this->fn_quote($key, $value);
                        } else {
                            $wheres [] = $column . ' ' . $operator . ' ' . $this->quote($value);
                        }
                    }
                } else {
                    switch ($type) {
                        case 'NULL' :
                            $wheres [] = $column . ' IS NULL';
                            break;

                        case 'array' :
                            $wheres [] = $column . ' IN (' . $this->array_quote($value) . ')';
                            break;

                        case 'integer' :
                        case 'double' :
                            $wheres [] = $column . ' = ' . $value;
                            break;

                        case 'boolean' :
                            $wheres [] = $column . ' = ' . ($value ? '1' : '0');
                            break;

                        case 'string' :
                            $wheres [] = $column . ' = ' . $this->fn_quote($key, $value);
                            break;
                    }
                }
            }
        }

        return implode($conjunctor . ' ', $wheres);
    }

    protected function where_clause($where) {
        $where_clause = '';

        if (is_array($where)) {
            $where_keys = array_keys($where);
            $where_AND = preg_grep("/^AND\s*#?$/i", $where_keys);
            $where_OR = preg_grep("/^OR\s*#?$/i", $where_keys);

            $single_condition = array_diff_key($where, array_flip(array(
                'AND',
                'OR',
                'GROUP',
                'ORDER',
                'HAVING',
                'LIMIT',
                'LIKE',
                'MATCH'
            )));

            if ($single_condition != array()) {
                $condition = $this->data_implode($single_condition, '');

                if ($condition != '') {
                    $where_clause = ' WHERE ' . $condition;
                }
            }

            if (!empty($where_AND)) {
                $value = array_values($where_AND);
                $where_clause = ' WHERE ' . $this->data_implode($where [$value [0]], ' AND');
            }

            if (!empty($where_OR)) {
                $value = array_values($where_OR);
                $where_clause = ' WHERE ' . $this->data_implode($where [$value [0]], ' OR');
            }

            if (isset($where ['MATCH'])) {
                $MATCH = $where ['MATCH'];

                if (is_array($MATCH) && isset($MATCH ['columns'], $MATCH ['keyword'])) {
                    $where_clause .= ($where_clause != '' ? ' AND ' : ' WHERE ') . ' MATCH ("' . str_replace('.', '"."', implode($MATCH ['columns'], '", "')) . '") AGAINST (' . $this->quote($MATCH ['keyword']) . ')';
                }
            }

            if (isset($where ['GROUP'])) {
                $where_clause .= ' GROUP BY ' . $this->column_quote($where ['GROUP']);

                if (isset($where ['HAVING'])) {
                    $where_clause .= ' HAVING ' . $this->data_implode($where ['HAVING'], ' AND');
                }
            }

            if (isset($where ['ORDER'])) {
                $ORDER = $where ['ORDER'];

                if (is_array($ORDER)) {
                    $stack = array();

                    foreach ($ORDER as $column => $value) {
                        if (is_array($value)) {
                            $stack [] = 'FIELD(' . $this->column_quote($column) . ', ' . $this->array_quote($value) . ')';
                        } else if ($value === 'ASC' || $value === 'DESC') {
                            $stack [] = $this->column_quote($column) . ' ' . $value;
                        } else if (is_int($column)) {
                            $stack [] = $this->column_quote($value);
                        }
                    }

                    $where_clause .= ' ORDER BY ' . implode($stack, ',');
                } else {
                    $where_clause .= ' ORDER BY ' . $this->column_quote($ORDER);
                }
            }

            if (isset($where ['LIMIT'])) {
                $LIMIT = $where ['LIMIT'];

                if (is_numeric($LIMIT)) {
                    $where_clause .= ' LIMIT ' . $LIMIT;
                }

                if (is_array($LIMIT) && is_numeric($LIMIT [0]) && is_numeric($LIMIT [1])) {
                    if ($this->database_type === 'pgsql') {
                        $where_clause .= ' OFFSET ' . $LIMIT [0] . ' LIMIT ' . $LIMIT [1];
                    } else {
                        $where_clause .= ' LIMIT ' . $LIMIT [0] . ',' . $LIMIT [1];
                    }
                }
            }
        } else {
            if ($where != null) {
                $where_clause .= ' ' . $where;
            }
        }

        return $where_clause;
    }

    protected function select_context($table, $join, &$columns = null, $where = null, $column_fn = null) {
        preg_match('/([a-zA-Z0-9_\-]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $table, $table_match);

        if (isset($table_match [1], $table_match [2])) {
            $table = $this->table_quote($table_match [1]);

            $table_query = $this->table_quote($table_match [1]) . ' AS ' . $this->table_quote($table_match [2]);
        } else {
            $table = $this->table_quote($table);
            $table_query = $table;
        }

        $join_key = is_array($join) ? array_keys($join) : null;

        if (isset($join_key [0]) && strpos($join_key [0], '[') === 0) {
            $table_join = array();

            $join_array = array(
                '>' => 'LEFT',
                '<' => 'RIGHT',
                '<>' => 'FULL',
                '><' => 'INNER'
            );

            foreach ($join as $sub_table => $relation) {
                preg_match('/(\[(\<|\>|\>\<|\<\>)\])?([a-zA-Z0-9_\-]*)\s?(\(([a-zA-Z0-9_\-]*)\))?/', $sub_table, $match);

                if ($match [2] != '' && $match [3] != '') {
                    if (is_string($relation)) {
                        $relation = 'USING ("' . $relation . '")';
                    }

                    if (is_array($relation)) {
                        // For ['column1', 'column2']
                        if (isset($relation [0])) {
                            $relation = 'USING ("' . implode($relation, '", "') . '")';
                        } else {
                            $joins = array();

                            foreach ($relation as $key => $value) {
                                $joins [] = (strpos($key, '.') > 0 ?
                                        // For ['tableB.column' => 'column']
                                        $this->column_quote($key) :
                                        // For ['column1' => 'column2']
                                        $table . '."' . $key . '"') . ' = ' . $this->table_quote(isset($match [5]) ? $match [5] : $match [3]) . '."' . $value . '"';
                            }

                            $relation = 'ON ' . implode($joins, ' AND ');
                        }
                    }

                    $table_name = $this->table_quote($match [3]) . ' ';

                    if (isset($match [5])) {
                        $table_name .= 'AS ' . $this->table_quote($match [5]) . ' ';
                    }

                    $table_join [] = $join_array [$match [2]] . ' JOIN ' . $table_name . $relation;
                }
            }

            $table_query .= ' ' . implode($table_join, ' ');
        } else {
            if (is_null($columns)) {
                if (is_null($where)) {
                    if (is_array($join) && isset($column_fn)) {
                        $where = $join;
                        $columns = null;
                    } else {
                        $where = null;
                        $columns = $join;
                    }
                } else {
                    $where = $join;
                    $columns = null;
                }
            } else {
                $where = $columns;
                $columns = $join;
            }
        }

        if (isset($column_fn)) {
            if ($column_fn == 1) {
                $column = '1';

                if (is_null($where)) {
                    $where = $columns;
                }
            } else {
                if (empty($columns)) {
                    $columns = '*';
                    $where = $join;
                }

                $column = $column_fn . '(' . $this->column_push($columns) . ')';
            }
        } else {
            $column = $this->column_push($columns);
        }

        return 'SELECT ' . $column . ' FROM ' . $table_query . $this->where_clause($where);
    }

    protected function data_map($index, $key, $value, $data, &$stack) {
        if (is_array($value)) {
            $sub_stack = array();

            foreach ($value as $sub_key => $sub_value) {
                if (is_array($sub_value)) {
                    $current_stack = $stack [$index] [$key];

                    $this->data_map(false, $sub_key, $sub_value, $data, $current_stack);

                    $stack [$index] [$key] [$sub_key] = $current_stack [0] [$sub_key];
                } else {
                    $this->data_map(false, preg_replace('/^[\w]*\./i', "", $sub_value), $sub_key, $data, $sub_stack);

                    $stack [$index] [$key] = $sub_stack;
                }
            }
        } else {
            if ($index !== false) {
                $stack [$index] [$value] = $data [$value];
            } else {
                $stack [$key] = $data [$key];
            }
        }
    }

    private function isIgnoreField($key) {
        global $skipFields;

        //清除不需要的参数
        foreach ($skipFields as $k => $val) {
            if (array_value_exists($val, $data))
                continue;
        }
    }

// </editor-fold>
}

?>