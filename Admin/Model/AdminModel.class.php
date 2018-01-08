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

        protected $_validate = array(
            array('username','require','用户名称为必填！'),
            array('nickname','require','昵称为必填！'),
            array('password','require','初始密码为必填！'),
            array('email','require','邮箱为必填！'),
            array('roleid','require','所属角色为必填！'),
        );

        public function login($username,$password) {
            $row = $this->where("username='$username'")->find();
            if($row) {
                if(password_verify($password,$row['password']) === true) {
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