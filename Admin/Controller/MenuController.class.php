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

    public function add(){
        $menu = D("Menu");
        $data['parentid'] = I('post.parentid');
        $data['is_show']  = I('post.is_show');
        I('post.model')      ? $data['m']    = I('post.model')      : false;
        I('post.controller') ? $data['c']    = I('post.controller') : false;
        I('post.action')     ? $data['a']    = I('post.action')     : false;
        I('post.name')       ? $data['name'] = I('post.name')       : false;
        I('post.icon')       ? $data['icon'] = I('post.icon')       : false;
        I('post.description')? $data['description'] = I('post.description') : false;
        if (!$menu -> create()){
            $msg = $menu -> getError();
            $this -> ajaxReturnData(0,$msg);
        }else{

            $result = $menu -> add();
            $result ? true : $this -> ajaxReturnData(0,'添加失败！');
            $data['menuid'] = $result;
            if($data['parentid'] == 0){
                $data['path'] = '-'.$result.'-';
                $data['level'] = 1;
            }else{
                $parentid  = $menu -> where(['menuid' => $data['parentid']]) -> getField('parentid');
                if($parentid === '0'){
                    $data['path']   = '-'.$data['parentid'].'-'.$result.'-';
                    $data['level']   = 2;
                }else{
                    $data['path']   = '-'.$parentid.'-'.$data['parentid'].'-'.$result.'-';
                    $data['level']   = 3;
                }
            }
            $res = M('Menu') -> save($data);
            $res !== false ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'保存失败！');
        }
    }

    public function edit(){

    }

    public function ajax_get_menu(){
        $menus = D('Menu') -> getmenu();
        $menus ? $this -> ajaxReturnSuccess($menus) : $this -> ajaxReturnData(0,'无数据');
    }
}