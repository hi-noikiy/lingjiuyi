<?php
//①声明命名空间 当前分组目录名\Controller
namespace Home\Controller;
//②引入父类控制器 使用use关键字 Think指的是核心目录下的目录(ThinkPHP/Library/Think) Controller指控制器父类 类名
use Think\Controller;
//③定义控制器类 名称和文件名(不带后缀) 保持一致 需要继承父类控制器
class TestController extends Controller{
	//定义一些方法
	public function index(){
		//$page = $_GET['page'];
		//echo 'This is Home Test Index'.$page;
		/*
		//使用U函数动态生成URL
		echo U("Home/Test/index"),"<br>";
		echo U("Home/Test/index","page=100&id=10"),"<br>";
		echo U("Home/Test/index","page=100&id=10",".htm"),"<br>";
		echo U("Home/Test/index","page=100&id=10",".htm","true"),"<br>";
		*/
		
		// $this -> display('');
		// $this -> display('index');
		$person =array(
			'name' => 'zhenzhen',
			'age' => 18
			);
		var_dump($person);
		dump($person);
		
		$this -> display('/Test/index');	
	}

	public function goods(){
		//$model = new \Home\Model\GoodsModel;

		//$model = D('Goods');
		$model = M('Advice',null);

		dump($model);
	}

	public function chaxun(){
		//实例化模型
		$model = D('Goods');
		//查询数据
		// $data = $model->select();  
		// $data = $model->select(2);  //where id = 2
		// $data = $model->select('1,2,3');  //where id in(1,2,3);
		
		// $data = $model->find();  //limit 1
		// $data = $model->find(2);  //where id = 2 limit 1
		
		// 辅助查询
		// $data = $model->where('id > 3 and goods_number=200')->select();  //字符串条件
		// $data = $model->where(array('id'=>3,'goods_number'=>200))->select();  //数组条件
		// $data = $model->where(array('id'=>array('GT',3),'goods_number'=>200))->select();  //数组条件


		// $data = $model->field('id,goods_name,goods_price')->where(array('id'=>array('GT',3),'goods_number'=>200))->select();
		
		// $data = $model->order('id desc')->select();
		// $data = $model->order('id desc')->limit('0,3')->select();
		// $data = $model->order('id desc')->limit(2,3)->select();
		// $data = $model->order('id desc')->limit(3)->select();
		
		$data = $model->field('id,goods_name,goods_price')->having('id>3')->select();  
		// having中的字段必须是查询条件中的字段
		// where中的字段只要是数据表中的字段
		dump($data);

		/*
		$x=5;
		echo $x; //5
		echo $x+++$x++; //  5++
		//$x;  //7
		//$x---$x--; //5-
		//$x
		//5,11,7,1,5
		*/	
	}

	public function tongji(){
		//统计查询
		$model=D('Goods');
		//查询总记录数
		//$total=$model->where('id > 3')->count();
		//Max
		$max = $model->max('id');
		dump($max);
	}

	public function lianbiao(){
		$model = D("Advice");
		//连表查询
		$data = $model->field('t1.*,t2.username')->alias('t1')->join('left join tpshop_user t2 on t1.user_id = t2.id')->select();
		dump($data);
	}

	public function zhanshi(){
		//普通变量
		$username = '';
		//数组变量
		$person = array('name'=>'kongkong','age'=>500,'sex'=>1);
		//二维数组
		$model = D('Goods');
		$data = $model->select();

		$a=100;
		$b=10;
		
		//变量赋值
		$this->assign('username',$username);
		$this->assign('person',$person);
		$this->assign('data',$data);
		$this->assign('a',$a);
		$this->assign('b',$b);
		$this->display();
	}

	public function tianjia(){
		/*
		//准备要添加的数据
		$data = array(
			'user_id'=>1,
			'content'=>'测试添加功能'
			);
		//实例化模型
		$model = D('Advice');
		// $model = M('Advice',null);
		
		// 普通数组添加方式
		// add(一维数组)
		$advice_id = $model -> add($data);  //返回添加成功之后的主键值,失败返回false
		dump($advice_id);
		*/
		/*
		//添加多条数据
		$data = array(
			array(
			'user_id'=>2,
			'content'=>'测试添加功能2'
			),
			array(
			'user_id'=>3,
			'content'=>'测试添加功能3'
			),
		);
		$model = D('Advice');
		//调用addAll方法
		$res = $model->addAll($data);
		dump($res);
		*/
		
		//AR方式
		$model = D('Advice');
		//把要添加的数据设置到对象的属性上
		$model -> user_id = '3';
		$model -> content = '测试AR方式添加';
		//使用add方法添加数据到数据库
		$id = $model -> add();
		dump($id);

	}

	public function xiugai(){
		/*
		$model = D('Advice');
		$data = array(
			'content' => '测试修改功能',
		);
		$res = $model->where("user_id = 1")->save($data);
		dump($res);
		*/
	/*
		//AR方式修改
		//把药修改的数据设置到类的属性上
		$model = D('Advice');
		$model->id = 5;
		$model->content = "测试你是不是9.1生日";
		//使用save方法
		$res = $model->save();
		dump($res);
		*/
	
		//如果使用AR方法,类的属性里没有包含主键字段,则需要where辅助方法
		$model = D('Advice');
		//把要修改的数据设置到类的属性上
		$model->content = "测试if you not love me";
		$res = $model->where("user_id = 3")->save();
		dump($res);

	}

	public function shanchu(){
		/*
		//实例化模型
		$model = D('Advice');
		//使用delete方法删除数据
		$res = $model->delete(2); //删除一条数据
		$res= $model->delete('2,3,4'); //删除多条数据
		$res= $model->where('user_id = 2')->delete(); //当删除的不是主键时,通过where条件删除
		dump($res);
		*/
	}

	public function moxin(){
		$model = D('Advice');
		dump($model);
	}

	public function test_cookie(){
		//设置cookie
		cookie('username','zhanzhan');
		//读取cookie
		echo cookie('username');
		//删除 cookie
		cookie('username',null);
		dump(cookie('username'));
		//设置cookie参数
		//直接设置有效期
		// cookie('age',18,3);
		dump(cookie('age'));

		//以字符串方式设置有效期及其他参数
		cookie('age',18,'expire=3&prefix=tpshop_');
		// dump(cookie('age'));  //null
		dump(cookie('tpshop_age'));  //int(18)

		//以数组方法设置有效期和前缀
		cookie('age',18,array('expire'=>3,'prefix'=>'tpshop_'));
		dump(cookie('tpshop_age'));

		//如果是在配置文件中设置的前缀,在读取和设置等操作是不用手动添加
		
		
		//删除cookie
		cookie(null);	//删除所有带默认前缀的cookie
		dump(cookie('tpshop_age'));

		cookie(null,'tpshop_'); 	//删除带指定前缀的cookie
		dump(cookie('tpshop_age'));

	}

	public function test_session(){
		/*
		//设置session
		session('user','kongkong');
		//读取session
		dump(session('user'));	//string(8) "kongkong"
		//读取所有session
		dump(session());	//array(1) {["user"] => string(8) "kongkong"}
		//删除session
		session('user',null);
		dump(session());	//array(0) {}
		//删除所有session
		session(null);
		dump(session());	//array(0) {}
		*/
		
		//session中数组的 使用
		session('person',array('name'=>'kongkong','sex'=>'1'));
		dump(session('person'));

		//直接修改session中数组的某一个值
		session('person.sex',2);
		dump(session('person'));

		//直接读取session中数组的某一个值
		dump(session('person.sex'));
		//判断是否设置session,返回布尔值
		dump(session('?person'));
		dump(session('?person_n'));	
	}

	public function test_xss(){
		if(IS_POST){
			//处理表单
			$data = $_POST;
			//1. $data['content'] = htmlspecialchars($data['content']);
			$data['content'] = strip_tags($data['content']);
			$data['user_id'] = 1;
			$model = D('Advice');
			$res = $model->add($data);
			if($res){
				$this->success('添加成功!');
			}else{
				$this->error('添加失败!');
			}
		}else{
			$model = D('Advice');
			$advice = $model->order('id desc')->find();
			$this->assign('advice',$advice);
			$this->display();
		}
		
	}

	public function test_curl(){
		$url = U('Home/Test/curl_api','','',true);
		//测试get请求
		dump($url);
		$data = array('id' => 10,'page' => 100);
		$res = curl_request($url,true,$data);
		dump($res);
	}

	public function curl_api(){
		$data = I('post.');
		$return = array(
			'code' => 10000,
			'msg' => 'success',
			'data' => $data,
		);
		$this->ajaxReturn($return);
	}

	//快递查询
	public function kuaidi(){
		//快递公司代码
		$type = 'yunda';
		//运单编号
		$postid = '3101314976598';
		//接口地址
		$url = "https://www.kuaidi100.com/query?type={$type}&postid={$postid}";
		//发送请求
		$res = curl_request($url,false,array(),true);
		//解析返回结果
		if($res == false){
			//请求失败
			echo '请求失败!';die;
		}
		$res_arr = json_decode($res,true);
		// dump($res_arr);
		if($res_arr['status'] != 200){
			echo '查询失败!';die;
		}
		//遍历数据,直接echo到页面
		foreach($res_arr['data'] as $k => $v){
			if($k == 0){
				//给最新的进度记录做突出显示
				echo "<p style='color:red;'>";
				echo $v['time'].'----'.$v['context']."</p>";
			}else{
				echo $v['time'].'----'.$v['context']."<br>";
			}
		}
	}
	/**
	 * 邮件涉及到的协议:
	 * SMTP(simple mail transfer protocol)
	 * 简单邮件传输协议
	 * 它是一组由原地址到目的地址传送邮件的规则,由他来控制新建的中转方式
	 * SMTP协议属于TCP/IP协议族,他帮助每台计算机在发送或中转新建时找到下一个目的地.
	 * 通过SMTP协议所指定的服务器,就可以吧E-mail寄到收信人的服务器上了,SMTP服务器则是遵循SMTP协议的发送邮件服务器
	 * 用来发送或中转发出的电子邮件
	 *
	 * POP3(post office protocol-version 3)
	 * 邮局协议版本3
	 * 是TCP/IP协议族的一员,由PFC1939定义
	 */
	
	//调用地图静态
	public function jingtaitu(){
		layout(false);
		//授权码AK
		$ak = '6UIokkDQpayHubILj5Vq0CXGLgCBXGGE';
		//中心点 经度,纬度 坐标
		$center = "116.403874,39.914888";
		$url = "http://api.map.baidu.com/staticimage/v2?ak={$ak}&center={$center}";
		$res = file_get_contents($url);
		$this->assign('url',$url);
		$this->display();
		// echo $res;die;
		// var_dump($res);die;
	}

	//地图动态图
	public function dongtaitu(){
		layout(false);
		//位置 纬度,经度
		$location = '40.107055,116.374194';
		//title标题
		$title = 'PHP58大本营';
		//内容
		$content = "修正大厦306";
		//输出类型
		$output = 'html';
		//src addName
		$src = '传智';
		//接口地址
		$url = "http://api.map.baidu.com/marker?location={$location}&title={$title}&content={$content}&output={$output}&src={$src}";
		// echo $url;die;
		// $res = file_get_contents($url);
		// var_dump($res);die; //直接调用返回结果为false
		$this->assign('url',$url);
		$this->display();
		
	}

	//测试邮件发送
	public function test_email(){
		$email = '313564624@qq.com';
		$subject = '使用PHPMailer';
		$body = '使用PHP发送邮件';
		$res = sendmail($email,$subject,$body);
		if($res === true){
			echo 'success';
		}else{
			echo $res;
		}
	}

	public function yan(){
		/*
		$count = 5;
		function get_count(){
			static $count = 0;
			return $count++;
		}
		echo $count;
		++$count;
		echo get_count();
		echo get_count();

		echo '<hr>';


		$GLOBALS['var1'] = 5;
		$var2 = 1;
		function get_value(){
			global $var2;
			$var1 = 0;
			return $var2++;
		}
		get_value();
		echo $var1;
		echo $var2;

		echo '<hr>';


		$test = 'aa';
		$abc = &$test;
		unset($test);
		echo $abc;

		echo '<hr>';
		*/
		function get_arr($arr){
			unset($arr[0]);
		}
		$arr1 = array(1,2);
		$arr2 = array(1,2);
		//get_arr(&$arr1);
		get_arr($arr2);
		echo count($arr1);
		echo count($arr2);
	}

}
