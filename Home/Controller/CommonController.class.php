<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller{
    protected function ajaxReturnData($code = 10000, $msg = 'success',$info = [])
    {
        //设置返回数据格式
        $data = array(
            'code' => $code,
            'msg'  => $msg,
            'info' => $info,
        );
        //返回数据
        $this->ajaxReturn($data);
    }

    public function _initialize(){
        session_start();

    }

    public function check_login(){
        if(!session('?userinfo')){
            $this -> ajaxReturnData(10002,'请先登录！');
        }
    }

}