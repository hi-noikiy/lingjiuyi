<?php
namespace Admin\Controller;
use Think\Controller;
require_once './Application/Tools/Qiniu/autoload.php';
use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;
use Qiniu\Storage\BucketManager;

class GoodsController extends BaseController{

	//初始化
	public function __construct()
	{
		parent::__construct();
		$setting = C('UPLOAD_VIDEO_QINIU.driverConfig');
		$this -> V_AK   = $setting['accessKey'];
		$this -> V_SK   = $setting['secretKey'];
		$this -> V_DOM  = $setting['domain'];
		$this -> V_BUCK = $setting['bucket'];
	}

	//首页列表
	public function index(){
		if(IS_AJAX){
			//查询商品数据
			//实例化模型
			$model = D('Goods');
			$fields = 'a.goods_id,a.goods_name,a.goods_bigprice,a.goods_price,a.goods_number,a.goods_small_img,a.is_act,a.goods_big_img,a.is_normal,b.shop_name,c.cate_name';
			//使用select查询数据 加上limit条件
			$goods = $model -> alias('a') -> field($fields) -> join('zhouyuting_shop b on a.shop_id = b.id') -> join('zhouyuting_category c on a.cate_id = c.id') -> select();
			$data  = ['data' => $goods];
			//变量赋值
			$this ->ajaxReturn($data);
		}else{
			$this -> display();
		}

	}

	//添加商品
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
			$data['shop_id']           = $_POST['shop_id'];
			$data['cate_id']           = $_POST['cate_id'];
			$data['is_act']            = $_POST['act'];
			$data['attr_value']        = $_POST['attr_value'] ? $_POST['attr_value'] : '';
			$id = D('Goods') -> add($data);//添加商品信息
			$id ? true : $this -> error('添加商品数据失败！');

			$shop_cate_id = I('post.shop_cate_id') ? I('post.shop_cate_id') : 0;
			if(!empty($shop_cate_id)){
				//如果存在商铺分类主键
				$sCate = array(
					'shop_cate_id' => $shop_cate_id,
					'goods_id'     => $id,
				);
				$resShopCate = D('Shop_cate_goods') -> add($sCate);
				$resShopCate ? true : $this -> error('商铺分类添加失败！');
			}

			if(!empty($data['attr_value'])){
				//如果存在商品属性值
				$attr = [];
				$i = 0;
				foreach($_POST['attr_value'] as $k => $v){
					foreach($v as $key => $value){
						if(!empty($value)){
							//如果商品属性值不为空则添加属性
							$attr[$i]['goods_id']  = $id;
							$attr[$i]['attr_id']    = $k;
							$attr[$i]['attr_value'] = $value;
							$i++;
						}
					}
				}
				unset($k,$v);
				unset($key,$value);
				$attrs = D('Goods_attr') -> addAll($attr);//添加商品属性
				$attrs ? true : $this -> error('添加商品属性失败！');
			}

			if(!empty($_FILES['goods_small_img']) && $_FILES['goods_small_img']['error'] == 0){
				//如果存在商品logo
				$str1  = 'imageView2/0/q/75|watermark/2/text/bGluZ2ppdXlp/font/dmVyZGFuYQ==/fontsize/';
				$str2 = '/fill/IzMyQjJFNA==/dissolve/100/gravity/SouthEast/dx/10/dy/10|imageslim';//水印样式
				$file['goods_small_img'] = $_FILES['goods_small_img'];
				//添加商品表大图和生成缩略图
				//进行文件上传,使用七牛云
				$setting = C('UPLOAD_QINIU');
				$Upload  = new \Think\Upload($setting);
				$info    = $Upload -> Upload($file);
				$info ? true : $this -> error('上传图片失败！');//判断是否上传成功
				$img['goods_big_img']   = $info['goods_small_img']['url'].'?imageMogr2/thumbnail/350x350>|'.$str1.'250'.$str2;//大图
				$img['goods_small_img'] = $info['goods_small_img']['url'].'?imageMogr2/thumbnail/150x150>|'.$str1.'150'.$str2;//缩略图
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
							$pics[$k]['pics_origin'] = $info[$k]['url'].'?'.$str1.'250'.$str2;//原图
							$pics[$k]['pics_big']    =   $info[$k]['url'].'?imageMogr2/thumbnail/800x800|'.$str1.'250'.$str2;//大图
							$pics[$k]['pics_mid']    =   $info[$k]['url'].'?imageMogr2/thumbnail/350x350|'.$str1.'250'.$str2;//中图
							$pics[$k]['pics_sma']    =   $info[$k]['url'].'?imageMogr2/thumbnail/150x150|'.$str1.'150'.$str2;//小图
						}
						$res = D('Goodspics') -> addAll($pics);
						empty($res) ? $this -> error('添加多个图片失败！') : $this -> success('商品信息添加成功');
					}else{
						$info = $Upload -> Upload($file);//单文佳上传
						$pics['goods_id']    = $id;
						$pics['pics_origin'] = $info['url'].'?'.$str1.'250'.$str2;//原图
						$pics['pics_big']    =   $info['url'].'?imageMogr2/thumbnail/800x800|'.$str1.'250'.$str2;//大图
						$pics['pics_mid']    =   $info['url'].'?imageMogr2/thumbnail/350x350|'.$str1.'250'.$str2;//中图
						$pics['pics_sma']    =   $info['url'].'?imageMogr2/thumbnail/150x150|'.$str1.'150'.$str2;//小图
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
			$shops = D('Shop') -> field('id,shop_name') -> select();
			$this -> assign('cates',$cates);
			$this -> assign('shops',$shops);
			$this -> display();
		}

	}

	//修改商品
	public function edit(){
		$id = I('get.id','','intval') ? I('get.id','','intval') : I('post.id','','intval');//商品id
		empty($id) ? $this -> ajaxReturnData(0,'参数错误') : true;
		if(IS_POST){
			//商品基本信息 goods
			//商品属性管理 goods_attr
			//商品相册 goodspics
			//如果有商品id，则是修改商品信息

			$data['pics_origin']     = I('post.pics')     ? I('post.pics')  : false;//如果上传的是商品相册，则保存相册相关参数
			$data['min_video']       = I('post.video')    ? I('post.video') : false;
			$data['goods_introduce'] = I('post.goods_introduce') ? I('post.goods_introduce') : false;
			$data['goods_id']        = I('post.goods_id') ? I('post.goods_id') : false;
			$data['is_normal']       = I('post.is_normal','','intval') || I('post.is_normal','','intval') == 0 ? I('post.is_normal','','intval') : false;

			if($data['pics_origin']){
				//如果存在相册，添加相册图片
				$str1  = 'imageView2/0/q/75|watermark/2/text/bGluZ2ppdXlp/font/dmVyZGFuYQ==/fontsize/';
				$str2  = '/fill/IzMyQjJFNA==/dissolve/100/gravity/SouthEast/dx/10/dy/10|imageslim';//水印样式
				$img['goods_id']      = I('post.id','','intval');
				$img['pics_origin']   = $data['pics_origin'].'?'.$str1.'250'.$str2;//大图; //原图
				$img['pics_big']      = $data['pics_origin'].'?imageMogr2/thumbnail/800x800>|'.$str1.'250'.$str2;//大图
				$img['pics_mid']      = $data['pics_origin'].'?imageMogr2/thumbnail/350x350>|'.$str1.'250'.$str2;//大图
				$img['pics_sma']      = $data['pics_origin'].'?imageMogr2/thumbnail/150x150>|'.$str1.'150'.$str2;//缩略图

			}elseif($data['min_video']){
				//删除原来的视频文件
				$id        = I('post.id','','intval');
				$string    = D('Goods') -> where(['goods_id' => $id]) -> getField('min_video');
				if(!empty($string)){
					$key       = substr($string,strrpos($string,'/') + 1);
					$bucket    = $this -> V_BUCK;//资源所在空间
					$auth      = new Auth($this -> V_AK, $this -> V_SK);
					$config    = new \Qiniu\Config();
					$bucketManager = new BucketManager($auth, $config);
					$err = $bucketManager -> delete($bucket, $key);
					$err ? $this -> ajaxReturnData(0,'删除源文件失败') : true;
				}
				//添加视频
				$data['goods_id']          = I('post.id','','intval');
				$data['min_video_logo']    = $data['min_video'].'?vframe/jpg/offset/0';
				$data['isset_transcoding'] = 0;
				$res = D('Goods') -> save($data);
				$res == false ? $this -> ajaxReturnData(0,'添加视频失败') : $this -> ajaxReturnData(10000,'添加视频成功！');
			}elseif($data['goods_introduce']){
				$data['goods_id'] = I('post.id','','intval');
				$data['goods_introduce'] = remove_xss($data['goods_introduce']);
				$res = D('Goods') -> save($data);
				$res !== false ? $this -> success('修改成功！') : $this -> success('修改失败！');
			}elseif($data['goods_id']){
				//添加商品详情
				strlen(I('post.goods_name')) > 60 ? $this -> ajaxReturnData(0,'商品名称不能超过60个字符！') : true;

				$data['goods_name']       = I('post.goods_name');
				$data['goods_bigprice']   = I('post.goods_bigprice');
				$data['goods_price']      = I('post.goods_price');
				$data['goods_number']     = I('post.goods_number','','intval');
				$data['goods_smallprice'] = I('post.goods_smallprice');
				$data['is_act']           = I('post.act','','intval');
				$data['cate_id']          = I('post.cate_id','','intval');
				$data['is_normal']          = I('post.is_normal','','intval');
				empty($data['cate_id']) ? $this -> error('分类id选择错误！') : true;

				$res = D('Goods') -> save($data);
				$res !== false ? true : $this -> error('修改商品详情错误！');
				$attr_values = I('post.attr_values');

				foreach($attr_values as $k => $v){
					//$k 就是attr_id值
					foreach($v as $attr){
						//$attr 就是attr_value 值
						$attr_data[] = array(
							//上面添加商品时返回值 就是添加成功的主键id
								'goods_id' => $data['goods_id'],
								'attr_id' => $k,
								'attr_value' => $attr
						);
					}
				}
				//先删除商品原来的属性
				D('Goods_attr') -> where(['goods_id' => $data['goods_id']])->delete();
				//多条属性数据的批量添加操作
				$attr_res = D('Goods_attr') -> addAll($attr_data);
				$attr_res ? $this -> success('修改商品信息成功！')  : $this -> error('修改商品属性错误！') ;


			}elseif($data['is_normal'] === 0 || $data['is_normal'] === 1){
				$data['goods_id'] = $id;
				$res = D('Goods') -> save($data);
				$res != false ? $this -> ajaxReturnData(10000) : $this -> ajaxReturnData(0,'修改失败');
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

			$cates = D('Category') -> where("pid = 0") -> select();

			$this -> assign('goods',$goods);
			$this -> assign('attrs',$attrs);
			$this -> assign('new_gattr',$new_gattr);
			$this -> assign('gpics',$gpics);
			$this -> assign('cates',$cates);
			$this -> display();

		}
	}

	//显示销售量
	public function show_sales(){
		$id        =  I('get.id','','intval')     ? I('get.id','','intval')   : $this -> ajaxReturnData(0,'参数错误');
		$year      =  I('get.year','','intval')   ? I('get.year','','intval') : '';
		$mon       =  I('get.mon','','intval')    ? I('get.mon','','intval')  : '';
		$min_num   = I('get.min_num','','intval') ? I('get.min_num','','intval') : '';
		$max_num   = I('get.max_num','','intval') ? I('get.max_num','','intval') : '';
		$min_price = I('get.min_price','','intval') ? I('get.min_price','','intval') : '';
		$max_price = I('get.max_price','','intval') ? I('get.max_price','','intval') : '';
		if(!empty($mon)){
			$mon >= 10 ? $year = $year.'-'.$mon : $year = $year.'-0'.$mon;
		}
		if(empty($min_num) && !empty($max_num)){
			$where['a.sale_num'] = ['LT',$max_num];
		}elseif(!empty($min_num) && empty($max_num)){
			$where['a.sale_num'] = ['GT',$min_num];
		}elseif(!empty($min_num) && !empty($max_num)){
			$where['a.sale_num'] = ['BETWEEN',[$min_num,$max_num]];
		}
		if(empty($min_price) && !empty($max_price)){
			$where['a.sale_money'] = ['LT',$max_price];
		}elseif(!empty($min_price) && empty($max_price)){
			$where['a.sale_money'] = ['GT',$min_price];
		}elseif(!empty($min_price) && !empty($max_price)){
			$where['a.sale_money'] = ['BETWEEN',[$min_price,$max_price]];
		}
		$where['a.goods_id'] = $id;
		$where['a.add_time'] = ['LIKE',"$year%"];
		$sales = D('Saleonline') -> alias('a') -> field('a.*,b.goods_smallprice') -> where($where) -> join('zhouyuting_goods b on a.goods_id = b.goods_id') -> select();
		$data = [];
		foreach($sales as $key => $value){
			$data['time'][]  = substr($value['add_time'],0,10);//时间
			$data['num'][]   = $value['sale_num'];//销售量
			$data['money'][] = $value['sale_money'];//销售金额
			$data['price'][] = $value['sale_num'] * $value['goods_smallprice'];//进价
			$data['pure'][]  = $value['sale_money'] - ($value['sale_num'] * $value['goods_smallprice']);//纯利润 = 销售金额 - (销售量 * 商品进价)
		}
		empty($sales) ? $this -> ajaxReturnData(0,'没有数据') : $this -> ajaxReturnSuccess($data);


	}


	//视频上传之后添加水印或者转码
	public function handle_video(){

		//对已经上传到七牛的视频发起异步转码操作
		$bucket    = $this -> V_BUCK;//资源所在空间
		$auth      = new Auth($this -> V_AK, $this -> V_SK);
		$gid       = I('post.id');
		$string    = D('Goods') -> where(['goods_id' => $gid]) -> getField('min_video');
		$key       = substr($string,strrpos($string,'/') + 1);//待处理的源文件
		$type      = I('post.type');//处理的类型
		$pipeline  = 'my_video';//资源处理队列
		$notifyUrl = 'http://admin.lingjiuyi.cn/Public/notify';//处理结果通知地址
		$config    = new \Qiniu\Config();
		$pfop      = new PersistentFop($auth, $config);
		$name      = date('Y-m-d_H:i:s')."_".time().".mp4";
		//$wmImg     = base64_urlSafeEncode('http://www.lingjiuyi.cn/index.png');//暂时不用图片
		$wmText    = base64_urlSafeEncode('www.lingjiuyi.cn');
		$wmFontColor = base64_urlSafeEncode('#ffffff');
		$saveas    = base64_urlSafeEncode($bucket . ":".$name);
		$data['isset_transcoding'] = 1;
		$mp4       = '/mp4/ab/128k/ar/44100/acodec/libfaac';
		$fops = "avthumb".$mp4."/wmText/".$wmText."/wmFontColor/".$wmFontColor."/wmFontSize/20|saveas/".$saveas;//待处理的pfop操作
		list($id, $err) = $pfop->execute($bucket, $key, $fops, $pipeline, $notifyUrl);
		if ($err != null) {
			$this -> ajaxReturnData(0,'视频处理失败！',$err);
		} else {
			//echo "PersistentFop Id: $id\n";
		}
		//查询转码的进度和状态
		list($ret, $err) = $pfop->status($id);
		if ($err != null) {
			$this -> ajaxReturnData(0,'视频处理失败！',$err);
		} else {
			//var_dump($ret);
			$name = 'http://'.$this -> V_DOM.'/'.$name;
			$data['goods_id']       = $gid;
			$data['min_video']      = $name;
			$data['min_video_logo'] = $name.'?vframe/jpg/offset/0';
			$res = D('Goods') -> save($data);
			$bucketManager = new BucketManager($auth, $config);
			$err = $bucketManager->delete($bucket, $key);
			$err ? $this -> ajaxReturnData(0,'删除源文件失败') : true;
			$res !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'视频处理失败');
		}

	}

	//删除相册
	public function del(){
		$id   = I('post.id','','intval') ? I('post.id','','intval') : false;
		$goods_id = I('post.goods_id','','intval') ? I('post.goods_id','','intval') : false;
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

	//获取商铺分类
	public function getcate(){
		I('get.shop_id','','intval') ? $shop_id = I('get.shop_id','','intval') : $this -> ajaxReturnData(0,'参数错误');
		$shop_cate = D('Shop_cate') -> where("`shop_id` = $shop_id") -> select();
		empty($shop_cate) ? $this -> ajaxReturnData(0,'没有商铺分类') : $this -> ajaxReturnSuccess($shop_cate);
	}


}