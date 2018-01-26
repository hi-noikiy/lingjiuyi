<?php
namespace Blog\Controller;
use Think\Controller;
class IndexController extends CommonController {
    public function index(){
        $this -> display();
    }
}