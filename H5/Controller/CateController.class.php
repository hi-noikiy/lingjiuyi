<?php
//1、定义命名空间
namespace H5\Controller;
//2、引入核心控制器
use Think\Controller;
use Think\Page;
//3、定义News控制器
class CateController extends CommonController {
    public function index(){
        $this -> display();
    }

    //获取分类图片
    public function get_banner(){
        I('get.id','','intval') ? $cate_id = I('get.id','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $img = M('Category') -> where("id = $cate_id") -> getField('cate_img');
        empty($img) ? $this -> ajaxReturnData(0,'无图片') : $this -> ajaxReturnSuccess($img);
    }

    //获取列表
    public function get_list(){

        I('get.id','','intval') ? $cate_id = I('get.id','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $category = D('Category') -> alias('a') -> field('c.id,c.pid') -> where(array( 'a.id' =>$cate_id)) -> join('zhouyuting_category b on a.id = b.pid') -> join('zhouyuting_category c on b.id = c.pid') -> select();//接收大类，查询pid下的小类id
        $cateids = [];
        foreach ($category as $key =>$value) {
            //将数组拼接成想要的形式
            $cateids['ids']     .= $value['id'].',';
        }
        $cateids['ids'] = rtrim($cateids['ids'],',');//凭借所有子类的id

        $p = I('get.p','','intval') ? I('get.p','','intval') : 1;
        $fields = 'goods_id,goods_name,goods_price,goods_small_img';
        $where = array(
            'cate_id' => ['in',$cateids['ids']],
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