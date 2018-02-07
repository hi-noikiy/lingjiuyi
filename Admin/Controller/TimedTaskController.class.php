<?php
namespace Admin\Controller;
use Think\Controller;

class TimedTaskController extends Controller{
    public function index(){

        $run_time = mktime(23,59,59,date("m"),date("d"),date("Y"));// 定时任务第一次执行的时间是 今天的结束时间 23:59:59
        $interval = 3600*24; // 每24个小时执行一次
        $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $time_task = D('Setting') -> where("name = 'time_task'") -> getField('content');
        if($time_task == 0){
            echo '关闭状态！';
            exit(); // 如果定时脚本任务 == 0，就停止执行，这是一个开关的作用
        }

        $sale = D('Saleonline') -> where(['add_time' => ['LIKE','%'.date('Y-m-d').'%']]) -> select();
        if($sale){
            echo '已经统计过了！';die;
        }else{

            $where = array(
                "a.order_status" => 4, //订单状态为4 已完成的订单
                'a.update_time'  => ['BETWEEN',[date("Y-m-d 00:00:00",strtotime("-1 day")),date('Y-m-d 00:00:00')]], //昨天0点 - 今天0点
            );
            $fields = 'b.goods_id,SUM(b.number) sale_num,SUM(b.goods_price) sale_money';
            $join   = 'zhouyuting_order_goods b on a.id = b.order_id';
            $data   = D('Order') -> field($fields) -> alias('a') -> where($where) -> join($join) -> group('goods_id') -> select();//查询线上销售
            $res    = D('Saleonline') -> addAll($data);

            if(empty($res)){
                //添加不成功
                $time     = date('Y-m-d H:i:s');
                $filename = 'Public/errorMsg.txt';
                file_put_contents($filename,$time."统计线上销售额失败！;\r\n",FILE_APPEND); // 结果为false 则添加不成功
            }

            //sleep(60);//sleep共享虚拟机不支持，我靠
            //file_get_contents($url);
        }


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
            return $result;
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
            return $result;
        }
    }
}