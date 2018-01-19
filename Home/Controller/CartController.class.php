<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
class CartController extends CommonController{

	//加入购物车
    public function add(){
        !IS_POST &&IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误') : true;
        $this -> check_login();

        $model = D('Cart');

        $data = array(
            'user_id'        => session('userinfo.id'),
            'goods_id'       => I('post.id','','intval'),
            'goods_attr_ids' => I('post.ids','','string'),
            'number'         => I('post.num','','intval'),
            'update_time'    => date('Y-m-d'),
            'add_time'       => time()
        );
        $where = array(
            'user_id'  => $data['user_id'],
            'goods_id' => $data['goods_id']
        );

        $num = $model -> where($where) -> getField('number');//查询购物车中是否存在相同商品
        if($num !== null){
            $add['number'] = $data['number'] + $num;
            $res = $model -> where($where) -> save($add);
        }else{
            $res = $model  -> add($data);
        }
        $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'添加购物车失败！');
    }

    //删除购物车
    public function del_cart(){
        !IS_POST &&IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误') : true;
        $this -> check_login();

        I('post.id','','intval') ? $id = I('post.id','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $res = D('Cart') -> delete($id);
        $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'删除失败！');
    }

    //结算页面
    public function index(){
        $this -> check_login();
        $uid = session('userinfo.id');
        $field = 'a.id cart_id,a.goods_id gid,a.goods_attr_ids,a.number num,b.goods_name name,b.goods_price price,b.goods_bigprice big_price,b.goods_small_img img';
        $carts = D('Cart') -> alias('a') -> field($field) -> where("a.user_id = $uid") -> join('zhouyuting_goods b on a.goods_id = b.goods_id')  -> select();//商品基本信息
        unset($field);
        foreach($carts as $key => $value){
            $map['a.id'] = ['in',$carts[$key]['goods_attr_ids']];
            $field = 'a.id,a.attr_value,b.attr_name';
            $attrs = D('Goods_attr') -> alias('a') -> field($field) -> where($map) -> join('zhouyuting_attribute b on a.attr_id = b.attr_id') -> select();//商品属性信息
            $carts[$key]['goods_attr_ids'] = $attrs;
        }
        $addr = D('Address') -> where("user_id = $uid AND default_addr = 1") -> find();
        if(empty($addr)){
            $addr = D('Address') -> where("user_id = $uid") -> find();
        }
        $this -> assign('carts',$carts);
        $this -> assign('addr',$addr);
        $this -> display();
    }

    //获取邮政编码
    public function getcode(){
        $keyword = I('get.address');//地址关键字
        $url = "http://cpdc.chinapost.com.cn/web/index.php?m=postsearch&c=index&a=ajax_addr&searchkey=".$keyword ;
        $res = curl_request($url,false);
        echo $res;
    }

    //查询商品库存
    public function get_stock(){
        $gattr_id = I('get.attr_id','','string');//商品属性关联表中主键
        $stock = D('Goods_stock') -> where("gattr_id = '$gattr_id'") -> getField('number');//库存量
        empty($stock) ? $this -> ajaxReturnData(0,'没有库存') : $this -> ajaxReturnData(10000,'success',$stock);
    }


}