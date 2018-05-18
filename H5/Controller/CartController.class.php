<?php
namespace H5\Controller;
use Think\Controller;
class CartController extends CommonController {

	//购物车列表
	public function index(){
		layout(false);
		$this -> display();
	}

	//结算列表
	public function flow2(){
		layout(false);
		$this -> display();
	}

	//ajax购物车列表
	public function ajax_index(){
		$uid = $_SESSION['userinfo']['id'];//获取用户id
		if(empty($uid)){//如果用户信息为空
			$code = 10002;
			$msg  = '请先登录';//去登录
		}else{
			//由于结算页面也是请求的这个方法，所以根据接收参数做判断，显示数据不同
			$ids = $_GET['ids'] ? $_GET['ids'] : 0;
			if(!empty($ids)){
				$where = "a.id in($ids) and ";
				$addresslist = M('Address')
						-> where("user_id = $uid")
						-> order('default_addr desc')
						-> select();
				$addresslist ? $addresslist : $this -> ajaxReturnData(0,'没有收货地址，去添加？！');
			}
			//购物车编号，购买数量，商品id,商品名称，商品缩略图，商品单价，商品所属店铺id,商铺名称
			$model = M('Cart');
			$field = 'a.id,a.number,a.add_time,b.goods_id,b.goods_name,b.goods_small_img,b.goods_price,b.shop_id,c.shop_name';
			$where .= "a.user_id = $uid";
			$cartlist = $model
					-> alias('a')
					-> field($field)
					-> where($where)
					-> join('zhouyuting_goods b on a.goods_id = b.goods_id')
					-> join('zhouyuting_shop c on b.shop_id = c.id')
					-> select();
			//如果当前用户的购物车为空，并且session中的add_time时间在今天的0点-24点之间
			$time   = date('Y-m-d',time());//格式化当前日期时间
			$time0  = strtotime($time);//当前日期0点的时间戳
			$time24 = strtotime($time) + 86309;//当前日期24点的时间戳
			if($_SESSION['add_time'] > $time0 && $_SESSION['add_time'] < $time24 && empty($cartlist)){
				$this -> ajaxReturnData(10001,'您超时未结算，购物车被清空啦！');
				//如果商品添加时间在今天24小时之内，并且购物车为空，则返回购物车被清空的消息
			}else{
				$cartlist ? $cartlist : $this -> ajaxReturnData(0,'购物车中没有数据，点击去购买？！');
				//如果不符合时间，则只返回购物车中没有数据
			}

			foreach($cartlist as $key => $value){
				$list[$value['shop_id']][] = $value;
			}
			$num = 0;
			foreach($list as $key => $value){
				foreach($value as $v => $k){
					$value[$v]['add_time'] = intval(strtotime($value[$v]['add_time']));
				}
				$newcartlist[$num] = $value;
				$num ++;
			}
			$newtime = M('Cart') -> where("user_id = $uid") -> max('add_time');//获取购物车中的最新商品的时间
			$newtime = intval(strtotime($newtime));
			if(!$cartlist){
				$code = 0;
				$msg  = '购物车中没有商品';
			}else{
				$code = 10000;
				$msg  = 'success';
			}

		}
		//设置返回数据格式
		$data = array(
				'code' => $code,
				'msg'  => $msg,
				'info' => compact('newcartlist','addresslist','newtime')
		);
		//返回数据
		$this->ajaxReturn($data);
	}


	//ajax普通商品添加购物车
	public function ajax_add(){
		$uid = session('userinfo.id') ? session('userinfo.id') : 1;//后期修改
		$data = I('post.');//接收数据

		$list = array(
				'user_id' => $uid,//用户id
				'goods_id' => $data['goods_id'],//商品id
				'goods_attr_ids' => $data['ids'],//商品属性id
		);
		foreach($list as $key => $value){
			empty($value) ? $this -> ajaxReturnData(0,'参数错误') : $value;
		}
		$model = M('Cart');
		$cart  = $model -> where($list) -> find();//查询购物车中的数据

		$list['number'] =  $data['goods_number'];//购买的商品数量
		$where = ['goods_id' => $list['goods_id']];//定义查询条件
		$number = M('Goods') -> where($where) -> getField('goods_number');//获取库存数量
		$num['goods_number'] = $number - $list['number'];//库存数量 - 购买商品数量
		M('Goods') -> where($where) -> save($num);//修改商品库存
		if(empty($cart)){
			//没有记录
			$res = $model -> add($list);//调用模型的add方法添加数据到数据表
			session('add_time',time());//将当前添加时间保存至session中
			$res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(10001,'添加失败！');
			//return $res ? true : false;
		}else{
			//有记录
			$list['id'] = $cart['id'];
			//添加数量
			$add = [
				'id' => $cart['id'],//获取购物车中的主键id
				'number' => $cart['number'] + $list['number'],
			];
			$res = $model -> save($add);//修改数据库中的数量
			session('add_time',time());//将当前添加时间保存至session中
			$res !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(10001,'保存失败！');
		}
	}

	//ajax活动商品加入购物车，直接创建订单
	public function ajax_actadd(){
		//var_dump(I('post.'));
		//array(5) {
		//["gid"]       => string(1) "1" 	  商品id 是否为活动商品，
		//["type"]      => string(1) "2"      类型 买涨还是买跌 2,预定 买涨 1，代售 买跌
		//["price"]     => string(6) "23.976" 商品价格
		//["num"]       => string(1) "1" 	  商品数量 是否超过限制数量
		//["ids"]       => string(3) "1,2"    商品的规格
		//["stopProfit"]=> string(3) "0.8"    止盈 百分比
		//["stopLoss"]  => string(3) "0.5"    止损 百分比
		//}
		$goods_id = I('post.gid');//商品id
		$number   = I('post.num');//商品购买数量

		//查询用户购买订单是否超限
		//先设置活动商品每人每天限购10件
		$_SESSION['userinfo']
		? $user_id = $_SESSION['userinfo']['id'] : $this -> ajaxReturnData(10002,'请登录后购买') ;//如果用户未登录

		$cartcount = M('Cart')
				-> where("user_id = $user_id and goods_id = $goods_id ")
				-> sum('number');//购物车中当前用户购买的现在购买商品的总数

		$stime = strtotime('2017-11-01 00:00:00');//活动开始时间
		$etime = strtotime('2017-11-12 00:00:00');//活动结束时间

		$where = "a.user_id = $user_id";//当前用户
		$where .= " and a.create_time between $stime and $etime";//订单创建时间在活动时间之内
		$where .= " and b.goods_id = $goods_id";//并且订单中商品是现在所购买的商品

		$ordercount = M('Order')
				-> alias('a')
				-> where($where)
				-> join('left join zhouyuting_order_goods b on a.id = b.order_id')
				-> sum('number');

		$count = 10;//查询商品限购的数量，假设为10
		($number > $count - $cartcount) || ($number > $count - $ordercount)
		? $this -> ajaxReturnData(10001,'商品限购'.$count.'件，您已超过数量！') : 1;
		//购买数量 > （限购总数 - 购物车商品数量） 返回错误

		$goods = D('Goods') -> where("goods_id = $goods_id") -> find();
		$number > $goods['goods_number']
		? $this -> ajaxReturnData(10001,'库存不足！') : 1;//购买数量大于库存数 返回错误

		//获取当前时间的商品小数价格
		$data = getData();
		//如果传递的商品整数部分 和 数据库中商品整数部分一致，则获取传递的商品总价 否则用实际商品价格拼接新获取的小数部分
		I('post.price','','intval') == $goods['goods_price']
		? $price = I('post.price') : $price = intval($goods['goods_price']) + substr($data['buy'],1);

		//买涨  t1=小数位 + 止盈百分比/5   t2=小数位 - （1 - 止损的百分比）/5
		//买跌  t1=小数位 + （1 - 止损的百分比）/5  t2=小数位 - 止盈的百分比/5
		//最大值=（t1>=t2）? t1 : t2;
		//最小值=（t1<=t2）? t1 : t2;

		$strSuffix = '0'.substr(I('post.price'),strpos(I('post.price','','float'),'.'));
		if (I('post.type','','intval') == 2){
			$t1 = $strSuffix + I('post.stopProfit') / 5;
			$t2 = $strSuffix - (1 - I('post.stopLoss')) / 5;
		}else{
			$t1 = $strSuffix +  (1 - I('post.stopLoss')) / 5;
			$t2 = $strSuffix - I('post.stopProfit') / 5;
		}

		$stop_max = ($t1 >= $t2) ? $t1 : $t2;
		$stop_min = ($t1 <= $t2) ? $t1 : $t2;
		$desc = 'type:'.I('post.type').',止盈价格:'.$stop_max.',止损价格:'.$stop_min.',止盈百分比:'.I('post.stopProfit').',止损百分比:'.I('post.stopLoss');//备注详细信息
		$order_sn = date("Ymd").time().rand(1000,9999);//设置长的订单编号
		$order_amount = $price * $number;//订单金额 = 商品单价*数量 + 邮费

		$data = array(
			'order_sn'      => $order_sn,//订单编号
			'order_amount'  => $order_amount,//订单金额
			'user_id'       => $_SESSION['userinfo']['id'],//下单用户id
			'create_time'   => time(),//订单创建时间
			'order_type'    => 2,//活动订单
			'order_desc'    => $desc,//订单备注
		);

		$res = D('Order') -> add($data);//添加活动商品
		$res ? $res : $this -> ajaxReturnData(10001,'购买活动商品失败！');

		$datagoods = array(
			'order_id'      => $res,//订单id
			'goods_id'      => $goods_id,//商品id
			'goods_price'   => $price,//购买时的商品单价
			'number'        => $number,//购买数量
			'goods_attr_ids'=> I('post.ids')//商品属性ids
		);

		$result = D('Order_goods') -> add($datagoods);//添加订单商品详情
		$result ? $result : $this -> ajaxReturnData(10001,'活动商品订单详情添加失败！');

		$user['id'] = $user_id;//用户id
		$balance = D('User') -> where($user) -> getField('balance');//用户余额
		$user['balance'] = $balance - $order_amount;//余额 - 订单金额
		$uresult = D('User') -> save($user);//扣除用户余额
		$uresult !== false ? $uresult : $this -> ajaxReturnData(10001,'扣除用户余额失败！');
		$endres = D('Order') -> where("id = $res") -> save(['order_status' => 1]);//修改订单状态
		$endres !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(10001,'修改订单状态失败！');



	}

	//ajax倒计时之后清空购物车，或者单个删除购物车商品
	public function ajax_delcart(){
		$ids = I('get.ids');
		$res = M('Cart') -> where(['id' => ['in',$ids]]) -> delete();
		$res ? $this -> ajaxReturnData(10000,'您超时未结算，购物车被清空啦！') : $this -> ajaxReturnData(10001,'购物车清空失败！');
	}

	//ajax结算页面
	public function ajax_flow2(){
		$uid = $_SESSION['userinfo']['id'];//获取用户id
		empty($uid) ? $this -> ajaxReturnData(10002,'请先登录') : true;//如果用户信息为空
		!IS_POST ? $this -> ajaxReturnData(0,'请求方式错误') : true;
		$ids = I('post.ids');//购物车id
		$addr_id = I('post.addr_id');//地址id
		$pay_type = I('post.pt');//购买方式
		$shipping_type = I('post.st');//配送方式
		$order_type = I('post.ot');//订单类型，暂时是普通订单
		(empty($ids) || empty($addr_id)) ? $this -> ajaxReturnData(0,'参数为空') : true;

		$fields = 'a.*,b.goods_price';
		$cart = M('Cart') -> alias('a') -> field($fields) -> where("id in ($ids)") -> join('zhouyuting_goods b on a.goods_id = b.goods_id') -> select();//查询购物车中的数据
		$address = M('Address') -> where("id = $addr_id") -> find();//查询选择的地址详情
		$order_sn = date("Ymd").time().rand(1000,9999);//设置长的订单编号
		$order_amount = 0;//初始化订单金额
		foreach($cart as $key => $value){
			$price = $value['number'] * $value['goods_price'];
			$order_amount += $price;//订单总金额  ,还需要商品邮费，后期要添加
		}
		$address_info = $address['consignee'].' '.$address['phone'].' '.$address['address'].' '.$address['zipcode'];
		//拼接地址详情
		//$str = date('H:i:s',time());
		//$string = date('H:i:s',(time()+60*30));


		//订单表中的字段
		$order = array(
				'order_sn' => $order_sn,//订单编号
				'order_amount' => $order_amount,//订单金额
				'user_id' => $uid,//下单用户id
				'address_info' => $address_info,//收货人地址详细信息
				'shipping_type' => $shipping_type,//配送方式 0圆通 1申通 2韵达 3中通 4顺丰  后期修改
				'pay_status' => 0,//支付状态 0未付款 1已付款
				'pay_type' => $pay_type,//支付方式 0银联 1微信 2支付宝 3余额 后期修改
				'create_time' => time(),//订单创建时间
				'order_type' => $order_type,//订单类型 0普通订单，1充值订单，2活动订单，3积分订单
		);

		//创建订单
		$res = M('Order') -> add($order);
		$res ? $res : $this -> ajaxReturnData(10001,'创建订单失败');

		$order_goods = array();
		if(count($cart) >= 1){
			foreach($cart as $key => $value){
				//订单和商品关联表中的字段
				$order_goods[] = array(
						'order_id' => $res,//订单id
						'goods_id' => $cart[$key]['goods_id'],//商品id
						'goods_price' => $cart[$key]['goods_price'],//商品单价
						'number' => $cart[$key]['number'],//购买数量
						'goods_attr_ids' => $cart[$key]['goods_attr_ids'],//商品属性ids
				);
			}
		}
		$result = D('Order_goods') -> addAll($order_goods);//添加订单明细
		$result ? $result : $this -> ajaxReturnData(10001,'订单商品明细添加失败！');

		//删除购物车中商品
		$where['id'] = ['in',$ids];
		$rescart = D('Cart') -> where($where) -> delete();
		$rescart ? $rescart : $this -> ajaxReturnData(10001,'删除购物车失败！');

		if($pay_type == 0){
			//0银联 扫码
			//$this -> ajaxReturnData(10010,'暂时没有银联支付，请选择其他！');
			$url = '/Pay/index/id/'.$res;
			$this -> ajaxReturnData(10020,'银联扫码支付',compact('url'));
		}elseif($pay_type == 1){
			//1微信 扫码
			$url = '/Pay/index/id/'.$res;
			$this -> ajaxReturnData(10020,'微信扫码支付',compact('url'));
		}elseif($pay_type == 2){
			//2支付宝 手机端账号支付
			$url = '/Pay/index/id/'.$res;
			$this -> ajaxReturnData(10010,'支付宝支付',compact('url'));

		}elseif($pay_type == 3){
			//3 余额付款
			$balance = M('User') -> where("id = $uid") -> getField('balance');//查询用户余额
			$user['balance'] = $balance - $order_amount;//用户余额扣除订单金额
			$resu = M('User') -> where("id = $uid") -> save($user);//保存扣除用户余额
			$resu !== false ? $resu : $this -> ajaxReturnData(10001,'扣除用户余额失败！');
			$order['pay_status'] = 1; //支付状态 0未付款 1已付款
			$order['order_status'] = 1;//0待付款，1,待发货
			$reso = D('Order') -> where("id = $res") -> save($order);
			$url = '/User/order/type/'.$order_type;
			$reso !== false ? $this -> ajaxReturnSuccess(compact('url')) : $this -> ajaxReturnData(10001,'修改订单状态失败');

		}elseif($pay_type == 4){
			//4支付宝 扫码
			$url = '/Pay/index/id/'.$res;
			$this -> ajaxReturnData(10020,'支付宝扫码支付',compact('url'));
		}


	}



}	