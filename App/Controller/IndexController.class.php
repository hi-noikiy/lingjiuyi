<?php
namespace App\Controller;
use Think\Controller;

class IndexController extends CommonController{
	//首页头部分类
    public function index()
    {
        $cate       = D('Category') -> field('id,cate_name') -> where(['pid' => 0,'is_show' => 1]) -> select();
        $fields     = 'goods_id,goods_name,goods_price,goods_bigprice,goods_small_img,goods_sn';
        $goods_list = D('Goods') -> field($fields) -> select();//后期添加条件需要改成热卖商品

        empty($cate) ? $this -> ajaxReturnData(0,'没有分类数据') : true;
        empty($goods_list) ? $this -> ajaxReturnData(0,'没有热卖商品数据') : true;

        $this -> ajaxReturnSuccess(compact('cate','goods_list'));
    }

    //首页商品
    public function goods_list()
    {
    	$id = I('get.id');

    }

}