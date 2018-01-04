<?php
    namespace Admin\Model;
    use Think\Model;

    class MenuModel extends Model {
        protected $pk = 'menuid';
        protected $fields = array('menuid','name','parentid','path','level','description','listorder','addtime');

        protected $_validate = array(
            array('name','require','菜单名称必须！'),
            array('model','require','模块必须！'),
            array('controller','require','控制器必须！'),
            array('model,controller,action','check_replace','方法名称已经存在！',1,'callback',3),
        );

        protected $_auto = array (
            array('addtime','time',1,'function'),
        );

        protected function check_replace($field){
            $where = "m = '".$field['model']."' AND c = '".$field['controller']."' AND a = '".$field['action']."'";
            $data = $this -> where($where) -> select();
            if(empty($data)){
                return true;
            }else{
                return false;
            }
        }

        public function recur($data,$parentid=0,$level=0) {
            static $list = array();
            foreach($data as $row) {
                if($row['parentid'] == $parentid) {
                    $row['level'] = $level;
                    $list[] = $row;
                    $this->recur($data,$row['menuid'],$level+1);
                }
            }
            $data = array();
            foreach($list as $row) {
                $row['addtime'] = date('Y-m-d H:i:s',$row['addtime']);
                $row['name'] = str_repeat('&nbsp;',$row['level']*10).$row['name'];
                $data[] = $row;
            }
            return $data;
        }

        public function getmenu() {
            $data = $this->order("listorder asc")->select();
            return $this->recur($data);
        }

    }
?>