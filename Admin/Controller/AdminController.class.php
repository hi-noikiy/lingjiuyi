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

}
