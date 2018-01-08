<?php
namespace Admin\Controller;

class AdminController extends BaseController {
    //展示权限管理中的管理列表
    public function index() {
        if(IS_AJAX){
            $adminmodel = D('Admin');
            $list = array();
            foreach($adminmodel->relation(true)->select() as $row) {
                $row['lastlogintime'] = date('Y-m-d H:i:s',$row['lastlogintime']);
                $list[] = $row;
            }
            $data = array(
                'data'=>$list
            );
            $this->ajaxReturn($data);
        }else{
            $this->display('Admin/index');
        }
    }

    public function add(){
        $admin = D('Admin');
        if (!$admin -> create()){
            $msg = $admin -> getError();
            $this -> ajaxReturnData(0,$msg);
        }else{
            $data = I('post.');
            $data['password'] = encrypt_pwd($data['password']);
            $res = $admin -> add($data);
            empty($res) ? $this -> ajaxReturnData(0,'添加失败') : $this -> ajaxReturnSuccess();
        }
    }

    public function del(){
        I('post.id','','intval') ? $id = I('post.id','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $res = D('Admin') -> delete($id);
        $res ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'删除失败');
    }

}
