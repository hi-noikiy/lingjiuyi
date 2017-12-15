<?php
namespace Home\Controller;
use Think\Controller;
class OrderController extends CommonController{
	//订单列表
	public function order_list(){
		if(!session('userinfo')){
			$this->redirect('Home/User/login');
		}
		$user_id = session('userinfo.id');
		$order= D('Order')->alias('t1')->field('t1.*,t2.goods_small_img,t2.goods_name,t2.goods_price,t3.number')->join("left join zhouyuting_order_goods t3 on t1.id=t3.order_id")->join("left join zhouyuting_goods t2 on t2.goods_id=t3.goods_id")->where("t1.user_id = $user_id")->order('create_time desc')->select();
		// dump($order);
		// $new_order = array();
		// foreach($order as $k => $v){
		// }
		// dump($new_order);die;
		$this->assign('order',$order);
		$this->display();
	}

	//查看物流
	public function logistics(){
		if(!session('userinfo')){
			$this->redirect('Home/User/login');
		}
		//接收订单表中的快递类型
		$shipping_type = I('get.shipping_type');
		//判断快递类型
		if($shipping_type == 0){
			$type = 'yuantong';
		}elseif($shipping_type == 1){
			$type = 'shentong';
		}elseif($shipping_type == 2){
			$type = 'yunda';
		}elseif($shipping_type == 3){
			$type = 'zhongtong';
		}else{
			$type = 'shunfeng';
		}
		//接收订单id
		$id = I('get.id');
		//查询商品属性信息
		$goods = D('Order_goods')->alias('t1')->field('t2.goods_small_img')->join("left join tpshop_goods t2 on t1.goods_id=t2.id")->where("t1.order_id = {$id}")->find();
		// dump($goods);die;
		//查询订单id对应的物流信息的编号
		$postid_arr = D('Order')->field('logistics_id')->find($id);
		$postid = $postid_arr['logistics_id'];
		// dump($type);dump($postid);die;
		//定义发送的接口url地址
		$url = "https://www.kuaidi100.com/query?type={$type}&postid={$postid}";
		//发送请求
		$res = curl_request($url,false,array(),true);
		//解析返回结果
		if(!$res){
			$this->error('请求错误');
		}
		$res_arr = json_decode($res,true);
		// dump($res_arr);
		$data = $res_arr['data'];
		// dump($data);
		$this->assign('goods',$goods);
		$this->assign('res_arr',$res_arr);
		$this->assign('data',$data);
		$this->display();
	}

}