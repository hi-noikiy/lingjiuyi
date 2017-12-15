<?php
namespace H5\Controller;
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

    protected function ajaxReturnError($code = 0 , $msg = 'error'){
        //设置返回数据格式
        $data = array(
            'code' => $code,
            'msg'  => $msg,
        );
        //返回数据
        $this->ajaxReturn($data);
    }
}