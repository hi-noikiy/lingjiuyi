<?php
namespace Home\Model;
use Think\Model;
class CartModel extends Model{
	//将购物车数据保存到购物车
	public function addCart($goods_id,$number,$goods_attr_ids){
		//购物车数据保存分两种 cookie 和 数据表
		if(session('?userinfo')){
			//已登录,保存到数据表
			$user_id = session('userinfo.id');
			//查询数据表中是否已有当前的购物记录,有则修改购买数量,没有则新增记录
			////查询时要根据 user_id goods_id goods_attr_ids 来确定一条唯一的记录
			//组装数据
			$data = array(
				'user_id' => $user_id,
				'goods_id' => $goods_id,
				'goods_attr_ids' => $goods_attr_ids,
			);
			$cart = $this->where($data)->find();
			if(empty($cart)){
				//没有记录
				$data['number'] = $number;
				//调用模型的add方法添加数据到数据表
				$res = $this->add($data);
				return $res ? true : false;
			}else{
				//有记录
				$cart['number'] += $number;
				$res = $this->save($cart);
				return ($res !== false) ? true :false;
			}
			
		}else{
			//未登录,保存到 cookie
			$cart_data = cookie('cart') ? unserialize(cookie('cart')) : array();
			$key = $goods_id.'-'.$goods_attr_ids;
			//组装数据
			// $data = array(
			// 	$key => $number
			// );
			// 判断原来的cookie中有没有当前要添加的购物记录
			if(isset($cart_data[$key])){
				//已有对应的记录
				$cart_data[$key] += $number;
			}else{
				//没有对应的记录
				// 把当前的购物数据添加到原来的购物车数据数组中去	
				$cart_data[$key] = $number;
			}
			//序列化数组
			$str = serialize($cart_data);
			//保存数据到cookie
			cookie('cart',$str);
			return true;
		}
	}

	//获取购物车中所有数据
	public function getCarts(){
		if(session('?userinfo')){
			//已登录,查询数据表
			$user_id = session('userinfo.id');
			$data = $this->where("user_id = $user_id")->select();
		}else{
			//未登录,查询cookie
			$cart_data = cookie('cart') ? unserialize(cookie('cart')) : array();
			$data = array();
			foreach ($cart_data as $k => $v) {
				$temp = explode('-',$k);
				// $data['goods_id'] = $temp[0];
				// $data['goods_attr_ids'] = $temp[1];
				// $data['number'] = $value;
				$data[] =array(
					'goods_id' => $temp[0],
					'goods_attr_ids' => $temp[1],
					'number' => $v,
				);
			}
			unset($k,$v);
		}
		//根据购物车数据中的商品id,查询商品信息
		foreach ($data as $k => &$v) {
			//获取商品信息, 保存到$data 中
			$model = M('Goods');
			$goods = $model->find($v['goods_id']);
			$v['goods'] = $goods; 
			//获取属性信息
			$attr = M('Goods_attr')->alias('t1')->join("left join zhouyuting_attribute t2 on t1.attr_id=t2.attr_id")->where("t1.id in ({$v['goods_attr_ids']})")->select();
			$v['attr'] = $attr;
		}
		unset($k,$v);
		return $data;
	}

	//把cookie购物车数据迁移到数据库
	public function cookieTodb(){
		//从cookie中取出数据
		$cart_data = cookie('cart') ? unserialize(cookie('cart')) : array();
		if(empty($cart_data)){
			return true;
		}
		foreach($cart_data as $k => $v) {
			$temp = explode('-',$k);
			$goods_id = $temp[0];
			$goods_attr_ids = $temp[1];
			$number = $v;
			$this->addCart($goods_id,$number,$goods_attr_ids);
		}
		unset($k,$v);
		//删除原来的cookie购物车数据
		cookie('cart',null);
		return true;
	}

	//获取购物车中购买的总数量
	public function getNumber(){
		//先获取购物车中的所有记录
		$data = $this->getCarts();
		//如果不给初始值,total 为 null
		$total = 0;
		//遍历数组 累加每一条记录的数量
		foreach($data as $k => $v){
			$total += $v['number'];
		}
		//返回总数量
		return $total;
	}

	//修改购物车中某一条购物记录 的数量
	public function changeNumber($goods_id,$goods_attr_ids,$number){
		//获取指定的购物记录
		//判断登录状态
		if(session('?userinfo')){
			//已登录,修好数据库
			//获取指定的购物记录
			$user_id = session('userinfo.id');
			$where = array(
				'user_id' => $user_id,
				'goods_id' => $goods_id,
				'goods_attr_ids' => $goods_attr_ids,
			);
			$cart = $this->where($where)->find();

			$cart['number'] = $number;
			$res = $this->save($cart);
			return ($res !== false) ? true : false;
		}else{
			
			//未登录,修好cookie
			//获取购物记录
			$cart_data = cookie('cart') ? unserialize(cookie('cart')) : array();
			//拼接当前购物记录的 $key
			$key = $goods_id.'-'.$goods_attr_ids;
			$cart_data[$key] = $number;
			cookie('cart',serialize($cart_data));
			return true;
		}
	}

	//删除购物车中的一条记录
	public function delCart($goods_id,$goods_attr_ids){
		if(session('?userinfo')){
			$user_id = session('userinfo.id');
			$where = array(
				'user_id' => $user_id,
				'goods_id' => $goods_id,
				'goods_attr_ids' => $goods_attr_ids,
			);
			$res = $this->where($where)->delete();
			return ($res !== false) ? true : false;
		}else{
			$cart_data = cookie('cart') ? unserialize(cookie('cart')) : array();
			$key = $goods_id.'-'.$goods_attr_ids;
			// $cart_data[$key] = null;
			unset($cart_data[$key]);
			cookie('cart',serialize($cart_data));
			return true;
		}
	}
	
}