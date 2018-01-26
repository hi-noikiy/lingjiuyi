<?php
namespace Home\Controller;
use Think\Controller;
include_once( ROOT_PATH.'/Application/Tools/weibo/saetv2.ex.class.php' );

class ApiController extends Controller{
    //微博登录
    public function weibo_login(){

        $o = new \SaeTOAuthV2( C('WEIBO_LOGIN_CONFIG.WB_AKEY') , C('WEIBO_LOGIN_CONFIG.WB_SKEY') );
        $code_url = $o->getAuthorizeURL( C('WEIBO_LOGIN_CONFIG.WB_CALLBACK_URL') );
        $this -> ajaxReturn($code_url);
    }

    //微博登录回调函数
    public function weibocallback(){
        $keys = array();
        $keys['code'] = $_REQUEST['code'];
        $keys['redirect_uri'] = C('WEIBO_LOGIN_CONFIG.WB_CALLBACK_URL');
        $o = new \SaeTOAuthV2( C('WEIBO_LOGIN_CONFIG.WB_AKEY') , C('WEIBO_LOGIN_CONFIG.WB_SKEY') );
        $token = $o->getAccessToken( 'code', $keys ) ;
        $c = new \SaeTClientV2( C('WEIBO_LOGIN_CONFIG.WB_AKEY') , C('WEIBO_LOGIN_CONFIG.WB_SKEY') ,$token["access_token"]);
        $user_message = $c->show_user_by_id( $token['uid']);//根据ID获取用户等基本信息

        //查询是否存在用户id
        $userinfo = D('User') -> where(['open_id' => $user_message['id']]) -> find();
        if($userinfo){
            //修改用户信息
            $user = array(
                'last_login_time' => time(),
            );
            D('User') -> where(['id' => $userinfo['id']]) -> save($user);
            session('userinfo',$userinfo);
            $msg = '登录成功';
        }else{
            //添加用户信息
            $gender = $user_message['gender'] == 'f' ? '女' : ($user_message['gender'] == 'm' ? '男' : '保密');
            $user = array(
                'username'        => 'user'.time(),       //用户名称
                'password'        => encrypt_pwd('123456abc'),              //初始密码
                'last_login_time' => time(),
                'create_time'     => time(),
                'weibo'           => $user_message['screen_name'],       //用户名称
                'header_img'      => $user_message['profile_image_url'], //用户头像
                'gender'          => $gender,
                'open_id'         => $user_message['id'],                //用户id
            );
            $res = D('User') -> add($user);
            $userinfo = D('User') -> find($res);
            session('userinfo',$userinfo);
            $msg = $res ? '登录成功！初始密码为123456abc' : '创建账号失败！';
        }
        if(!session('?userinfo')){
            $msg = '登录失败';
        }else{
            $msg = $msg;
        }
        $this -> assign('msg',$msg);
        $this -> display('index');

    }
}