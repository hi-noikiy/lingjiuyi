<?php
namespace App\Controller;
use Think\Controller;

class CommonController extends Controller {

	protected function ajaxReturnData($code = 10000, $msg = 'success',$info = [])
    {
    	$info = edit_img($info);
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
    	$info = edit_img($info);
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