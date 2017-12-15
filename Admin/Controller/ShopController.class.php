<?php
namespace Admin\Controller;
class ShopController extends BaseController
{
    public function index()
    {
        $this->display();
    }

    public function lst(){
        $shop = D('Shop') -> select();
        $data = ['data' => $shop];
        $this -> ajaxReturn($data);
    }

}