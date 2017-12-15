<?php
/**
 * Created by PhpStorm.
 * User: wcj
 * Date: 2017/10/20
 * Time: 12:51
 */

//1、定义命名空间
namespace H5\Controller;
//2、引入核心控制器
use Think\Controller;
use Think\Page;
//3、定义News控制器
class ActivityController extends CommonController {
    //限时活动展示页
    public function index(){
        $this -> display();
    }

    //ajax限时活动
    public function ajax_index(){
        $topicimg = M('Type') -> where(['type_id' => 5]) -> getField('banner_img');

        $p = $_GET['p'] ? $_GET['p'] : 1;
        $fields = 'goods_id,goods_name,goods_price,goods_sn,goods_small_img';
        $count = D('Goods') -> where('is_act = 1') -> count('goods_id');
        $pagesize = 10;//每次获取的数据条数
        $pager = new Page($count,$pagesize);
        $totalPages = $pager -> totalPages;
        $firstRow   = $pager -> firstRow;

        $topiclist = M('Goods') -> field($fields) -> where(['is_act' => 1]) -> limit($firstRow,$pagesize) -> select();


        $page = array(
            'scalarPageNum' => (int)$p,//当前页
            'scalarPageAll' => (int)$totalPages,//总页码
        );

        //设置返回数据格式
        $data = array(
            'code' => 10000,
            'msg'  => 'success',
            'info' => compact('topicimg','topiclist','page')
        );
        //返回数据
        $this->ajaxReturn($data);

    }

/*
    //ajax限时活动
    public function ajax_index(){
        $topicimg = M('Type') -> where(['type_id' => 5]) -> getField('banner_img');
        //显示活动中商品列表信息
        $join = 'zhouyuting_goods b on a.goods_id = b.goods_id';//定义连接语句
        $count = M('Activity') -> alias('a') -> join($join) -> count('b.goods_id');//商品总量
        //排序方式，参数为 销量：sales 价格：price 时间：create_time
        $sort = $_GET['sort'] ? $_GET['sort'] : 'id'; //默认按照 商品id显示综合数据
        //排序方式，参数为 升序：asc 降序：desc
        $rules = $_GET['rules'] ? $_GET['rules'] : 'desc';//默认按照 升序排列
        $order = "goods_".$sort." ".$rules;//定义排序的条件
        $pagesize = 10;//每页显示数量
        $page = new Page($count,$pagesize);//实例化分页类
        $p = $_GET['p'] ? $_GET['p'] : 1;//当前页码
        $firstRow = $page -> firstRow;//起始行数
        $totalPages = $page -> totalPages;// 分页总页面数
        //如果当前页大于总页码
        if($p > $totalPages){
            //返回信息
            $userlikeinfo = '已经是最后一页了';
        }else{
            $topiclist = M('Activity')
                -> alias('a')
                -> field('b.goods_id,b.goods_name,b.goods_price,b.goods_small_img,b.goods_sn')
                -> join($join)
                -> limit($firstRow,$pagesize)
                -> order($order)
                -> select();
            $pagesize = ($count < $pagesize) ? $count : $pagesize;
            $pager = array(
                'nowPage'    => (int)$p,
                'pagesize'   => (int)$pagesize,
                'totalPages' => $totalPages
            );
        }
        //设置返回数据格式
        $data = array(
            'code' => 10000,
            'msg'  => 'success',
            'info' => compact('topicimg','topiclist','pager','userlikeinfo')
        );
        //返回数据
        $this->ajaxReturn($data);
    }

*/
}