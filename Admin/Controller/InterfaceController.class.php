<?php
namespace Admin\Controller;

class InterfaceController extends BaseController {
    public function index(){
        if(IS_AJAX){
            $list = D('Interface') -> select();
            $data = array(
                'data'=>$list
            );
            $this->ajaxReturn($data);
        }else{
            $this -> display('Interface/index');
        }

    }
}

