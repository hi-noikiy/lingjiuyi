<?php
    namespace Admin\Controller;

    class PrivilegesController extends BaseController {
        public function index($roleid) {
            $this->assign('roleid',$roleid);
            $this->display('Privileges/index');
        }

        public function editok() {
            $privileges = D('Privileges');
            $roleid = I('post.roleid');
            $menuid = I('post.menuid');
            $sql = "insert into zhouyuting_privileges values ";
            if($menuid) {
                foreach($menuid as $v) {
                    $sql .= "(null,$roleid,$v),";
                }
            }
            $sql = rtrim($sql,',');
            if($privileges->execute($sql)) {
                $this->success('权限设置成功',U('Admin/Role/index'));
            }
        }
    }
?>