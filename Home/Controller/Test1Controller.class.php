<?php
namespace Home\Controller;
use Think\Controller;
class Test1Controller extends Controller{
	/**
	 * 电商项目的后台管理中有一个查询订单功能，可以根据用户名、下单日期来查询订单详情。
	 *该功能需要3张表: 会员表、订单表、商品表。 请自己设计这3张表，写出主要字段即可。
	 *请编写2个方法查询订单详情。结果信息必须包含订单编号、下单人、商品名称、订购数量。
	 *1. 根据用户名（模糊查询）查询  （getOrderByName）
	 *2. 根据下单时间区间查询        （getOrderByTime）
	 *3. 使用ajax方式完成
	 *4. 本题不需要给出数据库配置文件、自定义模型代码
	 *
	 */
	/**
	 * CREATE TABLE `user`(
	 * `id` int(11) AUTO_INCREMENT ,
	 * `user_name` varchar(40) not null default ''
	 * )CHARSET=utf8;
	 *
	 *
	 * CREATE TABLE `goods`(
	 * `id` int(11) AUTO_INCREMENT ,
	 * `goods_name` varchar(40) NOT NULL DEFAULT '',
	 * `goods_price` double NOT NULL DEFAULT ''
	 * )CHARSET=utf8;
	 *
	 *
	 * CREATE TABLE `order`(
	 * `id` int(11) AUTO_INCREMENT ,
	 * `goods_id` int(11) NOT NULL ,
	 * `goods_name` varchar(40) NOT NULL DEFAULT '',
	 * `number` int(11) NOT NULL DEFAULT 1,
	 * `create_time` int(20) 
	 * )CHARSET=utf8;
	 */
	
	public function index(){
		layout(false);
		$this->display();
	}

	public function getOrderByName(){
		
	}

	public function getOrderByTime(){
		$data = I('post.');
		dump($data);
	}

	public function captch(){
		$data = array(
			'lenght'=>4,
			'useCure'=>false,
			'useNose'=>false
		);
		$verify = new \Think\Verify($data);
		$verify->entry();

	}
/*
	public function login(){
		$data = I('post.');
		$verify = new \Think\Verify;
		$check = $verify->check($data['code']);
		if(!$check){
			$this->error('验证码错误');
		}
		$model = D('tp_user');
		$user = $model->where(array("name => $data['name']"))->find();
		if($data['passwd'] == $user['passwd'] && $user){
			$this->success('登录成功',U('Home/Main/index'));
		}else{
			$this->error('用户名或密码错误');
		}
	}
*/



protected $_validate = array(
	array('name','/^[\w]{6,12}$/','用户名格式不正确'),
	array('passwd','require','用户密码不能为空'),
	array('repasswd','','两次输入密码不一致','passwd'),
);
	protected $_auto = array(
		array('roleid','1',1,'function'),
		array('addtime','time',1,'function'),
	);
}