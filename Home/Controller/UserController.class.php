<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
class UserController extends CommonController{

    //加入收藏
    public function heart()
    {
        $this -> check_login();

        $data['goods_id'] = I('post.id', '', 'intval');
        empty($data['goods_id']) ? $this -> ajaxReturnData(0, '参数错误') : true;

        $data['user_id'] = session('userinfo.id');
        $data['add_time'] = time();
        $model = D('User_goods');
        $rep = $model -> where(['user_id' => $data['user_id']]) -> find();
        $rep ? $this -> ajaxReturnData(0,'此商品您已经收藏过了哦！') : true;
        $res = $model-> add($data);
        $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'收藏失败！');

    }


}