<?php
namespace Admin\Controller;

class MenuController extends BaseController {
    public function index() {
        if(IS_AJAX){
            $menu = D('Menu');
            $data = array(
                'data'=>$menu->getmenu()
            );
            $this->ajaxReturn($data);
        }else{
            $this->display('Menu/index');
        }
    }

    public function edit(){

    }
}