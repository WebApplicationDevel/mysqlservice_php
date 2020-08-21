<?php

/*
 * 文件：FormService.php
 * 说明：表单服务
 * 作者：李光强
 * 时间：2018/7/4/
 */

include_once ('config.php');

//include_once (CACHE_PATH.'form.cache.php');

class FormService {

    public static function createForm() {
        $requestData = ($_SERVER [FlagEnum::REQUEST_METHOD] === "GET" ? $_GET : $_POST);
        $routeId = 0;
        $prjId = 0;

        if (isset($requestData["routeId"])) {
            $routeId = intval($requestData["routeId"]);
        }
        if (isset($requestData["prjId"]))
            $prjId = intval($requestData["prjId"]);


        if ($routeId == 0 && $prjId == 0) {
            die(json_encode(array("code" => 3001, "msg" => "路线id不正确", "data" => "")));
        }

        require_once (dirname(dirname(__FILE__)) . '/libs/core/Dao.class.php');
        $dao = new Dao();
        $data = $dao->select("prj_form", "*", "id=fun_getFormId($prjId,$routeId)");
        if (is_array($data))
            $data = $data[0];

        echo json_encode(array("code" => 0,"data"=>FormService::build($data)));
    }
    
    /**
     * 生成表单
     * @param type $data
     */
    private static function build($data)
    {
        global $_FORM_TEMPLATE;
        $tpl=$_FORM_TEMPLATE;
        
        if(!is_array($data)) 
            die(json_encode(array("code" => 3002, "msg" => "表单生成失败", "data" => "")));
        
        $data= json_decode($data["content"]);
        
        $tpl_row=null;
        $content=array();

        $field=null;
        foreach($data as $row)
        {
           $type=$row->type;
           $tpl_row=$tpl[$type];
           $field=str_replace("{{label}}", $row->label, $tpl_row);
           $field=str_replace("{{field}}",$row->field,$field);
           if($row->required)
           {
               $field=str_replace("{{required-icon}}",'<span style="color:#f00">*</span>',$field);
               $field=str_replace("{{required}}","required=$row->required",$field);
           }
           else
           {
               $field=str_replace("{{required-icon}}",'',$field);
               $field=str_replace("{{required}}","",$field);
           }
           $field=str_replace("{{readonly}}",$row->readonly?"readonly='".$row->readonly."'":"",$field);
           $field=str_replace("{{placeholder}}",!empty($row->tip)?$row->tip:"",$field);
           
           if($type=="checkbox" ||$type=="select") //如果是复选框，则需要生成复选框中的选项
           {
               $field=str_replace("{{option}}",FormService::parseOption($row),$field);
           }
           
            if($type=="integer" ||$type=="numeric") //如果是数字 ，则需要生成min和max范围
           {               
                if(isset($row->min))
                    $field=str_replace("{{min}}","min=$row->min",$field);
                if(isset($row->max))
                    $field=str_replace("{{max}}","max=$row->max",$field);
           }
           
           $content[]=$field;
        }
        $content[]=<<<EOT
        <script>
          function checkbox_changed(obj){
               var sv=jQuery("#"+$(obj).attr("_target")).val();
               var val=$(obj).attr("key")+":"+$(obj).attr("value");
               if($(obj)[0].checked)
                {
                   sv+=(sv!=""?",":"")+val;
                }
                else{
                  sv=sv.replace(","+val+",",'')
                       .replace(","+val,"")
                       .replace(val+",","")
                       .replace(val,"");
                }
               jQuery("#"+$(obj).attr("_target")).val(sv); 
        }
                
            function select_changed(obj){               
               var t = $(obj).find("option:selected").text();
               var v= $(obj).val();
               jQuery("#"+$(obj).attr("_target")).val(t+":"+v); 
        }
        </script> 
EOT;
        $cont= implode(" ",$content);
        return $cont;
        
    }
    
    /** 
     * 生成checkbox 选项
     * @global type $_FORM_TEMPLATE
     * @param type $data 数据
     */
    private static function parseOption($row)
    {
        if(empty($row))
        {
            return "";
        }
        global $_FORM_TEMPLATE;
        $tpl=$_FORM_TEMPLATE;
        
        $tpl_option=$tpl["$row->type-option"];
        $options=array();
        $arr=explode(",",$row->options);
        foreach($arr as $o)
        {
            $ar=explode(":",$o);
            $opt= str_replace("{{label}}", $ar[0], $tpl_option);
            $opt=str_replace("{{val}}", $ar[1], $opt);
            $opt=str_replace("{{field}}", $row->field, $opt);
            $options[]=$opt;
        }
        
        return implode(" ", $options);
    }

}
