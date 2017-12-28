<?php
namespace Admin\Controller;
class OrderController extends BaseController{
	//首页显示
	public function index(){
		$this -> display();
	}

	//首页ajax请求数据
	public function lst(){
		$order = D('Order')
				->order('id desc')
				->select();
		foreach($order as $key => $value){
			$order[$key]['ct'] = date('Y-m-d H:i:s',$order[$key]['create_time']);
			$order[$key]['st'] = shipping_type($order[$key]['shipping_type']);
			$order[$key]['ps']    = pay_status($order[$key]['pay_status']);
			$order[$key]['pt']      = pay_type($order[$key]['pay_type']);
			$order[$key]['os']  = order_status($order[$key]['order_status']);
			$order[$key]['ot']    = order_type($order[$key]['order_type']);
		}
		$data = ['data' => $order];
		$this -> ajaxReturn($data);
	}

	//后台发货
	public function add_express(){
		$data = array(
			'id' => $_POST['oi'],
			'shipping_type' => $_POST['ei'],
			'express_sn'   => $_POST['es'],
			'order_status' => 2
		);
		$ei = D('Order') -> where(['id' => $data['id']]) -> getField('shipping_type');
		$data['shipping_type'] == $ei ? $data : $this ->ajaxReturnData(10001,'与用户选择的快递不一致，请重新选择！');
		$res = D('Order') -> save($data);dump($data);die;
		$res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(10001,'添加物流失败,请稍后重试！');

	}

	//主页关闭发货提醒
	public function clearn(){
		$msg = I('get.msg','','string');

		$f1=fopen('Public/deliver.txt','r');
		$tmp=tempnam('Public/tmp','tmp_');//建立临时文件
		$f2=fopen($tmp,'w');

		while(!feof($f1)){
			$line=fgets($f1);
			if (!(strpos($line, $msg) !== false)) fputs($f2,$line);
		}
		fclose($f1);
		fclose($f2);
		$res = rename($tmp,'Public/deliver.txt');
		$res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'清除消息失败！');
	}

	//订单详情
	public function detail(){
		$id = I('get.id','','intval');
		empty($id) ? $this -> ajaxReturnData(0,'参数错误') : true;
		//单据编号 订单状态 收货人姓名  收货人电话 收货地址 发货时间 总价 物流编号 备注  商品名称 商品价格 商品属性 购买数量
		//order_sn order_status address_info
		$data['order'] = D('Order')-> find($id);
		$data['goods'] = D('Order') -> alias('a')
				-> field('b.*,c.goods_name,c.goods_price')
				-> where(['a.id' => $id])
				-> join('zhouyuting_order_goods b on a.id = b.order_id')
				-> join('zhouyuting_goods c on b.goods_id = c.goods_id')
				-> select();
		foreach($data['goods'] as $key => $value){
			$data['goods'][$key]['goods_attr_ids'] = explode(',',$value['goods_attr_ids']);
			foreach($data['goods'][$key]['goods_attr_ids'] as $k => $v){
				$data['goods'][$key]['goods_attr_ids'][$k] = D('Goods_attr') -> where(['id' => $v ]) -> getField('attr_value');
			}
			$data['goods'][$key]['goods_attr_ids'] = implode(' X ',$data['goods'][$key]['goods_attr_ids']);
		}
		unset($key,$value);
		unset($k,$v);
		empty($data) ? $this -> ajaxReturnData(0,'没有数据') : $this -> ajaxReturnSuccess($data);
	}

	
}