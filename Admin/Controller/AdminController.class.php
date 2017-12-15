<?php
namespace Admin\Controller;

class AdminController extends BaseController {
    //展示权限管理中的管理列表
    public function index() {
        $this->display('Admin/index');
    }

    //管理列表中ajax异步获取管理员数据
    public function lst() {
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
    }
}
