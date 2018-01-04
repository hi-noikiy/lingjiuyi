<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller {

    public function __construct(){
        //先调用父类的构造函数
        parent::__construct();
        if(!session('?username')) {
            $this->redirect('Admin/Public/login');
        }
        //检测是否拥有当前页面的访问权限
        $this->checkauth();
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

    //权限检测
    public function checkauth(){
        //获取当前登录的管理员拥有的权限
        $user_id = session('userid');
        $role_id = D('Admin') -> where(['userid' => $user_id]) -> getField('roleid');

//        if($role_id == 1){
//            //超级管理员
//            return;
//        } 先不使用，等开发完成再使用
        $menuids = M('Privileges') -> where(['roleid' => $role_id]) -> getField('menuid',true);
        //获取当前访问的页面是哪个权限
        //偶去当前访问的控制器名称和方法 拼接"控制器-方法"
        $c = CONTROLLER_NAME;
        $a = ACTION_NAME;
        $ac = $c.'-'.$a;

        if($ac == 'Index-index'){
            return;
        }
        if($ac == 'Index-main'){
            return;
        }
        if($ac == 'Index-logout'){
            return;
        }

        $menuid = D('Menu') -> where("c = '$c' AND a = '$a'") -> getField('menuid');
//dump($menuid);dump($menuids);dump(!in_array($menuid,$menuids));die;
        //判断当前访问的页面对应的权限,是否在其拥有的权限中 $ac 是否在$role['role_author_ac']
        if(!in_array($menuid,$menuids)){
            //没有权限访问该页面
            if(IS_AJAX){
                $this -> ajaxReturnData(0,'没有权限进行操作！');
            }else{
                $this->error("没有权限访问该页面",U('Admin/Index/main'));
            }
        }
    }
}
