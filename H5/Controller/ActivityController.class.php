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

    //获取顶部banner
    public function get_banner(){
        $banner = M('Setting') -> where("`name` = 'activity_bgimg'") -> getField('content');
        empty($banner) ? $this -> ajaxReturnData(0,'暂无banner') : $this -> ajaxReturnSuccess($banner);
    }

    //ajax限时活动
    public function get_list(){

        $p = $_GET['p'] ? $_GET['p'] : 1;
        $fields = 'goods_id,goods_name,goods_price,goods_sn,goods_small_img';
        $count = D('Goods') -> where('is_act = 1') -> count('goods_id');
        $pagesize = 10;//每次获取的数据条数
        $pager = new Page($count,$pagesize);
        $totalPages = $pager -> totalPages;
        $firstRow   = $pager -> firstRow;

        $sort = $_GET['sort'] ? $_GET['sort'] : 'id';
        $rules = $_GET['rules'] ? $_GET['rules'] : 'desc';
        $order = "goods_".$sort." ".$rules;

        $topiclist = M('Goods') -> field($fields) -> where(['is_act' => 1]) -> limit($firstRow,$pagesize) -> order($order) -> select();

        $pagesize = ($count < $pagesize) ? $count : $pagesize;
        $page = array(
            'nowPage' => (int)$p,//当前页
            'pagesize'   => (int)$pagesize,
            'totalPages' => $totalPages
        );
        empty($topiclist) ? $this -> ajaxReturnData(0,'无数据') : $this -> ajaxReturnSuccess(compact('topiclist','page'));

    }
}