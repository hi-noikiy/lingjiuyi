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

        $id = I('post.id','','intval');
        $res = D('Cart') -> delete($id);
        $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'删除失败！');
    }


}