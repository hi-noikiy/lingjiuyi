<?php
//1、定义命名空间
namespace H5\Controller;
//2、引入核心控制器
use Think\Controller;

//3、定义News控制器
class CateController extends CommonController {
    public function index(){
        $this -> display();
    }
    //ajax商品分类之后列表页
    public function ajax_index(){
        $cate_id = $_GET['id'];//分类id
        if(empty($cate_id)){
            $code = 0;
            $mag  = '参数错误';
        }else{
            $model = M('Category');//实例化模型
            $category = $model
                -> alias('a')
                -> field('a.cate_img,c.id,c.pid')
                -> where(array( 'a.id' =>$cate_id))
                -> join('zhouyuting_category b on a.id = b.pid')
                -> join('zhouyuting_category c on b.id = c.pid')
                -> select();//接收大类，查询pid下的小类id
            $cateids = [];
            foreach ($category as $key =>$value) {
                //将数组拼接成想要的形式
                $cate_img = $value['cate_img'];//返回的分类图片地址
                $cateids['ids']     .= $value['id'].',';
            }
            $cateids['ids'] = rtrim($cateids['ids'],',');//凭借所有子类的id
            //查询分类下的所有商品
            $field = 'a.goods_id,a.goods_name,a.goods_price,a.goods_small_img,a.goods_sn';
            $goodslist = M('Goods')
                -> alias('a')
                -> field($field)
                -> where(array('a.cate_id'=>array('in',$cateids['ids'])))
                -> select();
            if(empty($goodslist)){
                $code = 10001;
                $msg  = '商品为空';
            }else{
                $code = 10000;
                $msg  = 'success';
            }
        }

        //设置返回数据格式
        $data = array(
            'code' => $code,
            'msg'  => $msg,
            'info' => compact('goodslist','cate_img')
        );
        //返回数据
        $this->ajaxReturn($data);


    }
}