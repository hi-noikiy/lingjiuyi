<?php
namespace Home\Controller;
use Think\Controller;
class ShopController extends CommonController{
    //店铺主页
    public function index(){
        I('get.shop_id','','intval') ? $shop_id = I('get.shop_id','','intval') : false; //商铺id
        isset($shop_id) ? $where['a.id'] = $shop_id : $this -> ajaxReturnData(0,'参数错误');
        $field = "shop_name,shop_logo_web,shop_desc,b.id cate_id,shop_cate_name";//需要展示的字段
        $shopinfo = D('Shop') -> alias('a') -> field($field) -> where($where) -> join('zhouyuting_shop_cate b on a.id = b.shop_id') -> select();
        $this -> assign('shopinfo',$shopinfo);
        $this -> display();
    }

    //获取分类商品
    public function get_goods(){
        I('get.cate_id','','intval') ? $cate_id  =  I('get.cate_id','','intval') : false;//分类id
        I('get.p','','intval') ? $p  =  I('get.p','','intval') : false;//当前页
        isset($cate_id) ? $where['a.shop_cate_id'] = $cate_id : $this -> ajaxReturnData(0,'参数错误');
        $pagesize = 10;
        $start = ($p - 1) * $pagesize;
        $field = "b.goods_id i,b.goods_name gn,b.goods_price gp,b.goods_big_img gb,b.goods_create_time add_time,is_act,click_num,goods_sales,b.goods_desc gd";
        $goods = D('Shop_cate_goods') -> alias('a') -> field($field) -> where($where) -> join('zhouyuting_goods b on a.goods_id = b.goods_id') -> limit($start,$pagesize) -> select();
        foreach($goods as $key => $value){
            $goods[$key]['add_time'] = date('Y/m/d',$goods[$key]['add_time']);
        }
        $page = array(
            'p' => $p,
            'pagesize' => $pagesize,
            'count' => count($goods)
        );
        empty($goods) ? $this -> ajaxReturnData(101,'最后一页') : $this -> ajaxReturnData(10000,'success',compact('goods','page'));
    }
}