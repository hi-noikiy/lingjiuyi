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
        $uid = session('userinfo.id');

        $User = D('User') -> where("id = $uid") -> find();//用户基本信息
        $User['birthday_Y'] = substr($User['birthday'],0,4);
        $User['birthday_M'] = substr($User['birthday'],5,2);
        $User['birthday_D'] = substr($User['birthday'],8,2);
        $User['create_time'] = date('Y-m-d',$User['create_time']);
        $User['last_login_time'] = date('Y-m-d',$User['last_login_time']);

        $Addr = D('Address') -> where("user_id = $uid") -> select();//收货地址

        $Order = $this -> get_order(1);//获取订单信息
        $OrderCount = count($Order);//订单总数

        $Heart = $this -> get_heart(1);//获取我的收藏信息
        $HeartCount = count($Heart);//收藏商品总数

        $this -> assign('User',$User);//账户信息
        $this -> assign('Addr',$Addr);//地址信息
        $this -> assign('Order',$Order);//订单信息
        $this -> assign('Heart',$Heart);//我的收藏
        $this -> assign('OrderCount',$OrderCount);//我的收藏
        $this -> assign('HeartCount',$HeartCount);//我的收藏
        $this -> display();
    }

    //ajax获取订单列表
    public function ajax_order(){
        I('get.p','','intval') ? $p = I('get.p','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $OrderList = $this -> get_order($p);//获取订单信息
        foreach($OrderList as $key => $value){
            $Order[$key]['order_id'] = $value['id'];
            $Order[$key]['bianhao']  = $value['order_sn'];
            $Order[$key]['total']    = $value['order_amount'];
            $Order[$key]['add_time'] = $value['create_time'];
            $Order[$key]['o_status'] = $value['order_status'];
            $Order[$key]['address']  = $value['address_info'];
            $Order[$key]['s_type']   = $value['shipping_type'];
            $Order[$key]['p_status'] = $value['pay_status'];
            $Order[$key]['p_type']   = $value['pay_type'];
            $Order[$key]['status']   = $value['status'];
            foreach($value['Order_goods'] as $k => $v){
                $Order[$key]['Order_goods'][$k]['gid']   = $v['goods_id'];
                $Order[$key]['Order_goods'][$k]['simg']  = $v['goods_small_img'];
                $Order[$key]['Order_goods'][$k]['price'] = $v['goods_price'];
                $Order[$key]['Order_goods'][$k]['name']  = $v['goods_name'];
                $Order[$key]['Order_goods'][$k]['num']   = $v['number'];
            }
        }
        empty($Order) ? $this -> ajaxReturnData(0,'没有更多了') : $this -> ajaxReturnData(10000,'success',$Order);
    }

    //获取订单信息
    public function get_order($p){
        $this -> check_login();
        $uid = session('userinfo.id');

        $pagesize = 10;
        $start    = ($p - 1) * $pagesize;

        $fieldA = "id,order_sn,order_amount,create_time,order_status,address_info,shipping_type,pay_status,pay_type";
        $Order = D('Order') -> field($fieldA) ->  where("user_id = $uid") -> relation(true) -> limit($start,$pagesize) -> order('update_time desc') -> select();

        $goods_ids = '';
        $attr_ids  = '';
        $order_ids = '';
        foreach($Order as $key => $value){
            foreach($value['Order_goods'] as $k => $v){
                $goods_ids .= $v['goods_id'].',';
                $attr_ids  .= $v['goods_attr_ids'].',';
                $order_ids .= $value['id'];
                $Order[$key]['create_time']   = date('Y/m/d H:i',$value['create_time']);
                $Order[$key]['status']        = order_status($Order[$key]['order_status']);
                $Order[$key]['shipping_type'] = shipping_type($Order[$key]['shipping_type']);
                $Order[$key]['pay_status']    = pay_status($Order[$key]['pay_status']);
                $Order[$key]['pay_type']      = pay_type($Order[$key]['pay_type']);
            }
        }
        unset($key,$value,$k,$v);

        $Order_img  = D('Goods') -> field('goods_id,goods_name,goods_price,goods_small_img') -> where(['goods_id' => ['in',$goods_ids]]) -> select();//商品信息
        $Order_attr = D('Goods_attr') -> field('id,attr_value') -> where(['id' => ['in',$attr_ids],'order_id' => ['in',$order_ids]]) -> select();//商品属性信息

        foreach($Order as $key => $value){
            foreach($value['Order_goods'] as $k => $v){
                $Order[$key]['Order_goods'][$k]['goods_attr_ids'] = explode(',',$v['goods_attr_ids']);
                foreach($Order_img as $gk => $gv){
                    if( $v['goods_id'] == $gv['goods_id'] ){
                        //循环添加商品信息
                        $Order[$key]['Order_goods'][$k]['goods_name']      = $gv['goods_name'];
                        $Order[$key]['Order_goods'][$k]['goods_price']     = $gv['goods_price'];
                        $Order[$key]['Order_goods'][$k]['goods_small_img'] = $gv['goods_small_img'];
                    }
                }
            }
        }
        unset($key,$value,$k,$v,$gk,$gv);

        foreach($Order as $key => $value){
            foreach($value['Order_goods'] as $k => $v){
                foreach($v['goods_attr_ids'] as $gattrk => $gattrv){
                    foreach($Order_attr as $attrk => $attrv){
                        //循环添加商品属性信息
                        if( $gattrv == $attrv['id'] ){
                            $Order[$key]['Order_goods'][$k]['attr_ids'][] = $attrv['attr_value'];

                        }
                    }
                }
                $Order[$key]['Order_goods'][$k]['attr_ids'] = implode(',',$Order[$key]['Order_goods'][$k]['attr_ids']);
            }
        }
        unset($key,$value,$k,$v,$gattrk,$gattrv,$attrk,$attrv);

        return $Order;
    }

    //ajax获取我的收藏
    public function ajax_heart(){
        I('get.p','','intval') ? $p = I('get.p','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $HeartList = $this -> get_heart($p);//获取我的收藏
        empty($HeartList) ? $this -> ajaxReturnData(0,'没有更多了') : $this -> ajaxReturnData(10000,'success',$HeartList);
    }

    //获取我的收藏
    public function get_heart($p){
        $this -> check_login();
        $p = isset($p) ? $p : (I('get.p','','intval') ? I('get.p','','intval') : 0);
        empty($p) ? $this -> ajaxReturnData(0,'参数不正确') : true;

        $uid = session('userinfo.id');
        $pagesize = 10;
        $start = ($p - 1) * $pagesize;
        $fileds = 'b.goods_id i,goods_name an,goods_bigprice gb,goods_price gp,goods_small_img si,is_act';
        $List = D('User_goods') -> alias('a') -> field($fileds) -> where("user_id = $uid AND is_normal = 1") -> join('zhouyuting_goods b on a.goods_id = b.goods_id') -> limit($start,$pagesize) -> order('a.add_time desc') -> select();
        return $List;
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
        $res == false ? $this -> ajaxReturnData(0,'修改失败') : $this -> ajaxReturnData();
    }

    //删除收货地址
    public function del_myAddress(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'访问方式错误') : true;
        $this -> check_login();
        I('post.id','','intval') ? $id = I('post.id','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $res = D('Address') -> delete($id);
        $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'删除失败');
    }

    //修改用户头像
    public function upload_img(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'访问方式错误') : true;
        $this -> check_login();
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

        $subscribe = I('post.subscribe','','intval');//接收订阅参数
        if($subscribe === 0 || $subscribe === 1){
            $data['is_subscribe'] = $subscribe;//判断订阅参数是否正确,如果正确则只是修改订阅信息
            $res = M('User') -> save($data);
            $res !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'修改订阅失败！');
        }

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

    //修改登录密码
    public function edit_Mypass(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'访问方式错误') : true;
        $this -> check_login();
        $model = D('User');
        if (!$model -> create()){
            $msg = $model -> getError();
            $this -> ajaxReturnData(0,$msg);
        }else{
            I('post.type','','string') ? $type = I('post.type','','string') : $this -> ajaxReturnData('类型参数错误');
            if($type == 'email'){
                //邮箱验证，有效时间为30分钟 60*30=1800
                $sendtime = session('sendtime');//设置过期时间//获取过期时间
                time() > ($sendtime + 1800) ? $this -> ajaxReturnData(0,'此验证码已无效，请重新发送！') : true;//当前时间不在有效期内
                $uid = session('userinfo.id');
                $user = D('User') -> where("id = $uid") -> find();//验证码是否正确

                $user['email_code'] == I('post.code','','string') ? true : $this -> ajaxReturnData(0,'验证码错误');
                (password_verify(I('post.oldpassword','','string'),$user['password']) === true) ? true : $this -> ajaxReturnData(0,'旧密码错误');

                $data['id'] = $uid;
                I('post.password','','string') ? $data['password'] = I('post.password','','string') : $this -> ajaxReturnData(0,'新密码不正确！');
                $data['password'] = encrypt_pwd($data['password']);
                $res = M('User') -> save($data);
                if($res !== false){
                    session(null);
                    $this -> ajaxReturnData();
                }else{
                    $this -> ajaxReturnData(0,'修改密码失败');
                }


            }elseif($type == 'phone'){
                //手机号验证,短信验证
            }else{
                $this -> ajaxReturnData('类型参数错误');
            }
        }
    }


}