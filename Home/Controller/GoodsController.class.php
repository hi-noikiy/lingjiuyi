<?php
namespace Home\Controller;
use Think\Controller;

class GoodsController extends CommonController {

    //商品详情页
    public function detail(){
        $goods_id = I('get.id','','intval');
        $where = "goods_id = $goods_id";
        $goods = D('Goods') -> where($where) -> relation(true) -> find();
        $goods['Heart'] = count($goods['Heart']);//收藏数
        $goods['Comment_num'] = D('Comment') -> where($where) -> count('goods_id');//评论数

        $goods['Shop'] = D('Goods') ->field('id,shop_name,shop_logo_web') -> alias('a') -> where($where) -> join('zhouyuting_shop b on a.shop_id = b.id') -> find();//店铺信息

        $attrs   = D('goods_attr') -> alias('a') -> field('a.id gaid,a.attr_id ai,a.attr_value value,b.attr_name name,b.attr_type type') -> where($where) -> join('zhouyuting_attribute b on a.attr_id = b.attr_id') -> select();

        $new_attrs = change_array($attrs,'ai');

        $where_copy = ['shop_id' => $goods['shop_id']];
        $shop_cate = D('Shop_cate') -> field('id,shop_cate_name') -> where($where_copy) -> select();//分类

        $fileds = 'goods_name an,goods_bigprice gb,goods_price gp,goods_small_img si';
        $hot_goods = D('Goods') -> field($fileds)  -> where($where_copy) -> limit(20) -> order('goods_sales desc') -> select();//店铺热销

        $this -> assign('goods',$goods);
        $this -> assign('shop_cate',$shop_cate);
        $this -> assign('hot_goods',$hot_goods);
        $this -> assign('attrs',$new_attrs);
        $this -> display();
    }

    //商品评论
    public function comment(){
        $goods_id = I('get.gid','','intval');
        $where = "goods_id = $goods_id";
        $fileds = 'a.*,b.username,b.header_img';
        $comment = D('Comment') -> field($fileds) -> alias('a') -> where($where) -> join('zhouyuting_user b on a.user_id = b.id') -> select();

        empty($comment) ? $this -> ajaxReturnData(0,'暂无评论') : $this -> ajaxReturnData(10000,'success',$comment);
    }
}