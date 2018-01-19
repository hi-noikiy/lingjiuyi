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
        empty($goods_id) ? $this -> ajaxReturnData(0, '参数错误') : true;

        $data = array(
            'goods_id' => $goods_id,
            'user_id' => session('userinfo.id'),
            'add_time' => time()
        );

        $model = D('User_goods');

        $where = array(
            'user_id'  => $data['user_id'],
            'goods_id' => $data['goods_id'],
        );

        $rep = $model -> where($where) -> find();
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

    //我的地址列表
    public function myAddress_list(){
        !IS_GET && !IS_AJAX ? $this -> ajaxReturnData(0, '访问方式错误') : true;
        $this -> check_login();
        $uid = session('userinfo.id');
        $res = D('Address') -> where("user_id = $uid") -> select();
        empty($res) ? $this -> ajaxReturnData(0,'没有地址') : $this -> ajaxReturnData(10000,'success',$res);
    }

    //添加收货地址
    public function add_myAddress(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0, '访问方式错误') : true;
        $this -> check_login();
        $model = D('Address');
        $uid = session('userinfo.id');
        if (!$model -> create()){
            $msg = $model -> getError();
            $this -> ajaxReturnData(0,$msg);
        }else{
            $data = array(
                'user_id' => $uid,
                'consignee' => I('post.name','','string'),
                'address' => I('post.address','','string').I('post.addrinfo','','string'),
                'phone' => I('post.tele','','string'),
                'zipcode' => I('post.zip','','string'),
            );
            $res = M('Address') -> add($data);
            empty($res) ? $this -> ajaxReturnData(0,'添加地址失败') : $this -> ajaxReturnData();
        }
    }

    //修改收货地址
    public function edit_myAddress(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0, '访问方式错误') : true;
        $this -> check_login();
        I('post.is_default') || I('post.is_default') == '0' ? $data['default_addr'] = I('post.is_default') : false;
        I('post.address_id') ? $data['id'] = I('post.address_id') : $this -> ajaxReturnData(0,'参数错误');//地址主键
        $uid = D('Address') -> where(['id' => $data['id']]) -> getField('user_id');//获取地址对应用户id
        $id  = D('Address') -> where("default_addr = 1 AND user_id = $uid") -> getField('id');//用户默认地址
        if($data['default_addr'] == 1 && isset($id)){
            //如果是设置默认地址，则将原来是默认地址的修改
            $save['id'] = $id;
            $save['default_addr'] = 0;
            D('Address') -> save($save);
        }
        session('userinfo.id') != $uid ? $this -> ajaxReturnData(0,'地址与用户不一致') : true;
        $res = D('Address') -> save($data);//保存修改
        $res != false ? $this -> ajaxReturnData(0,'修改失败') : $this -> ajaxReturnData();
    }


}