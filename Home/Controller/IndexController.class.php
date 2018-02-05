<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {

    //主页
    public function index(){

        //首页右上角分类
        if(!session('?cates')){
            $cates = D('Category') -> field('id,pid,cate_name') -> where("`is_show` = 1") -> select();
            $_SESSION['cates'] = $cates;
        }

        //四个分类对应的20个商品展示
        $gmodel   = D('Goods');
        $fields   = 'goods_id i,goods_name n,goods_bigprice bp,goods_price p,goods_big_img bi,goods_small_img si,is_act';
        $addA     = ',b.id cid,b.cate_name cn';
        $g = $fields.$addA;
        $three_goods    = $gmodel -> query("SELECT $g FROM zhouyuting_goods AS a LEFT JOIN zhouyuting_category AS b ON a.cate_id=b.id
WHERE 20 > ( SELECT COUNT(*) FROM zhouyuting_goods WHERE cate_id=a.cate_id AND goods_id > a.goods_id ) AND cate_id in (252,253,254,256)
ORDER BY a.click_num DESC");
        $cate_goods       = change_array($three_goods,'cid');

        //六个分类介绍
        $six_cates = D('Category') -> alias('a')
            -> field('d.id,d.cate_name cn,d.cate_small_img img,count(cate_id) sum')
            -> join('join zhouyuting_goods b on a.id = b.cate_id')
            -> join('join zhouyuting_category c on a.pid = c.id')
            -> join('join zhouyuting_category d on c.pid = d.id')
            -> limit(0,12) -> group('d.id') -> select();

        //六个店铺对应的16个商品展示
        $joinB      = 'left join zhouyuting_shop b on zhouyuting_goods.shop_id = b.id';
        $addB       = ',b.id spid,b.shop_name';
        $shop_goods = $gmodel -> field($fields.$addB) -> join($joinB) -> order('click_num desc') -> select();
        //后期添加条件where(is_act=1),并且每个店铺规定活动商品数量
        $new_shop_goods = change_array($shop_goods,'spid');

        //今日限时活动商品,4个商品
        $act_goods = $gmodel -> field($fields) -> limit(0,4) -> order('click_num desc') -> select();//后期设置一个今日活动的专题

        //三个大类商品，一个分类下6个商品
        $f = $fields.$addA;
        $three_goods    = $gmodel -> query("SELECT $f FROM zhouyuting_goods AS a LEFT JOIN zhouyuting_category AS b ON a.cate_id=b.id
WHERE 6 > ( SELECT COUNT(*) FROM zhouyuting_goods WHERE cate_id=a.cate_id AND goods_id > a.goods_id ) AND cate_id in (167,169,186)
ORDER BY a.click_num DESC");
        $new_three_goods = change_array($three_goods,'cid');

        $this -> assign('cate_goods',$cate_goods);
        $this -> assign('six_cates',$six_cates);
        $this -> assign('new_shop_goods',$new_shop_goods);
        $this -> assign('act_goods',$act_goods);
        $this -> assign('new_three_goods',$new_three_goods);
        $this -> display();
    }

    //购物车列表
    public function cart(){
        $this -> check_login();
        $uid = session('userinfo.id');
        $fields = 'a.id ci, a.goods_id gi,a.number num,b.goods_name gn,b.goods_price p,goods_bigprice bp,goods_small_img simg';
        $cartlist = D('Cart') -> alias('a') -> field($fields) -> where("user_id = $uid") -> join('zhouyuting_goods b on a.goods_id = b.goods_id') -> select();
        $cartlist ? $this -> ajaxReturnData(10000,'success',$cartlist) : $this -> ajaxReturnData(0,'购物车为空');
    }

    //搜索页面
    public function search(){
        if(IS_AJAX){
            $key      = I('post.keyword','','string') ? I('post.keyword','','string') : '';//搜索关键字
            $p        = I('post.p','','intval') ? I('post.p','','intval') : 1;//页码

            $pagesize = 20;//每页显示商品数量
            $start    = ($p - 1) * $pagesize;//开始行数

            $min      = I('post.min','','intval') || (I('post.min','','intval') == 0) ? I('post.min','','intval') : 0;//最低价格
            $max      = I('post.max','','intval') || (I('post.max','','intval') == 0) ? I('post.max','','intval') : 100000;//最高价格
            $cate     = I('post.cates','','intval');//分类
            if(!empty($cate)){
                $cates    = D('Category') -> alias('a') -> field('c.id') -> where("a.id = $cate") -> join('zhouyuting_category b on a.id = b.pid') -> join('zhouyuting_category c on b.id = c.pid') -> select();
                foreach($cates as $k => $v){
                    $cates[$k] = $cates[$k]['id'];
                }
                $ids = implode(',',$cates);
            }
            unset($k,$v);


            $where = array(
                'goods_name' => ['LIKE',"%$key%"], //查询商品名称
                'goods_price' => ['BETWEEN',$min.','.$max], //查询商品价格区间
            );//搜索条件

            if(isset($ids)){
                $where['cate_id'] = ['IN',$ids];
            }

            $order = I('post.sorts');//排序
            $price = I('post.prices');//价格排序
            if((!empty($order) || $order == '0') && empty($price)){
                $order = ($order == 'a') ? 'sale_num desc' : ($order == 'b' ? 'click_num desc' : ($order == 'c' ? 'comment_num desc' : 'goods_id asc'));//根据参数拼接排序规则
            }else{
                $order = $price; //按照价格排序
                $order = $order == 'ltoh' ? 'goods_price asc' : 'goods_price desc' ;
            }


            $fields = 'goods_id i,goods_name n,goods_bigprice bp,goods_price p,goods_big_img bi,is_act';
            $count  = D('Goods') -> where($where) -> count();
            $list = D('Goods') -> field($fields) -> where($where) -> limit($start,$pagesize) -> order($order) -> select();
            empty($list) && ($p === 1) ? $this -> ajaxReturnData(0,'到底了！',compact('count','p','pagesize')) : true;
            empty($list) ? $this->ajaxReturnData(10001,'没有搜索到您想要的商品！',compact('list','count','p','pagesize')) : $this->ajaxReturnData(10000,'success',compact('list','count','p','pagesize','start'));

        }else{
            $category = D('Category') -> field('id ci,cate_name cn') -> where('pid = 0') -> select();
            $this -> assign('cate',$category);
            $this -> display();
        }
    }

    //加入我们
    public function contact(){
        $this -> display();
    }

    //商品详情页弹窗
    public function goodsDetail(){
        $id      = I('get.id','','intval');
        $goods   = D('Goods') -> where(" goods_id = $id ") -> find();
        $goods['click_num'] += 1;
        D('Goods') -> save($goods);
        //获取商品相册
        $pic_img = D('Goodspics') -> field('pics_big pb,pics_sma ps') -> where("goods_id = $id") -> select();
        //获取商品属性
        $attrs   = D('goods_attr') -> alias('a') -> field('a.id gaid,a.attr_id ai,a.attr_value value,b.attr_name name,b.attr_type type') -> where("goods_id = $id") -> join('zhouyuting_attribute b on a.attr_id = b.attr_id') -> select();
        $new_attrs = change_array($attrs,'ai');

        $number = array(
            'sales_num' => $goods['goods_sales'], //购买数
            'click_num' => $goods['click_num'],//查看数
            'heart_num' => D('User_goods') -> where(" goods_id = $id ") -> count('goods_id'), //收藏数
            'comme_num' => D('Comment') -> where(" goods_id = $id ") -> count('goods_id')  //评论数
        );

        $this -> ajaxReturn(compact('pic_img','new_attrs','number'));

    }

    //底部数据
    public function get_footer(){
        $setting = D('Setting') -> field('`desc` show_name,content,type') -> where("is_show = 1") -> select();//基本设置
        empty($setting) ? $this -> ajaxReturnData(0,'没有数据') : $this -> ajaxReturnData(10000,'success',$setting);
    }

    //分类商品
    public function cate(){
        if(IS_AJAX){
            $p        = I('post.p','','intval') ? I('post.p','','intval') : 1;//页码

            $pagesize = 20;//每页显示商品数量
            $start    = ($p - 1) * $pagesize;//开始行数

            $min      = I('post.min','','intval') || (I('post.min','','intval') == 0) ? I('post.min','','intval') : 0;//最低价格
            $max      = I('post.max','','intval') || (I('post.max','','intval') == 0) ? I('post.max','','intval') : 100000;//最高价格
            $cate     = I('post.cates','','intval') ? I('post.cates','','intval') : (I('get.id','','intval') ? I('get.id','','intval') : 0);//分类

            empty($cate) ? $this -> ajaxReturnData(0,'分类不存在!') : $where = "`cate_id` = $cate AND";
            $where .= "`goods_price` BETWEEN $min AND $max";
            $order = I('post.sorts');//排序
            $price = I('post.prices');//价格排序
            if((!empty($order) || $order == '0') && empty($price)){
                $order = ($order == 'a') ? 'sale_num desc' : ($order == 'b' ? 'click_num desc' : ($order == 'c' ? 'comment_num desc' : 'goods_id asc'));//根据参数拼接排序规则
            }else{
                $order = $price; //按照价格排序
                $order = $order == 'ltoh' ? 'goods_price asc' : 'goods_price desc' ;
            }


            $fields = 'goods_id i,goods_name n,goods_bigprice bp,goods_price p,goods_big_img bi,is_act';
            $count  = D('Goods') -> where($where) -> count();
            $list = D('Goods') -> field($fields) -> where($where) -> limit($start,$pagesize) -> order($order) -> select();
            empty($list) && ($p === 1) ? $this -> ajaxReturnData(0,'到底了！',compact('count','p','pagesize')) : true;
            empty($list) ? $this->ajaxReturnData(10001,'没有搜索到您想要的商品！',compact('list','count','p','pagesize')) : $this->ajaxReturnData(10000,'success',compact('list','count','p','pagesize','start'));

        }else{
            $cate_id = I('get.id','','intval') ? I('get.id','','intval') : 0;
            $pid = D('Category') -> where("id = $cate_id") -> getField('pid');//pid
            if(!empty($pid)){
                $category = D('Category') -> field('id,cate_name cn') -> where("pid = $pid")-> select();
                $this -> assign('cate',$category);
            }
            $this -> display();
        }
    }


}