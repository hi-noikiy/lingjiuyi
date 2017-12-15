<?php
namespace Admin\Controller;
use Think\Controller;
class GoodsController extends BaseController{

	public function index(){
		if(IS_AJAX){
			//查询商品数据
			//实例化模型
			$model = D('Goods');
			//使用select查询数据 加上limit条件
			$goods = $model -> select();
			$data  = ['data' => $goods];
			//变量赋值
			$this ->ajaxReturn($data);
		}else{
			$this -> display();
		}

	}

	public function add(){
		if(IS_POST){
			//添加数据
			$data['goods_name']        = $_POST['goods_name'];
			$data['goods_bigprice']    = $_POST['goods_bigprice'];
			$data['goods_price']       = $_POST['goods_price'];
			$data['goods_number']      = $_POST['goods_number'];
			$data['goods_introduce']   = remove_xss($_POST['goods_introduce']);
			$data['goods_smallprice']  = $_POST['goods_smallprice'];
			$data['goods_create_time'] = time();
			$data['cate_id']           = $_POST['cate_id'];
			$data['is_act']            = $_POST['act'];
			$data['attr_value']        = $_POST['attr_value'] ? $_POST['attr_value'] : '';
			$id = D('Goods') -> add($data);//添加商品信息
			$id ? true : $this -> error('添加商品数据失败！');


			if(!empty($data['attr_value'])){
				$attr = [];
				$i = 0;
				foreach($_POST['attr_value'] as $k => $v){
					foreach($v as $key => $value){
						$attr[$i]['goods_id']  = $id;
						$attr[$i]['attr_id']    = $k;
						$attr[$i]['attr_value'] = $value;
						$i++;
					}
				}
				unset($k,$v);
				unset($key,$value);
				$attrs = D('Goods_attr') -> addAll($attr);//添加商品属性
				$attrs ? true : $this -> error('添加商品属性失败！');
			}

			if(!empty($_FILES['goods_small_img']) && $_FILES['goods_small_img']['error'] == 0){
				$str1  = 'imageView2/0/q/75|watermark/2/text/bGluZ2ppdXlp/font/dmVyZGFuYQ==/fontsize/';
				$str2 = '/fill/IzMyQjJFNA==/dissolve/100/gravity/SouthEast/dx/10/dy/10|imageslim';//水印样式
				$file['goods_small_img'] = $_FILES['goods_small_img'];
				//添加商品表大图和生成缩略图
				//进行文件上传,使用七牛云
				$setting = C('UPLOAD_QINIU');
				$Upload  = new \Think\Upload($setting);
				$info    = $Upload -> Upload($file);
				$info ? true : $this -> error('上传图片失败！');//判断是否上传成功
				$img['goods_big_img']   = $info['goods_small_img']['url'].'?imageMogr2/thumbnail/350x350>|'.$str1.'520'.$str2;//大图
				$img['goods_small_img'] = $info['goods_small_img']['url'].'?imageMogr2/thumbnail/150x150>|'.$str1.'200'.$str2;//缩略图
				$res = D('Goods') -> where("goods_id = $id") -> save($img);//保存商品logo
				$res !== false ? true : $this -> error('添加商品logo失败！');

				if(!empty($_FILES['goods_pics'])){
					//上传相册图片
					if(is_array($_FILES['goods_pics']['name'])){
						//如果相册为数组，则需要循环,转换样式
						$files = [];
						for($i=0;$i<count($_FILES['goods_pics']['name']);$i++){
							foreach($_FILES['goods_pics'] as $key => $value){
								foreach($value as $k => $v){
									($k == $i) ? $files[$i][$key] = $v : true;
								}
							}
						}
						unset($k,$v);
						unset($key,$value);
						$info = $Upload -> upload($files);//多文件上传
						empty($info) ? $this -> error('多文件上传失败！') : true;
						$pics = [];
						foreach($info as $k => $v){
							$pics[$k]['goods_id']    = $id;
							$pics[$k]['pics_origin'] = $info[$k]['url'].'?'.$str1.'520'.$str2;//原图
							$pics[$k]['pics_big']    =   $info[$k]['url'].'?imageMogr2/thumbnail/800x800|'.$str1.'520'.$str2;//大图
							$pics[$k]['pics_mid']    =   $info[$k]['url'].'?imageMogr2/thumbnail/350x350|'.$str1.'520'.$str2;//中图
							$pics[$k]['pics_sma']    =   $info[$k]['url'].'?imageMogr2/thumbnail/150x150|'.$str1.'200'.$str2;//小图
						}
						$res = D('Goodspics') -> addAll($pics);
						empty($res) ? $this -> error('添加多个图片失败！') : $this -> success('商品信息添加成功');
					}else{
						$info = $Upload -> Upload($file);//单文佳上传
						$pics['goods_id']    = $id;
						$pics['pics_origin'] = $info['url'].'?'.$str;
						$pics['pics_big']    =   $info['url'].'?imageMogr2/thumbnail/800x800|'.$str;
						$pics['pics_mid']    =   $info['url'].'?imageMogr2/thumbnail/350x350|'.$str;
						$pics['pics_sma']    =   $info['url'].'?imageMogr2/thumbnail/50x50|'.$str;
						$res = D('Goodspics') -> add($pics);
						empty($res) ? $this -> error('添加单个图片失败！') : $this -> success('商品信息添加成功');
					}
				}else{
					$this -> success('商品信息添加成功');
				}
			}else{
				$this -> error('商品logo为必传图片！');
			}

		}else{
			//展示页面
			$cates = D('Category') -> where("pid = 0") -> select();
			$this -> assign('cates',$cates);
			$this -> display();
		}

	}

	public function edit(){
		$id = I('get.id','','intval');//商品id
		if(IS_POST){
			//商品基本信息 goods
			//商品属性管理 goods_attr
			//商品相册 goodspics
			strlen(I('post.goods_name')) > 40 ? $this -> ajaxReturnData(0,'商品名称不能超过40个字符！') : true;
			$data = I('post.');

			//如果有商品id，则是修改商品信息
			$data['id'] = I('post.id','','intval');
			$data['pics_origin'] = I('post.url') ? I('post.url') : '';
			if(empty($data['pics_origin'])){
				//添加商品编
			}else{
				//添加商品图片表

			}

		}else{

			//如果有商品id，则获取商品信息展示页面
			$where = "goods_id = $id";
			$field = 'a.*,b.cate_name cate3_name,c.cate_name cate2_name,c.id cate2_id,d.cate_name cate1_name,d.id cate1_id';
			$goods = D('Goods')
					-> alias('a')
					-> field($field)
					-> where($where)
					-> join('zhouyuting_category b on a.cate_id = b.id')
					-> join('zhouyuting_category c on b.pid = c.id')
					-> join('zhouyuting_category d on c.pid = d.id')
					-> find();//商品基本信息

			$gattr = D('Goods_attr') -> where($where) -> order('id asc') -> select();//商品属性
			$new_gattr = array();
			foreach($gattr as $k => &$v){
				$new_gattr[$v['attr_id']][] = $v['attr_value'];//修改数组下标为属性id，将一个属性归类到一起
			}
			unset($k,$v);

			$attrs = D('Attribute') -> where(['cate_id' => $goods['cate1_id']]) -> order('attr_id asc') -> select();//分类全部属性
			foreach($attrs as $k => &$v){
				$v['attr_values'] = explode(',',$v['attr_values']);//分割字符串
			}
			unset($k,$v);

			$gpics = D('Goodspics') -> field('id,pics_big,pics_mid') -> where($where) -> select();//商品相册
			dump($goods);

			$cates = D('Category') -> where("pid = 0") -> select();

			$this -> assign('goods',$goods);
			$this -> assign('attrs',$attrs);
			$this -> assign('new_gattr',$new_gattr);
			$this -> assign('gpics',$gpics);
			$this -> assign('cates',$cates);
			$this -> display();

		}
	}

	//删除相册
	public function del(){
		$id   = I('post.id','','intval');
		$goods_id = I('post.goods_id','','intval');
		$setting = C('UPLOAD_QINIU');
		$Qiniu = new \Think\Upload\Driver\Qiniu\QiniuStorage($setting['driverConfig']);
		if($goods_id){
			//删除商品图片和商品信息
			$where  = "goods_id = $goods_id";
			$gimg   = D('Goods') -> where($where) -> getField('goods_big_img');
			$url    = substr($gimg,strpos($gimg,'/upload') + 1,strrpos($gimg,'?') - (strpos($gimg,'/upload') + 1));
			$resimg = $Qiniu -> del($url);//删除商品logo
			$resimg !== false ? true : $this -> ajaxReturnData(0,'删除商品logo失败');
			$res   = D('Goods') -> delete($goods_id);//删除商品信息
			$pics  = D('Goodspics') -> where($where) -> select();//查询相册
			if(empty($pics)){
				$res ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'删除商品失败');//如果相册为空则返回成功信息
			}else{
				//删除相册
				$res ? true : $this -> ajaxReturnData(0,'删除商品失败');
				foreach($pics as $k => $v){
					$start  = strpos($pics[$k]['pics_origin'],'/upload') + 1;
					$end    = strrpos($pics[$k]['pics_origin'],'?');
					$url    = substr($pics[$k]['pics_origin'],$start,$end-$start);
					$resimg = $Qiniu -> del($url);//删除商品logo
					$resimg !== false ? true : $this -> ajaxReturnData(0,'删除商品相册失败');
				}
				$res = D('Goodspics') -> where($where) ->delete();
				$res ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'删除相册数据失败！');
			}


		}elseif($id){
			//删除商品相册图片后期修改
		}else{
			$this -> ajaxReturnData(0,'参数错误');
		}
		die;
		$pics = D('Goodspics') -> find($id);
		$res  = D('Goodspics') -> delete($id);
		if($res !== false){
			//删除成功
			//unlink(ROOT_PATH.$pics['pics_origin']);//删除本地文件

			$setting = C('UPLOAD_QINIU');
			$Qiniu = new \Think\Upload\Driver\Qiniu\QiniuStorage($setting['driverConfig']);
			$url = 'http://owtcx73yl.bkt.clouddn.com/upload2017-12-07_5a28eaa4092e9.jpg?imageMogr2/thumbnail/50x50|imageView2/0/q/75|watermark/2/text/bGluZ2ppdXlp/font/dmVyZGFuYQ==/fontsize/520/fill/IzMyQjJFNA==/dissolve/100/gravity/SouthEast/dx/10/dy/10|imageslim';
			$res = $Qiniu -> del($url);
			$res[0] = $Qiniu -> del($pics['pics_origin']);
			$res[1] = $Qiniu -> del($pics['pics_big']);
			$res[2] = $Qiniu -> del($pics['pics_mid']);
			$res[3] = $Qiniu -> del($pics['pics_sma']);
			$error  = $Qiniu -> errorStr;//错误信息
			(is_array($res) && !($error)) ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'删除图片失败',$error);
		}else{
			$this -> ajaxReturnData(0,'删除数据库失败！');
		}
	}

	//删除全部商品信息
	public function del_copy(){
		//删除商品
		//接收get请求中的id值
		$id = I('get.id','','intval');
		//实例化模型
		$model = D('Goods');
		$res = M('Goodspics');
		$goods = $model->find($id);
		$pics = $res-> where("goods_id = $id")-> select();

		// unlink(ROOT_PATH.$goods['goods_big_img']);
		unlink(ROOT_PATH.$goods['goods_big_img']);
		unlink(ROOT_PATH.$goods['goods_small_img']);
		foreach($pics as $k => $v){
			unlink(ROOT_PATH.$pics[$k]['pics_origin']);
			unlink(ROOT_PATH.$pics[$k]['pics_big']);
			unlink(ROOT_PATH.$pics[$k]['pics_mid']);
			unlink(ROOT_PATH.$pics[$k]['pics_sma']);
		}
		$model = $model -> delete($id);
		$res = $res -> where("goods_id = $id") -> delete();
		if($res !== false && $model !== false){
			//成功
			$attrs = M('GoodsAttr')->where("goods_id = $id")->delete();
			if($attrs){
				$this->success('数据删除成功!',U('Admin/Goods/goods_list'));
			}
		}else{
			//失败
			$this->error('数据删除失败!');
		}
	}




	public function goods_add(){
		/*
		// echo encrypt_phone('13164606761');die;
		
		//load函数手动加载函数文件
		//需要指定函数文件放在Application目录下的分组,以及文件名称
		load('Common/str');
		//@符号表示在当前的分组中找str文件,当前分组为Admin
		// load('@/str');
		echo encrypt_password('13164606761');die;
		*/	
		
		// $page = new \Tools\Page();
		// echo $page->getPage();die;


		//一个方法处理两个业务逻辑		
		if(IS_POST){

			//2.接收表单提交数据
			// $data = $_POST[];
			// 使用I函数接收数据
			$data = I('post.');
			//对商品简介字段做特殊处理 不适用I函数的默认过滤方法 
			//需要防范xss攻击
			$data['introduce'] = remove_xss($_POST['introduce']);		
			//添加数据到数据库
			//实例化Goods模型
			$model = D("Goods"); 
			// dump($_FILES);die;
			//调用模型中的upload_logo方
			$data = $model->upload_logo($data,$_FILES['logo']);
			if(!$data){
				$error = $model->getError();
				$this->error($error);
			}

			//使用create()方法自动创建数据
			$create = $model->create($data);//不传参数是,默认取('post.')
			if(!$create){
				//如果返回值为false,表示模型中发生错误
				$error = $model->getError();
				$this->error($error);
			}
			$goods_id = $model -> add();//不传递参数

			if($goods_id){
				//添加成功,跳转到商品列表页
				//添加成功之后,进行商品相册上传(因为要goods_id)
				//只处理相册,删除logo图片信息
				$files = $_FILES;
				unset($files['logo']);
				// dump($files);die;
				$model->upload_pics($goods_id,$files);

				//添加商品属性到商品属性关联表
				
				foreach($data['attr_value'] as $k => $v){
					$attr['goods_id'] = $goods_id;
					$attr['attr_id'] = $k;

					foreach($v as $key => $value){
						$attr['attr_value'] = $value;
						
						M('GoodsAttr') -> add($attr);
					}

				}	


				$this->success("添加成功!",U("Admin/Goods/goods_list"),3);
			}else{
				//添加失败,跳转到商品添加页面
				$this->error("添加失败!");
			}

			
		}else{
			//1.显示页面
			//获取商品类型信息
			$type = M('Type')->select();
			$attrs = M('Attribute')->where('cate_id = 12')->select();
			foreach($attrs as $k => $v){
				$v['attr_values'] = explode(',',$v['attr_value']);
			}
			unset($k,$v);
			//获取所有的商品分类
			$category = M('Category')->select();
			$category = getTree($category);
			$this->assign('type',$type);
			$this->assign('attrs',$attrs);
			$this->assign('category',$category);
			$this->display();
		}
		
		
	}
	public function goods_edit(){
		//两个业务逻辑 根据请求方式来判断
		if(IS_POST){
			//form表单提交
			$data = I('post.');
			//对商品简介字段做特殊处理,防范xss攻击
			$data['introduce'] = remove_xss($_POST['introduce']);
			//实例化模型
			$model = D('Goods');

			if(!empty($_FILES['logo']) && $_FILES['logo']['error'] == 0){
				//调用模型的upload_logo方法实现文件上传
				$data = $model->upload_logo($data,$_FILES['logo']);
				if(!$data){
					//如果必须上传图片才能添加一个新商品,则这里需要报错
					//修改功能一般不要求上传新图片
					$data = I('post.');
				}else{
					$goods = $model->find($data['id']);
				}
			}

			$create = $model -> create($data);
			if(!$create){
				$error = $model->getError();
				$this->error($error);
			}
			$res = $model->save();
			if($res !== false){
				//成功返回值为0 页表示成功
				//如果上传新图片,删除旧图片
				if(isset($data['goods_big_img'])){
					//旧图片地址(修改之前获取)
					unlink(ROOT_PATH.$goods['goods_big_img']);
					unlink(ROOT_PATH.$goods['goods_small_img']);
				}

				//商品相册图片修改(继续上传新图片)
				$files = $_FILES;
				unset($files['logo']);
				$model->upload_pics($data['id'],$files);

				// dump($data);
				/*
				  ["attr_value"] => array(6) {
				    [9] => array(6) {
				      [0] => string(6) "原味"
				      [1] => string(6) "炭烧"
				      [2] => string(6) "奶油"
				  	}
				  	[10] => array(1) {
					    [0] => string(6) "武汉"
					}
				}
				*/
				//商品属性修改
				foreach($data['attr_value'] as $k => $v){
					//$k 就是attr_id值
					foreach($v as $attr){
						//$attr 就是attr_value 值
						$attr_data[] = array(
							//上面添加商品时返回值 就是添加成功的主键id
							'goods_id' => $data['id'],
							'attr_id' => $k,
							'attr_value' => $attr
						);
					}
				}
				// dump($attr_data);die;
				//先删除商品原来的属性
				M('GoodsAttr')->where("goods_id = {$data['id']}")->delete();
				//多条属性数据的批量添加操作
				$attr_res = M('GoodsAttr')->addAll($attr_data);
				//对应$attr_res 这个结果,可以不做处理
				
				$this->success("数据修改成功!",U('Admin/Goods/goods_list'),3);
			}else{
				//失败
				$this->error("数据修改失败!");
			}
		}else{
			//展示页面
			//接收get请求中的id值
			$id = I('get.id');
			//实例化商品模型
			$model = D('Goods');
			//查询数据
			$goods = $model->find($id);
			// dump($goods);die;
			//查询当前商品相册中的所有图片
			$goods_pics = M('Goodspics')->where("goods_id=$id")->select();
			//获取商品类型信息
			$type = M('Type')->select();

			//获取商品属性信息(tpshop_attribute表)
			$attrs = M('Attribute')->where("cate_id = {$goods['cate_id']}")->select();
			//把每个属性中的可选值转化为数组(方便页面遍历操作)
			foreach($attrs as $k => &$v){
				$v['attr_values'] = explode(',',$v['attr_values']); 
			}
			unset($k,$v);
			// dump($attrs);
			
			// 获取当前商品拥有的所有属性(tpshop_goods_attr表)
			$goods_attr = M('GoodsAttr')->where("goods_id = $id")->select();
			//对goods_attr做处理,转化成
			//array('attr_id'=>array('attr_value','attr_value'))即:
			$new_goods_attr = array();
			foreach($goods_attr as $k => $v){
				$new_goods_attr[$v['attr_id']][] = $v['attr_value'];
			}
			unset($k,$v);
			// dump($goods_attr);
			/*
			array(5){
				[0]=>array(4){
					['id'] => '22',
					["goods_id"] => string(2) "42"
    				["attr_id"] => string(1) "9"
    				["attr_value"] => string(6) "原味"
				}
			}
			*/
			// dump($new_goods_attr);
			//array('10'=>array('北京昌平'),'11'=>array('210g'),'12'=>array('原味','奶油'))
			
			$category = M('Category')->select();
			$category = getTree($category);
			$this->assign('goods',$goods);
			$this->assign('goods_pics',$goods_pics);
			$this->assign('type',$type);
			$this->assign('attrs',$attrs);
			$this->assign('goods_attr',$goods_attr);
			$this->assign('new_goods_attr',$new_goods_attr);
			$this->assign('category',$category);
			$this->display();
		}

	}
	public function goods_detail(){
		//接收get方式的数据id
		$id = I('get.id');
		//查询商品信息
		$goods = D('Goods')->find($id);
		$this->assign('goods',$goods);
		//获取商品销售额数据
		//组装返回数据的格式
		$sales = array(
			array('name' => 'offline','data' => []),
			array('name' => 'online','data' => []),
		);
		
		//查询线下销售额
		$offline_data = D('Saleoffline')->where("goods_id = $id")->order('month asc')->select();

		foreach($offline_data as $k => $v){
			$sales[0]['data'][] = floatval($v['money']);
		}
		unset($k,$v);
		//查询线上的销售额
		$online_data = D('Saleonline')->where("goods_id = $id")->order('month asc')->select();
		foreach($online_data as $k => $v){
			$sales[1]['data'][] = floatval($v['money']);
		}
		//转化json格式的数据
		$sales_json = json_encode($sales);
		// dump($sales_json);die;
		$this->assign('sales_json',$sales_json);
		$this->display();
	}

	public function goods_del(){
		//删除商品
		//接收get请求中的id值
		$id = I('get.id');
		//实例化模型
		$model = D('Goods');
		$res = M('Goodspics');
		$goods = $model->find($id);
		$pics = $res-> where("goods_id = $id")-> select();
		
		// unlink(ROOT_PATH.$goods['goods_big_img']);
		unlink(ROOT_PATH.$goods['goods_big_img']);
		unlink(ROOT_PATH.$goods['goods_small_img']);
		foreach($pics as $k => $v){
			unlink(ROOT_PATH.$pics[$k]['pics_origin']);
			unlink(ROOT_PATH.$pics[$k]['pics_big']);
			unlink(ROOT_PATH.$pics[$k]['pics_mid']);
			unlink(ROOT_PATH.$pics[$k]['pics_sma']);
		}
		$model = $model -> delete($id);
		$res = $res -> where("goods_id = $id") -> delete();
		if($res !== false && $model !== false){
			//成功
			$attrs = M('GoodsAttr')->where("goods_id = $id")->delete();
			if($attrs){
				$this->success('数据删除成功!',U('Admin/Goods/goods_list'));
			}
		}else{
			//失败
			$this->error('数据删除失败!');
		}
	}

	//商品属性下拉获取值
	public function getattr(){
		$cate_id = I('post.cate_id',0,'intval');
		if($cate_id <= 0){
			//参数不合法
			$return = array(
				'code' => 10001,
				'msg' => '参数不合法'
			);
			$this->ajaxReturn($return);
		}
		//查询属性
		$attrs = M('Attribute')->where("cate_id = $cate_id")->select();
		$return = array(
			'code' => 10000,
			'msg' => 'success',
			'attrs' => $attrs
		);
		$this->ajaxReturn($return);
	}


}