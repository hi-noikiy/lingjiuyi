<?php
//1、定义命名空间
namespace H5\Controller;
//2、引入核心控制器
use Think\Controller;
use Think\Upload;
require_once './Application/Tools/Qiniu/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
//3、定义News控制器
class UserController extends CommonController {

    //登录展示页面
    public function login(){
        layout(false);
        $this -> display();
    }

    //注册页面
    public function register(){
        layout(false);
        $this -> display();
    }

    //平台协议
    public function reg_intro(){
        layout(false);
        $this -> display();
    }

    //用户首页
    public function my(){
        $type = I('post.type','','string');
        if(IS_POST && $type === 'upload_header_img'){
            //上传头像
            $setting   = C('UPLOAD_QINIU');
            $Upload  = new \Think\Upload($setting);
            $info    = $Upload -> upload($_FILES);//上传
            $data['id']      = session('userinfo.id');
            $data['header_img']  = $info['basicInfoHead']['url'];

            $header_img           = D('User') -> where(['id' => $data['id']]) -> getField('header_img');//获取原图片
            $key                  = substr($header_img,strrpos($header_img,'/') + 1);
            if(!empty($header_img)){
                //如果原图片不为空
                $accessKey = $setting['driverConfig']['accessKey'];
                $secretKey = $setting['driverConfig']['secretKey'];
                $bucket    = $setting['driverConfig']['bucket'];
                $auth      = new Auth($accessKey, $secretKey);
                $config    = new \Qiniu\Config();
                $bucketManager = new BucketManager($auth, $config);
                $err = $bucketManager -> delete($bucket, $key);//删除源文件
                $err ? $this -> ajaxReturnData(0,'删除源文件失败') : true;
            }

            $res = D('User') -> save($data);
            session('userinfo.header_img',$data['header_img']);
            $res !== false ? $this -> ajaxReturnSuccess($data['header_img']) : $this -> ajaxReturnData(0,'保存头像失败');
        }
        $this -> display();
    }

    //用户基本信息
    public function profile(){
        $this -> display();
    }

    //普通订单显示
    public function order(){
        $this -> display();
    }

    //我的钱包显示
    public function wallet(){
        $this -> display();
    }

    //我的钱包充值
    public function recharge(){
        $this -> display();
    }

    //我的钱包提现
    public function cash(){
        $this -> display();
    }

    //我的地址
    public function address(){
        layout(false);
        $this -> display();
    }

    //玩法说明
    public function explain(){
        layout(false);
        $this -> display();
    }

    //其他设置
    public function other_settings(){
        $this -> display();
    }

    //修改密码
    public function edit_password(){
        if(IS_POST && IS_AJAX){
            I('post.type','','string') ? $type = I('post.type','','string') : $this -> ajaxReturnData(0,'参数错误');
            if($type === 're_pass'){
                //未登录，用户忘记密码
                $check_code = $this -> check_repass_code();
                $check_code === true ? true : $this -> ajaxReturnData(0,'手机号验证未通过！');
                I('post.password','','string')   ? $password = I('post.password','','string')     : $this -> ajaxReturnData(0,'密码不能为空！');
                I('post.repassword','','string') ? $repassword = I('post.repassword','','string') : $this -> ajaxReturnData(0,'确认密码不能为空！');
                preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/', $password)   == false ? $this -> ajaxReturnData(0,'密码格式不正确，只能6-20位数字和字母组成') : true;
                preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/', $repassword) == false ? $this -> ajaxReturnData(0,'确认密码格式不正确，只能6-20位数字和字母组成') : true;
                $password !== $repassword ? $this -> ajaxReturnData(0,'两次密码输入不一致') : true;
                I('post.tele','','string') ? $tele = I('post.tele','','string') : $this -> ajaxReturnData(0,'手机号不能为空');
                $data['password'] = encrypt_pwd($password);
                $res = D('User') -> where("`phone` = $tele") -> save($data);
                session('repass_code_' . $tele,null);
                $res !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'修改密码失败');

            }elseif($type == 'tele_register'){
                //新用户手机号注册，设置用户名和密码
                I('post.tele','','string') ? $tele = I('post.tele','','string') : $this -> ajaxReturnData(0,'手机号不能为空');
                I('post.code','','intval') ? $code = I('post.code','','intval') : $this -> ajaxReturnData(0,'手机验证码不能为空');
                I('post.invite_tele','','string') ? $invite_tele = I('post.invite_tele','','string') : $this -> ajaxReturnData(0,'邀请码不能为空');

                I('post.username','','string')   ? $username   = I('post.username','','string')   : $this -> ajaxReturnData(0,'用户名不能为空');
                I('post.password','','string')   ? $password   = I('post.password','','string')   : $this -> ajaxReturnData(0,'密码不能为空');
                I('post.repassword','','string') ? $repassword = I('post.repassword','','string') : $this -> ajaxReturnData(0,'重复密码不能为空');
                $password != $repassword ? $this -> ajaxReturnData(0,'两次密码输入不一致') : true;

                $uid = D('User') -> where("phone = '$tele'") -> getField('id');
                !empty($uid) ? true : $this -> ajaxReturnData(10001,'手机号未注册，请注册');
                $invite_uid = D('User') -> where("phone = '$invite_tele'") -> getField('id');
                empty($invite_uid) ? $this -> ajaxReturnData(0,'邀请人不存在') : true;

                $sendtime = session('register_code_sendtime') ? session('register_code_sendtime') : 0;
                if(time() - $sendtime > 300){
                    session('register_code_'.$tele,null);
                    $this -> ajaxReturnData(0,'验证码已失效，请重新获取');//验证码是否过期
                }elseif(session('register_code_'.$tele) != $code){
                    $this -> ajaxReturnData(0,'短信验证码不正确!');//验证验证码是否正确
                }else{
                    session('register_code_'.$tele,null);//验证通过,让验证失效
                    $data = array(
                        'username'    => $username,
                        'password'    => encrypt_pwd($password),  //定义初始密码
                    );
                    $res = D('User') -> where("phone = '$tele'") -> save($data);
                    $res !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'注册失败！');//验证通过

                }

            }elseif($type == 'email_register'){
                //新用户邮箱注册，设置用户名和密码

            }else{
                $this -> ajaxReturnData(0,'参数错误');//验证是否登录
            }

        }else{
            layout(false);
            $this -> display();
        }
    }

    //修改银行卡信息
    public function editCard(){
        layout(false);
        $this -> display();
    }

    //意见反馈
    public function feedback(){
        layout(false);
        $this -> display();
    }

    //关于我们
    public function aboutus(){
        layout(false);
        $this -> display();
    }

    //我的邀请码
    public function erweima(){
        layout(false);
        $this -> display();
    }

    //邀请收益
    public function commission(){
        layout(false);
        $this -> display();
    }


    //积分兑换
    public function integral(){
        $pwd = 'abc123456';
        echo md5(sha1($pwd)).'<hr>';
        echo '57af13bb2bd1edf007db246573dd6b53';die;
        $this -> display();
    }



    //ajax登录
    public function ajax_login(){
        !IS_POST && !IS_AJAX ?  $this -> ajaxReturnData(0,'请求方式错误') : true;
        I('post.username','','string') ? $username = I('post.username','','string') : $this -> ajaxReturnData(0,'用户名不能为空');
        I('post.password','','string') ? $password = I('post.password','','string') : $this -> ajaxReturnData(0,'密码不能为空');

        $model     = M('user');//实例化模型
        $where     = "username = '$username' or phone = '$username' or email = '$username'";//定义查询条件
        $userinfo  = $model -> where($where) -> find();//查询密码
        empty($userinfo) ? $this -> ajaxReturnData(0,'账号不存在') : true;
        if(password_verify($password,$userinfo['password']) === true){
            session('userinfo',$userinfo);//保存用户数据
            $this -> ajaxReturnSuccess();
        }else{
            $this -> ajaxReturnData(0,'密码错误');
        }
    }

    //ajax退出登录
    public function ajax_logout(){
        //删除指定的session
        session('userinfo',null);
        //删除所有的session
        session(null);
        $data = array(
            'code' => 10000,
            'msg'  => '退出成功',
        );
        //返回数据
        $this -> ajaxReturn($data);
    }

    //发送短信验证码
    public function sendCode(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误！') : true;
        I('post.phone','','string') ? $phone = I('post.phone','','string') : $this -> ajaxReturnData(0,'手机号不能为空！');
        I('post.type','','string')  ? $type = I('post.type','','string') : $this -> ajaxReturnData(0,'参数错误！');

        if($type == 'register_code'){
            //注册
            $sendtime = session('register_code_sendtime') ? session('register_code_sendtime') : 0;//发送频率限制,半小时
            time() - $sendtime < 300 ? $this -> ajaxReturnData(0,'发送太频繁，请稍后再试') : true;

            $uid = M('User') -> where("phone = '$phone'") -> getField('id');
            !empty($uid) ? $this -> ajaxReturnData(10001,'手机号已经注册，是否登录？') : true;//如果存在记录，说明已经注册
        }elseif($type == 'repass_code'){
            $sendtime = session('repass_code_sendtime') ? session('repass_code_sendtime') : 0;//发送频率限制,半小时
            time() - $sendtime < 300 ? $this -> ajaxReturnData(0,'发送太频繁，请稍后再试') : true;
        }else{
            $this -> ajaxReturnData(0,'参数错误');
        }

        $code = rand(1000,9999);//验证码
        $tpl_id = '63946';
        $key = '2580aa6c016f2c630f5835d0aa40edb2';//APP key
        $url = "http://v.juhe.cn/sms/send?mobile=".$phone."&tpl_id=".$tpl_id."&tpl_value=%23code%23%3d".$code."&dtype=&key=".$key;//请求地址
        $res = curl_request($url, false, array(), true);//发送请求调用接口，使用curl发送请求
        $result = json_decode($res, true);//转化结果为数组格式

        if($result['error_code'] !== 0){
            $this -> ajaxReturnData(0,'服务器异常');//如果请求不成功
        }else{
            if($type == 'register_code'){
                session('register_code_' . $phone, $code);//将验证码保存到session
                session('register_code_sendtime',time());
            }elseif($type == 'repass_code'){
                session('repass_code_' . $phone, $code);//将验证码保存到session
                session('repass_code_sendtime',time());
            }
            $this -> ajaxReturnData();
        }

    }

    //发送邮箱验证码
    public function sendEmailCode(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误！') : true;
        I('post.email','','string') ? $email = I('post.email','','string') : $this -> ajaxReturnData(0,'邮箱不能为空！');
        I('post.type','','string')  ? $type = I('post.type','','string') : $this -> ajaxReturnData(0,'参数错误！');

        if($type == 'register_code'){
            //注册
            $sendtime = session('register_email_code_sendtime') ? session('register_email_code_sendtime') : 0;//发送频率限制,半小时
            time() - $sendtime < 300 ? $this -> ajaxReturnData(0,'发送太频繁，请稍后再试') : true;

            $uid = M('User') -> where("email = '$email'") -> getField('id');
            !empty($uid) ? $this -> ajaxReturnData(10001,'邮箱已经注册，是否登录？') : true;//如果存在记录，说明已经注册
        }elseif($type == 'repass_code'){
            $sendtime = session('register_email_code_sendtime') ? session('register_email_code_sendtime') : 0;//发送频率限制,半小时
            time() - $sendtime < 300 ? $this -> ajaxReturnData(0,'发送太频繁，请稍后再试') : true;
        }else{
            $this -> ajaxReturnData(0,'参数错误');
        }

        $code = rand(1000,9999);//验证码
        $tpl_id = '63946';
        $key = '2580aa6c016f2c630f5835d0aa40edb2';//APP key
        $url = "http://v.juhe.cn/sms/send?mobile=".$phone."&tpl_id=".$tpl_id."&tpl_value=%23code%23%3d".$code."&dtype=&key=".$key;//请求地址
        $res = curl_request($url, false, array(), true);//发送请求调用接口，使用curl发送请求
        $result = json_decode($res, true);//转化结果为数组格式

        if($result['error_code'] !== 0){
            $this -> ajaxReturnData(0,'服务器异常');//如果请求不成功
        }else{
            if($type == 'register_code'){
                session('register_email_code' . $phone, $code);//将验证码保存到session
                session('register_email_code_sendtime',time());
            }elseif($type == 'repass_code'){
                session('register_email_code' . $phone, $code);//将验证码保存到session
                session('register_email_code_sendtime',time());
            }
            $this -> ajaxReturnData();
        }
    }

    //验证重置密码的手机验证码是否正确
    public function check_repass_code(){
        I('post.tele','','string') ? $tele = I('post.tele','','string') : $this -> ajaxReturnData(0,'手机号不能为空');
        I('post.code','','string') ? $code = I('post.code','','string') : $this -> ajaxReturnData(0,'手机验证码不能为空');
        I('post.type','','string') ? $type = I('post.type','','string') : true;

        $user = D('User') -> where("`phone` = $tele") -> getField('id');
        empty($user) ? $this -> ajaxReturnData(10001,'用户不存在,是否去注册？') : true;

        $sendCode = session('repass_code_' . $tele);
        $sendTime =  session('repass_code_sendtime');
        if($type === 're_pass'){
            //如果是修改密码验证
            if($code == $sendCode){
                if(time() - $sendTime > 300){
                    return false;
                }else{
                    return true;
                }
            }else{
                return false;
            }

        }else{
            //如果只是POST请求
            if($code == $sendCode){
                time() - $sendTime > 300 ? $this -> ajaxReturnData(0,'验证码已过期，请重新获取！') : $this -> ajaxReturnData();
            }else{
                $this -> ajaxReturnData(0,'手机号与验证码不匹配！');
            }
        }


    }

    //ajax注册
    public function ajax_register(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误！') : true;
        $tele        = I('post.tele','','string')       ? I('post.tele','','string') : true;
        $code        = I('post.code','','intval')       ? I('post.code','','intval') : true;
        $invite_tele = I('post.invite_tele','','string') ? I('post.invite_tele','','string') : true;

        $email         = I('post.email','','string')             ? I('post.email','','string') : true;
        $email_code    = I('post.email_code','','intval')        ? I('post.email_code','','intval') : true;
        $e_invite_tele = I('post.email_invite_tele','','string') ? I('post.email_invite_tele','','string') : true;

        //如果是手机号码注册
        if(isset($tele)){
            $uid = D('User') -> where("phone = '$tele'") -> getField('id');
            empty($uid) ? true : $this -> ajaxReturnData(10001,'手机号已注册，请登录');
            $invite_uid = D('User') -> where("phone = '$invite_tele'") -> getField('id');
            empty($invite_uid) ? $this -> ajaxReturnData(0,'邀请人不存在') : true;

            //验证验证码和手机号
            $sendtime = session('register_code_sendtime') ? session('register_code_sendtime') : 0;
            if(time() - $sendtime > 300){
                session('register_code_'.$tele,null);
                $this -> ajaxReturnData(0,'验证码已失效，请重新获取');//验证码是否过期
            }elseif(session('register_code_'.$tele) != $code){
                $this -> ajaxReturnData(0,'短信验证码不正确!');//验证验证码是否正确
            }else{
                $data = array(
                    'phone'       => $tele,
                    'password'    => encrypt_pwd('123456abc'),  //定义初始密码
                    'create_time' => time(),
                    'invite_uid'  => $invite_uid,
                );
                $res = D('User') -> add($data);
                $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'注册失败！');//验证通过

            }
        }elseif(isset($email)){
            //如果是邮箱注册
            //生成一个验证码,保存到email_code字段
            $email_code = rand(100000,999999);
            $data['email_code'] = $email_code;//邮箱注册码
            $model = D('User');
            //创建数据对象
            if(!$model->create($data)){
                //如果添加失败，返回错误信息
                $code = 10001;
                $msg  = $model->getError();
            }else{
                //添加到数据库
                $res = $model->add();
                if($res){
                    //添加成功
                    $email = $data['email'];//收件邮箱
                    $subject = '【零玖一】商城激活';
                    $url ="http://www.shop.com/index.php/Home/User/jihuo/id/{$res}/code/{$email_code}";
                    $body = "点击一下连接进行激活:<br /><a href='{$url}'>{$url}</a>;<br/>如果点击无法跳转,请复制以上链接直接在浏览器打开.";
                    //调用sendmail函数发送邮件
                    $res = sendmail($email,$subject,$body);
                    if($res !== true){
                        //发送失败
                        //sendmail($email,$subject,$body);
                        $code = 10001;
                        $msg  = '邮件发送失败,请重试';
                    }else{
                        $code = 10000;
                        $msg = '邮件发送成功，请前往激活';//后期修改，根据用户邮箱类型提示用户跳转到邮箱登录页面
                    }
                }else{
                    $code = 10001;
                    $msg  = '注册失败';
                }
            }
        }else{
            $this -> ajaxReturnData(0,'参数错误');
        }


    }

    //ajax获取用户头像
    public function ajax_my(){
        $uid = $_SESSION['userinfo']['id'];//获取用户id
        if(empty($uid)){//如果用户信息为空
            $code = 10001;
            $msg  = '请先登录';//去登录
        }else{

            $where = ['id' => $uid];
            $user  = M('User') ->field('header_img,username') -> where($where) -> find();
            //获取用户头像和用户名
            if($user){
                $code = 10000;
                $msg  = 'success';
            }else{
                $code = 10001;
                $msg  = '没有用户头像和用户名信息';
            }
        }

        //设置返回数据格式
        $data = array(
            'code' => $code,
            'msg'  => $msg,
            'info' => compact('user')
        );
        //返回数据
        $this->ajaxReturn($data);
    }

    //ajax获取用户基本信息
    public function ajaxprofile(){

        if(IS_POST){
            //ajax修改用户基本信息

        }else{
            //ajax获取用户基本信息
            $uid = $_SESSION['userinfo']['id'];//获取用户id
            if(empty($uid)){//如果用户信息为空
                $code = 10001;
                $msg  = '请先登录';//去登录
            }else{

                $field = 'id,username,phone,email,weixin,header_img,gender,birthday';//定义查询字段
                $user  = M('User') -> field($field) -> where(['id' => $uid]) -> find();
                if($user){
                    $code = 10000;
                    $msg  = 'success';
                }else{
                    $code = 10001;
                    $msg  = '没有用户信息';
                }
            }

            //设置返回数据格式
            $data = array(
                'code' => $code,
                'msg'  => $msg,
                'info' => compact('user')
            );
            //返回数据
            $this->ajaxReturn($data);
        }

        //设置返回数据格式
        $data = array(
            'code' => $code,
            'msg'  => $msg,
            'info' => compact('user')
        );
        //返回数据
        $this->ajaxReturn($data);
    }

    //ajax获取我的钱包显示
    public function ajax_wallet(){
        //ajax获取用户基本信息
        $uid = $_SESSION['userinfo']['id'];//获取用户id
        if(empty($uid)){//如果用户信息为空
            $code = 10001;
            $msg  = '请先登录';//去登录
        }else{
            $field = 'price,add_time,remark';
            $price = M('User') -> where(['id' => $uid]) -> getField('balance');
            $log   = M('User_price_log') -> field($field) -> where(['uid' => $uid]) -> order('add_time desc') -> select();
            if($log){
                $code = 10000;
                $msg  = 'success';
            }else{
                $code = 10001;
                $msg  = '没有资金明细';
            }
        }
        //设置返回数据格式
        $data = array(
            'code' => $code,
            'msg'  => $msg,
            'info' => compact('price','log')
        );
        //返回数据
        $this->ajaxReturn($data);
    }


    //ajax我的钱包充值
    public function ajaxrecharge(){
        //ajax获取用户基本信息
        $uid = $_SESSION['userinfo']['id'];//获取用户id
        empty($uid) ? $this -> ajaxReturnData(10002,'请先登录！') : $uid;//如果用户信息为空
        IS_POST ? IS_POST : $this -> ajaxReturnData(0,'请求方式错误');
        (empty($_POST['pricetype']) && empty($_POST['price'])) ? $this -> ajaxReturnData(0,'参数错误') : 1;
        (empty($_POST['pricetype']) && $_POST['price']) ? $price = $_POST['price'] :  $price = $_POST['pricetype'];
        $add = [
            'type'   => 1,//1充值，2提现
            'uid'    => $uid,
            'prices' => $price,
            'realprice' => 0,
        ];

    }

    //ajax加载地址
    public function ajax_address(){
        //ajax获取用户基本信息
        $uid = $_SESSION['userinfo']['id'];//获取用户id
        empty($uid) ? $this -> ajaxReturnData(10002,'请先登录') : $uid;//如果用户信息为空

        $addresslist = M('Address') -> where(['user_id' => $uid]) -> select();
        foreach($addresslist as $key => $value){
            if(substr($addresslist[$key]['address'],6,3) === '市'){
                $addresslist[$key]['province'] = '';
                $addresslist[$key]['city'] = substr($addresslist[$key]['address'],0,9);
                $addresslist[$key]['area'] = substr($addresslist[$key]['address'],9,9);
                $addresslist[$key]['address'] = substr($addresslist[$key]['address'],18);
            }else{
                $addresslist[$key]['province'] = substr($addresslist[$key]['address'],0,9);
                $addresslist[$key]['city'] = substr($addresslist[$key]['address'],9,9);
                $addresslist[$key]['area'] = substr($addresslist[$key]['address'],18,9);
                $addresslist[$key]['address'] = substr($addresslist[$key]['address'],27);
            }
        }
        $addresslist ? $this -> ajaxReturnSuccess(compact('addresslist')) : $this -> ajaxReturnError();

    }

    //ajax删除地址
    public function ajax_del_address(){
        !IS_POST || !IS_AJAX ? $this -> ajaxReturnData(0,'访问错误') : true;
        $oid = I('post.id','','intval');
        $uid = $_SESSION['userinfo']['id'];//获取用户id
        empty($uid) ? $this -> ajaxReturnData(10002,'请先登录') : $uid;//如果用户信息为空
        $addr = D('Address') -> where(['user_id' => $uid, 'id' => $oid]) -> find();
        empty($addr) ? $this -> ajaxReturnData(0,'订单号与用户不符') : true;
        $res = D('Address') -> delete($oid);
        $res !== false ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'删除失败');
    }

    //ajax订单
    public function ajax_order(){
        //ajax获取用户基本信息
        $uid = $_SESSION['userinfo']['id'];//获取用户id
        empty($uid) ? $this -> ajaxReturnData(10002,'请先登录') : $uid;//如果用户信息为空
        $where['a.user_id'] = $uid;
        //确认订单类型
        $where['order_type'] = I('get.type','','intval') ? I('get.type','','intval') : 0;//普通订单类型为0
        $order_status = $_GET['status'] ? $_GET['status'] : 0;
        //订单编号，商品图片，商品名称，商品单价，购买数量，(邮费，暂时没有)，实付
        $fields = 'a.id,order_id,b.goods_id,order_sn,goods_small_img,goods_name,b.goods_price,number,order_amount,order_status';
        if($where['order_type'] === 0){
            //普通订单
            //'0待付款，1,已付款未发货，2已付款已发货，3，已付款已收货，4已付款已评价，
            //5取消订单，6申请退货，7退货中（商家处理退货申请），8退货成功（商家同意退货），9退款成功(余额已返回账户)'

            if($order_status == 0){
                $where['a.order_status'] = 0;
            }elseif($order_status == 1){
                //1待处理(提货，退款，)
                $where['a.order_status'] = 1;
            }elseif($order_status == 2){
                //2发货中（确认收货，查看物流）
                $where['a.order_status'] = 2;
            }elseif($order_status == 3){
                //3-10已完成
                $where['a.order_status'] = ['in','3,4,5,6,7,8,9'];
            }
        }elseif($where['order_type'] === 1){
            //充值订单

        }elseif($where['order_type'] === 2){
            //活动订单
            //活动订单：1持有，2已提货，9解约退款
            if($order_status == 0){
                $where['a.user_id'] = $uid;
            }elseif($order_status == 1){
                $where['a.order_status'] = 1;
            }elseif($order_status == 2){
                $where['a.order_status'] = 2;
            }elseif($order_status == 3){
                $where['a.order_status'] = 9;
            }
            $fields .= ',order_desc';
        }elseif($where['order_type'] === 3){
            //积分订单
        }


        $orderlist = M('Order')
            -> alias('a')
            -> field($fields)
            -> where($where)
            -> join('zhouyuting_order_goods b on a.id = b.order_id')
            -> join('zhouyuting_goods c on b.goods_id = c.goods_id')
            -> order('update_time desc')
            -> select();
        foreach($orderlist as $key => $value){
            $neworder[$value['order_id']][] = $value;
        }
        $num = 0;
        foreach($neworder as $key => $value){
            $neworderlist[$num] = $value;
            $num ++;
        }

        $orderlist ? $this -> ajaxReturnSuccess(compact('neworderlist')) : $this -> ajaxReturnData(10001,'没有订单信息');


    }

    public function ajax_acttotal(){
        $where['user_id'] = $_SESSION['user_info']['id'];
        $where['order_status'] = 1;
        $acrorder = D('Order') -> field('id,order_amount') -> where($where) -> select();//获取所有持有状态的订单
    }


    //ajax提现
    public function ajax_cash(){
        //ajax获取用户基本信息
        $uid = $_SESSION['userinfo']['id'];//获取用户id
        if(empty($uid)){//如果用户信息为空
            $code = 10001;
            $msg  = '请先登录';//去登录
        }else{

        }
    }

    //ajax我的邀请码，二维码
    public function ajax_erweima(){
        //ajax获取用户基本信息
        $phone = $_SESSION['userinfo']['phone'];//获取用户id
        empty($phone) ? $this -> ajaxReturnData(10002,'请先登录') : $phone;//如果用户信息为空

        $userimg = M('user') ->where(['phone' => $phone]) -> getField('header_img');//用户头像
        $username = $_SESSION['userinfo.username'];//用户名称
        $path = 'Public/Uploads/erweima/';//定义保存图片地址
        if(!file_exists($path))
        {
            mkdir($path, 0777, true);//如果没有则创建目录
        }
        $filename = $path.$phone.$username['username'].'.png';//定义文件名称
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/User/register/invite_tele/'.$phone;//推广链接
        $userimg ? makecode($url, $filename ,substr($userimg,1)) : qrcode($url, $filename);
        //如果存在用户头像，则生成带有用户头像logo的二维码,如果不存在用户头像，直接生成二维码
        $img = 'http://'.$_SERVER['SERVER_NAME'].'/'.$filename;
        $this -> ajaxReturnSuccess(compact('img','url'));
    }

    //ajax邀请收益
    public function ajax_commission(){

    }

    public function ajax_pay(){
        $id = I('get.id','','intval');
        $id ? $id : $this -> ajaxReturnData(0,'参数错误！');
        $url = '/Pay/index/id/'.$id;
        $this -> ajaxReturnSuccess(compact('url'));
    }

    //忘记密码
    public function forget_pass(){
        layout(false);
        $this -> display();
    }






}