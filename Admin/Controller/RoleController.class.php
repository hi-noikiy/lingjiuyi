<?php
    namespace Admin\Controller;

    class RoleController extends BaseController {
        public function index() {
            $this->display('Role/index');
        }

        public function lst() {
            $role = D('Role');
            $list = $role
                ->field('a.*,group_concat(c.name) as privileges')
                ->join('a left join zhouyuting_privileges b on a.roleid=b.roleid')
                ->join('left join zhouyuting_menu c on b.menuid=c.menuid')
                ->group('a.roleid')
                ->select();
            $data = array(
                'data'=>$list
            );
            $this->ajaxReturn($data);
        }

        public function add() {
            $article = D('Article');
            $this->display('Article/add');
        }
    }
?>