<?php
namespace Home\Controller;
use Think\Controller;
require_once './Application/Tools/Gt3/class.geetestlib.php';
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

	//注册
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

	//登录
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
			if($user['is_check'] == 0 && $user['email'] != '' && empty($user['phone'])){
				//邮箱账户,并且不存在手机号时 需要先激活才能登陆
				$this -> ajaxReturnData(10001,'账户未激活');
			}

			//判断用户输入的密码是否正确
			if(password_verify($data['password'],$user['password']) === true)
			{
				session('userinfo',$user);
				cookie('userinfo_id',$user['id']);
				//调用cart模型 cookieTodb方法,迁移购物车数据
				D('cart')->cookieTodb();
				$save['id'] = session('userinfo.id');
				$save['last_login_time'] = time();
				M('User') -> save($save);
				$this -> ajaxReturnData();

			}else{
				$this -> ajaxReturnData(0,'密码错误！');
			}
		}else{
			$this -> ajaxReturnData(0,'请求方式错误');
		}
		
	}

	//发送邮件激活
	public function sendEmail($type){
		!IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'访问方式错误') : true;
		if(isset($type)){
			$type = $type;
		}else{
			I('post.type','','string') ? $type = I('post.type','','string') : $this -> ajaxReturnData(0,'参数错误');//是否存在传递类型
		}
		$sendtime = session('sendtime') ? session('sendtime') : 0;//是否存在过期时间
		time() - $sendtime < 300 ? $this -> ajaxReturnData(0,'发送太频繁，请稍后再试！') : true;//限制发送频率 300秒
		$model = D('User');
		$data['id'] = session('userinfo.id');//获取当前用户id
		$email = I('post.e','','string') ? I('post.e','','string') : $model -> where(['id' => $data['id']]) -> getField('email');//注册页面传递邮箱参数，重置密码页面从数据库获取邮箱
		$data['email_code'] = rand(100000,999999);//生成邮箱验证码
		$res_save = M('User') -> save($data);//保存邮箱验证码
		$res_save !== false ? true : $this -> ajaxReturnData(0,'保存邮箱验证码失败！');
		if($type == 'jihuo'){
			//商城激活模板
			$body = "<html><head><title>零玖一</title></head><body>
					<div style='width:90%;padding:30px;'><p>尊敬的用户：</p><p>您好，感谢您注册零玖一！</p>
					<p>您的激活验证码为：<span style='color:blue'>{$data['email_code']}</span>，30分钟内此验证码有效！</p>
					<p>如您未做出此操作，可能是他人误填，请忽略此邮件。</p><p>本邮件为系统发送，请勿回复。</p></div></body></html>";
		}elseif($type == 'repass'){
			//重置密码模板
			$body = "<html><head><title>零玖一</title></head><body>
					<div style='width:90%;padding:30px;'><p>尊敬的用户：</p>
					<p>我们已经收到您修改密码的请求，您的修改密码验证码为：<span style='color:blue'>{$data['email_code']}</span>
					，30分钟内此验证码有效！</p>
					<p>如您未做出此操作，可能是他人误填，请忽略此邮件。</p><p>本邮件为系统发送，请勿回复。</p></div></body></html>";
		}
		$res = sendmail($email,$body);//发送邮件
		session('sendtime',time());//设置过期时间
		$res !== true ? $this -> ajaxReturnData(0,'发送失败',$res) : $this -> ajaxReturnData();

	}
	//发送短信
	public function sendMsg(){
		!IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'访问方式错误') : true;
		$sendtime = session('sendtime') ? session('sendtime') : 0;
		time() - $sendtime < 300 ? $this -> ajaxReturnData(0,'发送太频繁，请稍后再试！') : true;
		$model = D('User');
		$data['id'] = session('userinfo.id');
		$code = rand(1000, 9999);//短信验证码
		$minute = 300/60;
		$phone = I('post.p','','string') ? I('post.p','','string') : $model -> where(['id' => $data['id']]) -> getField('phone');//注册页面传递手机号，重置密码页面不传递手机号
		$url = "https://api.miaodiyun.com/20150822/industrySMS/sendSMS";//请求地址
		$accountSid = C('MIAODIYUN_CONFIG.ACCOUNT_SID');
		$authToken  = C('MIAODIYUN_CONFIG.AUTH_TOKEN');
		$timestamp  = date('YmdHis');
		$data = array(
			'accountSid'   => C('MIAODIYUN_CONFIG.ACCOUNT_SID'),   //开发者主账号ID
			'smsContent'   => "【零玖一】您的验证码为{1}，请于{2}分钟内正确输入，如非本人操作，请忽略此短信。",    //短信内容
			'templateid'   => '164837011',
			'param'        => $code.','.$minute,
			'to'           => $phone,
			'timestamp'    => $timestamp,  //时间戳 yyyyMMddHHmmss
			'sig'          => MD5($accountSid.$authToken.$timestamp),   //签名
			'respDataType' => 'JSON',    //响应数据类型，JSON 或 XML 格式
		);
		$res = curl_request($url,true,$data);
		echo $res;

	}

	//用户退出
	public function logout(){
		//清除session
		session(null);
		$this->redirect('Index/index');
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
					$this->success('激活成功',U('User/login'));
				}else{
					$this->error('验证码不正确',U('User/register'));
				}
			}else{
				$this->error('用户不存在',U('User/register'));
			}
		}else{
			$this->error('参数不合法',U('User/register'));
		}
	}

	//获取验证
	public function StartCaptchaServlet(){
		$CAPTCHA_ID  = C('GEETESTLIB_CONFIG.CAPTCHA_ID');
		$PRIVATE_KEY = C('GEETESTLIB_CONFIG.PRIVATE_KEY');
		$GtSdk = new \GeetestLib($CAPTCHA_ID, $PRIVATE_KEY);
		session_start();
		$data = array(
				"user_id" => "test", # 网站用户id
				"client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
				"ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
		);
		$status = $GtSdk->pre_process($data, 1);
		$_SESSION['gtserver'] = $status;
		$_SESSION['user_id'] = $data['user_id'];
		echo $GtSdk->get_response_str();
	}
	//提交验证
	public function VerifyLoginServlet(){
		$CAPTCHA_ID  = C('GEETESTLIB_CONFIG.CAPTCHA_ID');
		$PRIVATE_KEY = C('GEETESTLIB_CONFIG.PRIVATE_KEY');
		$GtSdk = new \GeetestLib($CAPTCHA_ID, $PRIVATE_KEY);
		// 比如你设置了一个验证码是否验证通过的标识
		$code_flag = false;
		// 这里获取你之前设置的user_id，传送给极验服务器做校验
		$user_id = $_SESSION['user_id'];
		if ($_SESSION['gtserver'] == 1) {
			$result = $GtSdk -> success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $user_id);
			$result ? $code_flag = true : false;
		}else{
			if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
				$code_flag=true;// 验证码验证成功
			}
		}
//		$code_flag ? $this -> sendEmail() : $this -> ajaxReturnData(0,'验证未通过');// 如果验证码验证成功，再进行其他校验
		session_start();
		$this -> sendEmail($type = 'jihuo');
	}

	//初始化及获取字符串（API1）
	public function pre_process($user_id = null, $new_captcha=1) {
		$data = array('gt'=>$this->captcha_id,
				'new_captcha'=>$new_captcha
		);
		if (($user_id != null) and (is_string($user_id))) {
			$data['user_id'] = $user_id;
		}
		$query = http_build_query($data);
		$url = "http://api.geetest.com/register.php?" . $query;
		$challenge = $this->send_request($url);
		if (strlen($challenge) != 32) {
			$this->failback_process();
			return 0;
		}
		$this->success_process($challenge);
		return 1;
	}
	//二次验证及宕机（API2）
	/**
	 * 正常模式获取验证结果
	 *
	 * @param      $challenge
	 * @param      $validate
	 * @param      $seccode
	 * @param null $user_id
	 * @return int
	 */
	public function success_validate($challenge, $validate, $seccode, $user_id = null, $data='', $userinfo='', $json_format=1) {
		if (!$this->check_validate($challenge, $validate)) {
			return 0;
		}
		$query = array(
				"seccode" => $seccode,
				"data"=>$data,
				"timestamp"=>time(),
				"challenge"=>$challenge,
				"userinfo"=>$userinfo,
				"captchaid"=>$this->captcha_id,
				"json_format"=>$json_format,
				"sdk"     => self::GT_SDK_VERSION
		);
		if (($user_id != null) and (is_string($user_id))) {
			$query["user_id"] = $user_id;
		}
		$url          = "http://api.geetest.com/validate.php";
		$codevalidate = $this->post_request($url, $query);
		$obj = json_decode($codevalidate);
		if ($obj->{'seccode'} == md5($seccode)) {
			return 1;
		} else {
			return 0;
		}
	}
	/**
	 * 宕机模式获取验证结果
	 *
	 * @param $challenge
	 * @param $validate
	 * @param $seccode
	 * @return int
	 */
	public function fail_validate($challenge, $validate, $seccode) {
		if(md5($challenge) == $validate){
			return 1;
		}else{
			return 0;
		}
	}




}
