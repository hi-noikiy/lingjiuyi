<?php
//1、定义命名空间
namespace H5\Controller;
//2、引入核心控制器
use Think\Controller;
use Think\Page;

//3、定义News控制器
class ShopController extends CommonController{
    public function index(){
        $this -> display();
    }

    public function get_banner(){
        I('get.id','','intval') ? $id = I('get.id','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $img = M('Shop') -> where("id = $id") -> getField('shop_logo');
        empty($img) ? $this -> ajaxReturnData(0,'无图片') : $this -> ajaxReturnSuccess($img);
    }

    public function get_list(){
        I('get.id','','intval') ? $shop_id = I('get.id','','intval') : $this -> ajaxReturnData(0,'参数错误');

        $p = I('get.p','','intval') ? I('get.p','','intval') : 1;
        $fields = 'goods_id,goods_name,goods_price,goods_small_img';
        $where = array(
            'shop_id'   => $shop_id,
            'is_normal' => 1,
        );

        $count = D('Goods') -> where($where) -> count('goods_id');
        $pagesize = 10;//每次获取的数据条数
        $pager = new Page($count,$pagesize);
        $totalPages = $pager -> totalPages;
        $firstRow   = $pager -> firstRow;

        $sort  = I('get.sort','','string')  ? I('get.sort','','string') : 'id';
        $rules = I('get.rules','','string') ? I('get.rules','','string') : 'desc';
        $order = "goods_".$sort." ".$rules;

        $goodslist = M('Goods') -> field($fields) -> where($where) -> limit($firstRow,$pagesize) -> order($order) -> select();

        $pagesize = ($count < $pagesize) ? $count : $pagesize;
        $page = array(
            'nowPage' => (int)$p,//当前页
            'pagesize'   => (int)$pagesize,
            'totalPages' => $totalPages
        );
        empty($goodslist) ? $this -> ajaxReturnData(0,'无数据') : $this -> ajaxReturnSuccess(compact('goodslist','page'));
    }
}