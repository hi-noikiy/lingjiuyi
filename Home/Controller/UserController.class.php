<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
require_once './Application/Tools/Qiniu/autoload.php';
use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;
use Qiniu\Storage\BucketManager;

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
        $this -> check_login();
        $User = D('User') -> where(['id' => session('userinfo.id')]) -> find();//用户基本信息
        $User['birthday_Y'] = substr($User['birthday'],0,4);
        $User['birthday_M'] = substr($User['birthday'],5,2);
        $User['birthday_D'] = substr($User['birthday'],8,2);
        $User['create_time'] = date('Y-m-d',$User['create_time']);
        $User['last_login_time'] = date('Y-m-d',$User['last_login_time']);
        $this -> assign('User',$User);
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

    //修改用户头像
    public function upload_img(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'访问方式错误') : true;
        //注意 ： base64已经修改，其余的没有修改
        $type      = $_POST['type'] ? $_POST['type'] : false;
        $setting   = C('UPLOAD_QINIU');

        $accessKey = $setting['driverConfig']['accessKey'];
        $secretKey = $setting['driverConfig']['secretKey'];
        $bucket    = $setting['driverConfig']['bucket'];
        $auth      = new Auth($accessKey, $secretKey);
        $config    = new \Qiniu\Config();
        if($type == 'base64'){
            //上传头像
            $image     = $_POST['data'];
            if (strstr($image,",")){
                $image = explode(',',$image);
                $image = $image[1];
            }//去除base64中需要的部分

            $upToken   = $auth->uploadToken($bucket, null, 3600); //获取token
            $result    = request_by_curl('http://upload.qiniu.com/putb64/-1',$image,$upToken);//上传七牛云
            $info      = json_decode($result,true);

            if(array_key_exists('error',$info) === true){
                $data['msg']  = '上传图片失败！';
                $data['code'] = 0;
                echo json_encode($data);die;
            }
        }else{
            //上传商品图片
            $Upload  = new \Think\Upload($setting);
            $info    = $Upload -> upload($_FILES);
        }
        if($type == 'base64'){
            //上传头像
            if($info['error']){
                $data['msg']  = '上传图片失败！';
                $data['code'] = 0;
            }else{
                $user['id'] = session('userinfo.id');
                $user['header_img']   =  'http://'.$setting['driverConfig']['domain'].'/'.$info['key'];
                $header_img           = D('User') -> where(['id' => $user['id']]) -> getField('header_img');
                $key                  = substr($header_img,strrpos($header_img,'/') + 1);
                $substr_key           = substr($header_img,strrpos($header_img,'//') + 1,25);

                if($key != "admin_header_img.jpg" && $substr_key  == "owtcx73yl.bkt.clouddn.com"){
                    //如果当前头像不等于默认头像，并且是七牛云空间中的图片，则删除
                    $bucketManager = new BucketManager($auth, $config);
                    $err = $bucketManager -> delete($bucket, $key);
                    $err ? $this -> ajaxReturnData(0,'删除源文件失败') : true;
                }

                $res = D('User') -> save($user);
                session('header_img',$user['header_img']);
                $res !== false ? true : $this -> ajaxReturnData(0,'保存头像失败');
                $data['msg']  = '上传图片成功！';
                $data['code'] = 10000;
                $data['info'] = $user['header_img'];
            }
        }else{
            //上传商品图片
            if(empty($info)){
                $data['msg']  = '上传图片失败！';
                $data['code'] = 0;
            }else{
                $data['msg']  = '上传图片成功！';
                $data['code'] = 10000;
                $data['info'] = $info['upload_img']['url'] ? $info['upload_img']['url'] : $info['shop_logo']['url'];
            }
        }

        echo json_encode($data);die;

    }

    //修改用户信息
    public function edit_Userinfo(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'访问方式错误') : true;
        $this -> check_login();
        $data['id'] = session('userinfo.id');
        I('post.username','','string') ? $data['username']  = I('post.username','','string')  : $this -> ajaxReturnData(0,'用户名不能为空');
        I('post.gender','','string')   ? $data['gender']    = I('post.gender','','string')    : $this -> ajaxReturnData(0,'性别不能为空');
        I('post.year','','string')     ? $data['birthday']  = I('post.year','','string').'-'  : $this -> ajaxReturnData(0,'出生年份不能为空');
        I('post.month','','string')    ? $data['birthday'] .= I('post.month','','string').'-' : $this -> ajaxReturnData(0,'出生月份不能为空');
        I('post.day','','string')      ? $data['birthday'] .= I('post.day','','string')    : $this -> ajaxReturnData(0,'出生日期不能为空');
        I('post.email','','string')    ? $data['email']     = I('post.email','','string')  : false;
        I('post.phone','','string')    ? $data['phone']     = I('post.phone','','string')  : false;
        I('post.weixin','','string')   ? $data['weixin']    = I('post.weixin','','string') : false;
        I('post.qq','','string')       ? $data['qq']        = I('post.qq','','string')     : false;
        I('post.weibo','','string')    ? $data['weibo']     = I('post.weibo','','string')  : false;

        isset($data['email']) && (filter_var($data['email'], FILTER_VALIDATE_EMAIL) == false) ? $this -> ajaxReturnData(0,'邮箱格式不正确') : true;
        isset($data['phone']) && (preg_match('/^\d{11}$/', $data['phone']) == false) ? $this -> ajaxReturnData(0,'手机号码格式不正确') : true;

        $map['id'] = ['NEQ', $data['id']];
        $userinfo = D('User') -> field('username,phone,email') -> where($map) -> select();
        foreach($userinfo as $key => $value){
            $user['username'][] = $userinfo[$key]['username'];
            $user['phone'][]    = $userinfo[$key]['phone'];
            $user['email'][]    = $userinfo[$key]['email'];
        }
        unset($key,$value);
        foreach($user as $key => $value){
            $user[$key] = array_flip($user[$key]);
        }
        unset($key,$value);
        isset($data['username']) && (array_key_exists($data['username'], $user['username']) == true) ? $this -> ajaxReturnData(0,'用户名已经存在') : true;
        isset($data['email'])    && (array_key_exists($data['email'], $user['email']) == true) ? $this -> ajaxReturnData(0,'邮箱已经存在') : true;
        isset($data['phone'])    && (array_key_exists($data['phone'], $user['phone']) == true) ? $this -> ajaxReturnData(0,'手机号已经存在') : true;
        $res = D('User') -> save($data);
        $res !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'修改失败');


    }


}