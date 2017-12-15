<?php
namespace Admin\Controller;

class MenuController extends BaseController {
    public function index() {
        $this->display('Menu/index');
    }

    public function lst() {
        $menu = D('Menu');
        $data = array(
            'data'=>$menu->getmenu()
        );
        $this->ajaxReturn($data);
    }
}