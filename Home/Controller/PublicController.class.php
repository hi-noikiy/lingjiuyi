<?php
namespace Home\Controller;
use Think\Controller;
class PublicController extends Controller{

	protected function ajaxReturnData($code = 10000, $msg = 'success',$info = [])
	{
		//设置返回数据格式
		$data = array(
				'code' => $code,
				'msg'  => $msg,
				'info' => $info,
		);
		//返回数据
		$this->ajaxReturn($data);
	}

	public function register(){		
		if(IS_POST){
			//接收数据
			$data = I('post.');
			//验证短信验证码(如果是手机号注册)
			if(isset($data['phone'])){
				//验证验证码及手机号
				//参数校验
				//验证验证码的有效期
				$sendtime = session('sendtime') ? session('sendtime') : 0;
				if(time() - $sendtime > 300){
					//过期
					session('register_code_'.$data['phone'],null);
					$this->error('验证码已失效');
				}
				//验证验证码的正确性
				if(session('register_code_'.$data['phone']) != $data['code']){
					$this->error('短信验证码不正确');
				}else{
					//验证通过,让验证失效
					session('register_code_'.$data['phone'],null);
					//手机号注册 is_check 表示不用激活邮箱
					$data['is_check'] = 1;
				}
			}
			$data['code'] = null;
			//邮箱注册
			if(isset($data['email'])){
				//生成一个验证码,保存到email_code字段
				$email_code = rand(100000,999999);
				$data['email_code'] = $email_code;
			}		
			$model = D('User');
			if(!$model->create($data)){
				$error = $model->getError();
				$this->error($error);
			}
			// dump($data);die;
			$res = $model->add();
			if($res){
				//如果是邮箱注册,需要发送激活邮件
				if(isset($data['email'])){
					$email = $data['email'];
					//调用sendmail函数发送邮件
					$res = sendmail($email,$res,$email_code);
					if($res !== true){
						//发送失败
						sendmail($email,$res,$email_code);
					}
				}
				//自动登录
				// $user = $model->find($res);
				// session('userinfo',$user);
				$this->success('注册成功!',U('Home/User/login'));

			}else{
				$this->error('注册失败!');
			}
		}else{
			//显示页面
			layout(false);
			$this->display();
		}
		
	}

	public function login(){
		if(IS_POST){
			//接收数据
			$data['username'] = I('post.e');
			$data['password'] = I('post.p');

			$model = D('User');
			//必须做校验
			if(empty($data['username']) || empty($data['password'])){
				$this -> ajaxReturnData(0,'必填项不能为空');
			}
			$user = $model->where("username='{$data['username']}' or email='{$data['username']}' or phone='{$data['username']}'")->find();
			empty($user) ? $this -> ajaxReturnData(0,'用户名/邮箱/手机号不存在！') : true;
			if($user['is_check'] == 0 && $user['email'] != ''){
				//邮箱账户需要先激活才能登陆
				$this -> ajaxReturnData(10001,'账户未激活');
			}

			$hash = encrypt_pwd($data['password']);
			//判断用户输入的密码是否正确
			if($user['password'] == password_verify($data['password'],$hash))
			{
				session('userinfo',$user);
				//调用cart模型 cookieTodb方法,迁移购物车数据
				D('cart')->cookieTodb();

				$this -> ajaxReturnData();

			}else{
				$this -> ajaxReturnData(0,'密码错误！');
			}
		}else{
			$this -> ajaxReturnData(0,'请求方式错误');
		}
		
	}

	//发送邮件激活
	public function sendEmail(){
		$email = I('post.e');
		$model = D('User');
		$id = $model -> where(['email' => $email]) -> getField('id');
		$data['email_code'] = rand(100000,999999);
		$res_save = $model -> where(['id' => $id]) -> save($data);
		empty($res_save) ? $this -> ajaxReturnData(0,'保存邮箱验证码失败！') : true;
		$res = sendmail($email,$id,$data['email_code']);
		$this -> ajaxReturnData(0,'',$res);
		$res !== true ? $this -> ajaxReturnData(0,'发送失败',$res) : $this -> ajaxReturnData();

	}

	//用户退出
	public function logout(){
		//清除session
		session(null);
		$this->redirect('Home/User/login');
	}

	//邮箱账号激活地址
	public function jihuo(){
		//接收用户点击链接之后的参数
		$data = I('get.');
		//参数校验
		$user_id = $data['id'];
		$code = $data['code'];
		if($user_id && $code){
			//判断用户是否存在
			$user = D('User')->find($user_id);
			if($user){
				//检验验证码
				if($user['email_code'] == $code){
					//激活用户,修改数据库中的激活状态
					$user['is_check'] = 1;
					D('User')->save($user);
					$this->success('激活成功',U('Home/User/login'));
				}else{
					$this->error('验证码不正确',U('Home/User/register'));					
				}
			}else{
				$this->error('用户不存在',U('Home/User/register'));
			}
		}else{
			$this->error('参数不合法',U('Home/User/register'));
		}
	}


	//用户首页
	public function index(){

	}



}
