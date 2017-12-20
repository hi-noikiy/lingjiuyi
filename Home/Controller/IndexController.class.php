<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {

    //主页
    public function index(){

        //首页右上角分类
        if(!session('?cates')){
            $cates = D('Category') -> field('id,pid,cate_name') -> select();
            $_SESSION['cates'] = $cates;
        }

        //四个分类对应的20个商品展示
        $gmodel   = D('Goods');
        $fields   = 'goods_id i,goods_name n,goods_bigprice bp,goods_price p,goods_big_img bi,is_act';
        $addA     = ',b.id cid,b.cate_name cn';
        $g = $fields.$addA;
        $three_goods    = $gmodel -> query("SELECT $g FROM zhouyuting_goods AS a LEFT JOIN zhouyuting_category AS b ON a.cate_id=b.id
WHERE 20 > ( SELECT COUNT(*) FROM zhouyuting_goods WHERE cate_id=a.cate_id AND goods_id > a.goods_id ) AND cate_id in (246,311,213,186)
ORDER BY a.click_num DESC");
        $cate_goods       = change_array($three_goods,'cid');

        //六个分类介绍
        $six_cates = D('Category') -> alias('a')
            -> field('a.id,cate_name cn,cate_img img,count(cate_id) sum')
            -> join('join zhouyuting_goods b  on a.id = b.cate_id')
            -> limit(0,6) -> group('cate_id') -> select();
        //后期添加条件 where(pid=2)

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

    //搜索页面
    public function search(){
        if(IS_AJAX){
            $key      = I('post.keyword') ? I('post.keyword') : '';//搜索关键字
            $p        = I('post.p') ? I('post.p','','intval') : 1;//页码
            $pagesize = 20;//每页显示商品数量
            $start    = ($p - 1) * $pagesize;//开始行数
            $min      = I('post.min','','intval');
            $max      = I('post.max','','intval');
            $where = array(
                'goods_name' => ['LIKE',"%$key%"],
                'goods_price' => ['BETWEEN',$min.','.$max],
            );//搜索条件

            $order = I('post.sorts');
            $price = I('post.prices');
            if((!empty($order) || $order == '0') && empty($price)){
                $order = ($order == 'a') ? 'sale_num desc' : ($order == 'b' ? 'click_num desc' : ($order == 'c' ? 'comment_num desc' : 'goods_id asc'));
            }else{
                $order = $price;
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

    public function aboutUs(){
        $this -> display();
    }

    public function shopSingle(){
        $this -> display();
    }
    public function myAccount(){
        $this -> display();
    }
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

        $this -> ajaxReturn(compact('pic_img','new_attrs'));

    }



    public function detail(){
    	//接收商品id参数
      $id = I('get.id',0,'intval');

      if($id <= 0){
        $this->error('参数不合法',0,'intval');
      }

      /*
      //把动态的页面转化为一个真静态页面
      //判断    
      $file = "./Static/detail_{$id}.html";//如果存在纯静态文件,直接访问纯静态文件
      //如果文件存在,需要判断有效期
      if(file_exists($file) && (filemtime($file) + 3600 > time()) ){
        // echo 'i an old';die;
        //直接使用Common/function.PHP中的redirect函数直接跳转
        redirect("/Static/detail_{$id}.html");
      }*/
      //如果是第一次请求,纯静态文件不存在,需要正常访问
    	//查询商品基本信息
      $goods=M('Goods')->where(" goods_id = $id ")->find();
      $goods['click_num'] += 1;  //商品点击量加1
      M('Goods')->save($goods); //保存商品点击量
      $goods = M('Goods')->find($id);
      $this->assign('goods',$goods);

    	//获取商品的单选属性
      $attr_radio = M('Goods_attr')->alias('t1')->join("left join zhouyuting_attribute t2 on t1.attr_id=t2.attr_id")->where("t1.goods_id = $id and attr_type = 1")->select();
    	 //dump($attr_radio);die;
    	// $data =array('口味'=>array('原味','炭烧'),);
    	foreach($attr_radio as $k => $v){
        $new_attr_radio[$v['attr_id']][] = $v;
      }
    	// dump($new_attr_radio);die;
      /*
      array(2) {
        ["口味"] => array(6) {
          [0] => array(9) {
            ["id"] => string(3) "103"
            ["goods_id"] => string(2) "42"
            ["attr_id"] => string(1) "9"
            ["attr_value"] => string(6) "麻辣"
          }
        }
        ["包装"] => array(2) {
          [0] => array(9) {
            ["id"] => string(3) "107"
            ["goods_id"] => string(2) "42"
            ["attr_id"] => string(2) "13"
            ["attr_value"] => string(6) "盒装"
          }
        }
      }
      */
        //获取商品的唯一属性
      $attr_only = M('Goods_attr')->alias('t1')->join("left join zhouyuting_attribute t2 on t1.attr_id=t2.attr_id")->where("t1.goods_id = $id and t2
        .attr_type = 0")->select();

    	//获取商品的 所有相册图片
    	$goodspics = M('Goodspics')->where("goods_id = $id")->select();

    	$this->assign('attr_radio',$attr_radio);
    	$this->assign('new_attr_radio',$new_attr_radio);
    	$this->assign('attr_only',$attr_only);
    	$this->assign('goodspics',$goodspics);

      // ob_start();    //开启ob缓存
    	$this->display('detail'); //输出一些内容
      //①使用fetch方法获取原始页面所输出的内容 
      //$str = $this->fetch();
      
      /*
      $str = ob_get_contents();    //从ob缓存获取其中的内容
      $str = ob_get_clean();
      dump($str);die;
      */
     /*
      //②生成一个纯静态html页面把获取的内容放到一个文件
      $file = "./Static/detail_{$id}.html";
      file_put_contents($file,$str);
      //③以后访问原始的页面,直接替换成访问静态页面,跳转
      redirect("/Static/detail_{$id}.html");
      */
    }

//    //搜索框
//    public function search(){
//      //遍历属性表中的分类
//      $attribute = D('Attribute')->select('1,2,3');
//      foreach($attribute as $k => $v){
//        $attribute[$k]['attr_values'] = explode(',',$v['attr_values']);
//      }
//      // dump($attribute);
//      $this->assign('attribute',$attribute);
//
//      if(IS_POST){
//        $data = I('post.keyword');
//      }else{
//        $data = I('get.keyword');
//        $cate_id = I('get.id');
//      }
//      $keyword = $data;
//      //实例化模型
//      $model = D('Goods');
//      //使用分页类实现分页功能
//      //获取总记录数
//      if($cate_id){
//        $total = $model->where("category_id = {$cate_id}")->count();
//      }else{
//        $total = $model->where("goods_name LIKE '%{$keyword}%'")->count();
//      }
//
//      $pagesize = 8;
//      //实例化page类
//      $page = new \Think\Page($total,$pagesize);
//      //定制分页栏显示
//      $page->setConfig('prev','上一页');
//      $page->setConfig('next','下一页');
//      $page->setConfig('first','首页');
//      $page->setConfig('last','尾页');
//      $page->setConfig('theme',' %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
//      //修改page类的属性rollPage lastSuffix
//      $page->rollPage = 4;
//      $page->lastSuffix = false;
//      //获取分页栏代码
//      $page_html = $page->show();
//      $this->assign('page_html',$page_html);
//      //使用select查询数据 加上limit条件
//      if($cate_id){
//        $goods = $model->limit($page->firstRow,$page->listRows)->where("category_id = $cate_id")->select();
//      }else{
//        $goods = $model->limit($page->firstRow,$page->listRows)->where("goods_name LIKE '%{$keyword}%'")->select();
//      }
//
//      //变量赋值
//      $this->assign('goods',$goods);
//
//      $this->display();
//    }
}