<?php
namespace Tv\Controller;
use Think\Controller;

class IndexController extends Controller{

    public function index(){
        if(IS_AJAX && IS_GET){

        }else if(IS_GET){
            $this -> display();
        }
    }
}