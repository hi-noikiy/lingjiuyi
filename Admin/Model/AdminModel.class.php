<?php
    namespace Admin\Model;
    use Think\Model\RelationModel;

    class AdminModel extends RelationModel {
        protected $pk = 'userid';

        protected $fields = array('userid','username','password','roleid','salt','lastloginip','lastlogintime');

        protected $_link = array(
            'Role' => array(
                'mapping_type'=>self::BELONGS_TO,
                'class_name'=>'Role',
                'foreign_key'=>'roleid',
                'mapping_name'=>'role'
            )
        );

        public function login($username,$password) {
            $row = $this->where("username='$username'")->find();
            if($row) {
                if(md5(md5($password).$row['salt']) == $row['password']) {
                    session('username',$username);
                    session('userid',$row['userid']);
                    session('header_img',$row['header_img']);
                    return true;
                }
            }
            return false;
        }

        public function islogin() {
            return (session('?userid') && session('userid') > 0);
        }
    }