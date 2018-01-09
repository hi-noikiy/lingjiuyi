<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
class UserController extends CommonController{

    //加入收藏
    public function heart()
    {
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0, '访问方式错误') : true;
        $this -> check_login();

        $goods_id = I('post.id', '', 'intval');
        empty($data['goods_id']) ? $this -> ajaxReturnData(0, '参数错误') : true;

        $data = array(
            'goods_id' => $goods_id,
            'user_id' => session('userinfo.id'),
            'add_time' => time()
        );

        $model = D('User_goods');

        $rep = $model -> where(['user_id' => $data['user_id']]) -> find();
        $rep ? $this -> ajaxReturnData(0,'此商品您已经收藏过了哦！') : true;

        $res = $model-> add($data);
        $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'收藏失败！');

    }

    //订阅
    public function subscribe(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0, '访问方式错误') : true;
        $this -> check_login();
        $uid = session('userinfo.id');

        $is_subscribe = D('User') -> where("id = $uid") -> getField('is_subscribe');
        $is_subscribe == 1 ? $this -> ajaxReturnData(0,'已经订阅，无需重复订阅') : true;

        $data = array(
            'email'        =>  I('post.id','','string'),
            'id'           => $uid,
            'is_subscribe' => 1
        );
        $res = D('User') -> save($data);
        $res != false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'订阅失败');
    }

    //我的账户
    public function index(){
        $this -> display();
    }


}