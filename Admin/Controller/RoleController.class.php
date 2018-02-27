<?php
    namespace Admin\Controller;

    class RoleController extends BaseController {
        public function index() {
            if(IS_AJAX){
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
            }else{
                $this->display('Role/index');
            }
        }

        public function add(){
            !IS_AJAX && !IS_POST ? $this -> ajaxReturnData(0,'访问方式错误！') : true;
            I('post.name','','string') ? $data['rolename']    = I('post.name','','string') : $this -> ajaxReturnData(0,'参数错误！');
            I('post.desc','','string') ? $data['description'] = I('post.desc','','string') : $this -> ajaxReturnData(0,'参数错误！');
            $roleid = D('Role') -> add($data);
            empty($roleid) ? $this -> ajaxReturnData(0,'添加管理员失败！') : true;
            $menuids = explode(',',I('post.menuids','','string'));
            $dataAll = [];
            foreach($menuids as $key => $value){
                $dataAll[$key]['roleid']  = $roleid;
                $dataAll[$key]['menuid'] = $value;
            }
            $res    = D('Privileges') -> addAll($dataAll);
            $res ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'分配管理员权限失败');
        }

        public function edit(){
            $type = I('post.type','','string');
            empty($type) ? $this -> ajaxReturnData(0,'参数错误') : true;
            if($type === 'checked_pri'){
                //添加权限
                $data = array(
                    'roleid' => I('post.roleid','','intval'),
                    'menuid' => I('post.menuid','','intval'),
                );
                empty($data['roleid']) ? $this -> ajaxReturnData(0,'参数错误') : true;
                empty($data['menuid']) ? $this -> ajaxReturnData(0,'参数错误') : true;
                $res = D('Privileges') -> add($data);
                $res ? $this -> ajaxReturnSuccess($res) : $this -> ajaxReturnData(0,'添加失败');
            }else if($type === 'cancel_pri'){
                //删除权限
                $id = I('post.pri_id','','intval');
                empty($id) ? $this -> ajaxReturnData(0,'参数错误') : true;
                $res = D('Privileges') -> delete($id);
                $res ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'删除失败');
            }else if($type === 'save_name'){
                $data = array(
                    'roleid'   => I('post.id','','intval'),
                    'rolename' => I('post.name','','string'),
                );
                empty($data['roleid'])   ? $this -> ajaxReturnData(0,'参数错误') : true;
                empty($data['rolename']) ? $this -> ajaxReturnData(0,'参数错误') : true;
                $res = D('Role') -> save($data);
                $res !== false ? $this -> ajaxReturnSuccess($res) : $this -> ajaxReturnData(0,'保存失败');
            }else{
                $this -> ajaxReturnData(0,'参数错误');
            }
        }

        public function del(){
            I('post.id','','intval') ? $id = I('post.id','','intval') : $this -> ajaxReturnData(0,'参数错误');
            $res = D('Role') -> delete($id);
            $res ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'删除失败');
        }

        //获取菜单名称
        public function ajax_get_menu(){
            !IS_AJAX && !IS_GET ? $this -> ajaxReturnData(0,'访问方式错误！') : true;
            $type = I('get.type','','string') ? I('get.type','','string') : false;
            if($type === 'add'){
                //只获取所有菜单
                $menu_all = D('Menu') -> field('menuid,name') -> where('parentid != 0') -> select();//获取所有操作菜单
                empty($menu_all) ? $this -> ajaxReturnData(0,'无数据') : $this -> ajaxReturnSuccess($menu_all);
            }else{
                //获取所有菜单 和管理员拥有权限
                $id = I('get.id','','intval');
                empty($id) ? $this -> ajaxReturnData(0,'参数错误') : true;
                $menu_all = D('Menu') -> field('menuid,name') -> where('parentid != 0') -> select();//获取所有操作菜单
                $menus = D('Privileges') -> where("roleid = $id") -> select();//获取当前管理员拥有的菜单
                empty($menu_all) && empty($menus) ? $this -> ajaxReturnData(0,'无数据') : $this -> ajaxReturnSuccess(compact('menu_all','menus'));
            }
        }

        //获取角色名称
        public function ajax_get_role(){
            !IS_AJAX && !IS_GET ? $this -> ajaxReturnData(0,'访问方式错误！') : true;
            $roles = D('Role') -> field('roleid,rolename') -> select();
            empty($roles) ? $this -> ajaxReturnData(0,'无数据') : $this -> ajaxReturnSuccess($roles);
        }


    }
?>