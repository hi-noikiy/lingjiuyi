<?php
/**
 * [get_os 获取操作系统]
 * @return [type] [description]
 */
function get_os()
{
    $agent = $_SERVER['HTTP_USER_AGENT']; //获取操作系统、浏览器等信息
    $os = ''; //返回操作系统

    if(eregi('win', $agent) && eregi('nt 6.3', $agent))
    {
        $os = 'Windows 8.1';
    }
    elseif(eregi('win', $agent) && eregi('nt 6.2', $agent))
    {
        $os = 'Windows 8';
    }
    else if(eregi('win', $agent) && eregi('nt 6.1', $agent))
    {
        $os = 'Windows 7';
    }
    else if(eregi('win', $agent) && eregi('nt 6.0', $agent))
    {
        $os = 'Windows Vista';
    }
    else if(eregi('win', $agent) && eregi('nt 5.2', $agent))
    {
        $os = "Windows 2003"; //eregi字符串比对
    }
    else if(eregi('win', $agent) && eregi('nt 5.1', $agent))
    {
        $os = 'Windows XP';
    }
    else if(eregi('win', $agent) && eregi('nt 5', $agent))
    {
        $os = 'Windows 2000';
    }
    else if(eregi('win', $agent) && eregi('nt', $agent))
    {
        $os = 'Windows NT';
    }
    else if(eregi('linux', $agent))
    {
        $os = 'Linux';
    }
    else if(eregi('unix', $agent))
    {
        $os = 'Unix';
    }
    else if(eregi('sun', $agent) && eregi('os', $agent))
    {
        $os = 'SunOS';
    }
    else if(eregi('ibm', $agent) && eregi('os', $agent))
    {
        $os = 'IBM OS/2';
    }
    else if(eregi('Mac', $agent) && eregi('Macintosh', $agent))
    {
        $os = 'Macintosh';
    }
    else if(eregi('PowerPC', $agent))
    {
        $os = 'PowerPC';
    }
    else if(eregi('AIX', $agent))
    {
        $os = 'AIX';
    }
    else if(eregi('HPUX', $agent))
    {
        $os = 'HPUX';
    }
    else if(eregi('NetBSD', $agent))
    {
        $os = 'NetBSD';
    }
    else if(eregi('BSD', $agent))
    {
        $os = 'BSD';
    }
    else if(ereg('OSF1', $agent))
    {
        $os = 'OSF1';
    }
    else if(ereg('IRIX', $agent))
    {
        $os = 'IRIX';
    }
    else if(eregi('FreeBSD', $agent))
    {
        $os = 'FreeBSD';
    }
    else if(eregi('teleport', $agent))
    {
        $os = 'teleport';
    }
    else if(eregi('flashget', $agent))
    {
        $os = 'flashget';
    }
    else if(eregi('webzip', $agent))
    {
        $os = 'webzip';
    }
    else if(eregi('offline', $agent))
    {
        $os = 'offline';
    }
    else
    {
        $os = 'Unknown';
    }
    return $os;
}

/**
 * [get_mysql_version 数据库版本]
 * @return [type] [description]
 */
function get_mysql_version()
{
   $mysqlinfo=mysql_get_server_info();
   return $mysqlinfo;
}



