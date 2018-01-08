<?php
namespace Tv\Controller;
use Think\Controller;

class BaseController extends Controller{
    public function __construct()
    {
        parent::__construct();
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