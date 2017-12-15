<?php
    //1、定义命名空间
    namespace H5\Controller;
    //2、引入核心控制器
    use Think\Controller;

    //3、定义News控制器
    class NewsController extends CommonController {
       //定义index方法
       public function index() {
         //接收参数
         $skip = I('get.skip');
         $limit = I('get.limit');
         //实例化模型
         $news = M('News');
         //调用后台数据
         $data = array();
         $data['code'] = 0; //0代表success
         $data['msg'] = 'success';
         $data['info'] = $news->limit($skip,$limit)->select();
         //返回数据
         $this->ajaxReturn($data);
       }  
    }