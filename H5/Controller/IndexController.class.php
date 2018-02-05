<?php
//1、定义命名空间
namespace H5\Controller;
//2、引入核心控制器
use Think\Controller;
use Think\Page;

//3、定义News控制器
class IndexController extends CommonController {
    //定义index方法
    public function index() {
        $this ->display();
    }


    public function ajax_index(){

        //首页顶部展示的置顶的商品信息
        $shoplist = M('Shop')
            -> field('id,shop_logo,shop_header_img')
            -> where(array('is_top' => 1))
            -> select();
        //首页中部分类大图标信息
        $catelist = M('Category')
            -> field('id,cate_img')
            -> where(array('pid' => 0))
            -> select();

        //设置返回数据格式
        $data = array(
            'code' => 10000,
            'msg'  => 'success',
            'info' => compact('shoplist','catelist')
        );
        //返回数据
        $this->ajaxReturn($data);
    }

    public function ajax_goods(){
        //首页底部显示用户猜你喜欢商品的数据信息
        //判断用户是否登录
        $uid = session('userinfo.uid') ? session('userinfo.uid') : 0;//测试数据，暂时使用，后期修改
        if($uid){
            //如果存在用户，获取用户喜欢表中的关键字
            $keywords = M('User')
                -> where(array('user_id' => $uid))
                -> getField('search_keywords');
            //表中用户喜欢关键字为一整个字符串，拆分成数组
            $keywords = explode(',',$keywords);
            //定义查询条件
            $where = '';
            //循环关键字的数组
            foreach ($keywords as $key => $value) {
                //拼接查询条件，商品名称存在关键字
                $where .= "goods_name like '%$value%' or ";
            }
            //去除最右边的多余字符
            $where = rtrim($where,' or ');
            $where = '('.$where.") and is_act = 0";//不是活动商品

        }else{
            $where = "";
        }
        //接收参数
        //查询商品表中符合查询条件的商品总数
        $count = M('goods') -> where($where) -> count('goods_id');
        $pagesize = 10;
        $page = new Page($count,$pagesize);
        $p = $_GET['p'] ? $_GET['p'] : 1;//当前页码
        $firstRow = $page -> firstRow;//起始行数
        $totalPages = $page -> totalPages;// 分页总页面数
        //如果当前页大于总页码
        if($p > $totalPages){
            //返回信息
            $userlikeinfo = '已经是最后一页了';
            $userlikelist = '';
        }else{
            //根据关键字查询商品表中的标题
            $userlikelist = M('goods')
                -> field('goods_id,goods_name,goods_price,goods_small_img,goods_sn')
                -> where($where)
                -> limit($firstRow,$pagesize)
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
            'info' => compact('userlikelist','pager','userlikeinfo')
        );
        //返回数据
        $this->ajaxReturn($data);
    }


}
