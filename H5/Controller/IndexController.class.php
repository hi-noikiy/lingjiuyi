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

    //顶部滑动banner 和店铺小图标
    public function get_banner(){
        $shoplist = M('Shop')-> field('id,shop_logo,shop_header_img') -> where(array('is_top' => 1)) -> select();
        empty($shoplist) ? $this -> ajaxReturnData(0,'无banner') : $this -> ajaxReturnSuccess($shoplist);
    }

    //获取分类cate_banner
    public function get_cate_banner(){
        $catelist = M('Category') -> field('id,cate_img') -> where(array('pid' => 0)) -> select();
        empty($catelist) ? $this -> ajaxReturnData(0,'无分类banner') : $this -> ajaxReturnSuccess($catelist);
    }

    public function ajax_goods(){
        //首页底部显示用户猜你喜欢商品的数据信息
        //判断用户是否登录
        $uid = session('userinfo.uid') ? session('userinfo.uid') : 0;//测试数据，暂时使用，后期修改
        if($uid){
            //如果存在用户，获取用户喜欢表中的关键字
            $keywords = M('User') -> where(array('user_id' => $uid)) -> getField('search_keywords');
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

        $userlikelist = M('goods') -> field('goods_id,goods_name,goods_price,goods_small_img,goods_sn') -> where($where) -> limit($firstRow,$pagesize) -> select();

        //根据关键字查询商品表中的标题
        $pagesize = ($count < $pagesize) ? $count : $pagesize;
        $pager = array(
            'nowPage'    => (int)$p,
            'pagesize'   => (int)$pagesize,
            'totalPages' => $totalPages
        );

        empty($userlikelist) ? $this -> ajaxReturnData(0,'无数据') : $this -> ajaxReturnSuccess(compact('userlikelist','pager'));
    }


}
