<?php

/*
 * 文件：DataOpEnum.php
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'EnumAbstract.php';
/**
 * Description of DataOpEnum
 *
 * @author admin
 */
class DataOpEnum extends EnumAbstract {
    /**
     * 选择操作
     */
    const SELECT=0;
    /**
     * 插入或更新操作
     */
    const MERGE=1;
    /**
     * 删除操作
     */
    const DELETE=2;
    
}
