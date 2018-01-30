<?php
namespace Admin\Controller;
use Think\Controller;
ignore_user_abort(true);
set_time_limit(0);
date_default_timezone_set('PRC'); // 切换到中国的时间
class TimedTaskController extends Controller{
    public function index(){
        $run_time = strtotime('+1 day'); // 定时任务第一次执行的时间是明天的这个时候
        $interval = 3600*12; // 每12个小时执行一次

        if(!file_exists(dirname(__FILE__).'/cron-run')) exit(); // cron-run文件，如果这个文件不存在，说明已经在执行过程中了

        do {
            dump(C());die;
            if(C('zhouyuting_time_task') == 0) break; // 如果zhouyuting_time_task == 0，就停止执行，这是一个开关的作用

            $gmt_time = microtime(true); // 当前的运行时间，精确到0.0001秒
            $loop = isset($loop) && $loop ? $loop : $run_time - $gmt_time; // 这里处理是为了确定还要等多久才开始第一次执行任务，$loop就是要等多久才执行的时间间隔
            $loop = $loop > 0 ? $loop : 0;
            if(!$loop) break; // 如果循环的间隔为零，则停止
            sleep($loop);
            // ...
            // 执行某些代码
            // ...
            @unlink(dirname(__FILE__).'/cron-run'); // 这里就是通过删除cron-run来告诉程序，这个定时任务已经在执行过程中，不能再执行一个新的同样的任务
            $loop = $interval;
        } while(true);
    }

    public function send_sock() {
        $url = "http://admin.lingjiuyi.cn/TimedTask/index";
        $host = parse_url($url,PHP_URL_HOST);
        $port = parse_url($url,PHP_URL_PORT);
        $port = $port ? $port : 80;
        $scheme = parse_url($url,PHP_URL_SCHEME);
        $path = parse_url($url,PHP_URL_PATH);
        $query = parse_url($url,PHP_URL_QUERY);
        if($query) $path .= '?'.$query;
        if($scheme == 'https') {
            $host = 'ssl://'.$host;
        }

        $fp = fsockopen($host,$port,$error_code,$error_msg,1);
        if(!$fp) {
            $result = array('error_code' => $error_code,'error_msg' => $error_msg);
            var_dump($result);
        }
        else {
            stream_set_blocking($fp,true);//开启了手册上说的非阻塞模式
            stream_set_timeout($fp,1);//设置超时
            $header = "GET $path HTTP/1.1\r\n";
            $header.="Host: $host\r\n";
            $header.="Connection: close\r\n\r\n";//长连接关闭
            fwrite($fp, $header);
            usleep(1000); // 这一句也是关键，如果没有这延时，可能在nginx服务器上就无法执行成功
            fclose($fp);
            $result = array('error_code' => 0);
            var_dump($result);
        }
    }
}