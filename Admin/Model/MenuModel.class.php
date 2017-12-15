<?php
    namespace Admin\Model;
    use Think\Model;

    class MenuModel extends Model {
        protected $pk = 'menuid';
        protected $fields = array('menuid','name','parentid','path','level','description','listorder','addtime');

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