<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller {
    protected function _initialize() {
        if(!session('?username')) {
            $this->redirect('Admin/Public/login');
        }
    }

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

    protected function ajaxReturnSuccess($info = [] , $code = 10000 , $msg = 'success')
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
}
