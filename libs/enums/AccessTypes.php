<?php


namespace smartyphp\core\enums;

include_once 'EnumAbstract.php';

/**
 * 网站访问类型
 * @author SmartPower
 * @since 2018/3/14/
 * @version 1.0
 */
class AccessTypes extends EnumAbstract {
    /**
     * WEB访问
     */
    const WEB=0;
    /**
     * WAP访问
     */
    const WAP=1;
    /**
     * 后台管理
     */
    const ADMIN=2;
}
