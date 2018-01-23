<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{
	//自动验证
	protected $_validate = array(
		array('username','require','用户名不能为空!'),
		array('username','','用户名已经被注册','0','unique'),

		array('gender','require','性别不能为空!'),
		array('birthday','require','生日不能为空!'),

		array('email','email','邮箱格式不正确'),
		array('email','','邮箱已经被注册','0','unique'),

		array('phone','/^\d{11}$/','手机号码格式不正确'),
		array('phone','','手机号码已被注册','0','unique'),

		array('password','require','密码不能为空!'),
		array('repassword','require','确认密码不能为空!'),
		array('password','repassword','密码输入不一致','0','confirm'),

	);

	//自动完成
	protected $_auto = array(
		array('create_time','time',1,'function'),
	);
}