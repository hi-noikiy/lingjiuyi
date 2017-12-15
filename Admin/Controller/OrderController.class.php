<?php
namespace Admin\Controller;
class OrderController extends BaseController{
	public function index(){
		$this -> display();
	}

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

	
}