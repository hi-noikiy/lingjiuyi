<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
class CartController extends CommonController{

	//加入购物车
    public function add(){
        $this -> check_login();
        $model = D('Cart');
        $data['user_id']  = session('userinfo.id');
        $data['goods_id'] = I('post.id','','intval');
        $data['number']   = I('post.num','','intval');
        $data['add_time'] = time();
        $where = ['user_id' => $data['user_id'],'goods_id' => $data['goods_id']];
        $num = $model -> where($where) -> getField('number');
        if($num !== null){
            $add['number'] = $data['number'] + $num;
            $res = $model -> where($where) -> save($add);
        }else{
            $res = $model  -> add($data);
        }
        $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'添加购物车失败！');
    }

	//购物车列表
	public function cart_list(){
		//调用Cart模型中的getCarts方法
		$carts = D('Cart') -> getCarts();
		$this->assign('carts',$carts);
		$this->display();
	}

	//用于ajax请求 获取总购买数量
	public function ajaxgetnumber(){
		//调用模型的getNumber方法 获取数量
		$total = D('Cart')->getNumber();
		$return = array(
			'code' => '10000',
			'msg' => 'success',
			'total' => $total
		);
		$this->ajaxReturn($return);
	}

	//用于修改ajax指定记录 的数量
	public function changenumber(){
		$data = I('POST.');
		$res = D('Cart')->changeNumber($data['goods_id'],$data['goods_attr_ids'],$data['number']);
		if($res){
			$total = D('Cart')->getNumber();
			$return = array(
				'code' => 10000,
				'msg' => 'success',
				'total' => $total
			);
			$this->ajaxReturn($return);
		}else{
			$return = array(
				'code' => 10001,
				'msg' => '失败'
			);
			$this->ajaxReturn($return);
		}
	}

	//用于ajax删除指定的购物记录
	public function delcart(){
		$data = I('post.');
		$res = D('Cart')->delCart($data['goods_id'],$data['goods_attr_ids']);
		if($res){
			//删除成功 重新获取购物车中的总购物数
			$total = D('Cart')->getNumber();
			$return = array(
				'code' => 10000,
				'msg' => 'success',
				'total' => $total
			);
			$this->ajaxReturn($return);
		}else{
			$return = array(
				'code' => 10001,
				'msg' => '失败'
			);
			$this->ajaxReturn($return);
		}
	}

    //结算页面
    public function flow2(){
        //判断用户是否登录
        if(!session('userinfo')){
            //未登录,把登录后要返回的页面设置到session,返回到购物车列表
            session('login_back_url',U('Home/Cart/cart_list'));
            //跳转到登录页面
            $this->redirect('Home/User/login');
        }
        //获取收货人的信息
        $user_id = session('userinfo.id');
        $address = D('Address')->where("user_id = $user_id")->select();
        //dump($address);die;
        $this->assign('address',$address);
        
        //获取商品购物车数据信息(商品属性表,商品属性关联表)
        $ids = I('get.ids');
        //dump($ids);die;
        //检查$IDS 合法性 检查IDS中是否是纯数字组成的字符串
        $id_arr = explode(',',$ids);
        foreach($id_arr as $k => $v){
            if(!is_numeric($v)){
                $this->error('参数不合法',U('Home/Cart/cart_list'));
            }
        }
        //查询zhouyuting_cart,zhouyuting_goods
        $data = D('Cart')->alias('t1')->field('t1.*,t2.goods_name,t2.goods_small_img,t2.goods_price')->join("left join zhouyuting_goods t2 on t1.goods_id = t2.goods_id")->where("t1.id in($ids) and user_id=$user_id")->select();
        //变量查询每一行记录的属性信息
        $total_amount = 0; //计算总金额
        foreach($data as $k => &$v){
            $attr = D('GoodsAttr')->alias('t3')->join('left join zhouyuting_attribute t4 on t3.attr_id = t4.attr_id')->where("id in ({$v['goods_attr_ids']})")->select();
            $v['attr'] = $attr;
            //计算zongjine
            $total_amount += $v['number'] * $v['goods_price'];         
        }
        $this->assign('data',$data);
        $this->assign('total_amount',$total_amount);
        $this->display();
    }
    
    //提交订单(生成订单)
    public function createorder(){
        if(IS_POST){
            //接收数据
            $data = I('post.');
            //dump($data);die;
            //array(4) { ["address_id"] => string(1) "1"    ["shipping_type"] => string(1) "0"     ["pay_type"] => string(1) "0"       ["ids"] => string(5) "15,16"  }
            //组装订单信息数组,添加到订单表 zhouyuting_order
            $order_sn = time().rand(10000,99999);   //生成一个数字的订单编号
            $user_id = session('userinfo.id'); //用户id
            $order_amount = 0;  //计算订单金额
           //根据ids字段查询 购物车表及商品表  zhouyuting_goods , zhouyuting_cart
           $cart_data = D('Cart')->alias('t1')->field('t1.*,t2.goods_price')->join("left join zhouyuting_goods t2 on t1.goods_id = t2.goods_id")->where("t1.id in ({$data['ids']}) and t1.user_id = $user_id")->select();
            //dump($cart_data);die
            /*
            array(2) {
                [0] => array(6) {   ["id"] => string(2) "15"        ["user_id"] => string(1) "2"        ["goods_id"] => string(2) "42"
                     ["goods_attr_ids"] => string(7) "103,107"    ["number"] => string(1) "1"      ["goods_price"] => string(6) "129.00"
                }
                [1] => array(6) {  ["id"] => string(2) "16"          ["user_id"] => string(1) "2"        ["goods_id"] => string(2) "42"
                     ["goods_attr_ids"] => string(7) "100,107"    ["number"] => string(1) "2"        ["goods_price"] => string(6) "129.00" }
            }
            */
            //遍历查询到的购物数据,计算总金额
            foreach ($cart_data as $k => $v){
                $order_amount += $v['number'] * $v['goods_price'];
            }
            
            //组装订单数组
            $order_data = array(
                'order_sn' => $order_sn,
                'order_amount' => $order_amount,
                'user_id' => $user_id,
                'address_id' => $data['address_id'],
                'shipping_type' => $data['shipping_type'],
                'pay_type' => $data['pay_type'],
                'create_time' => time(),
            );
            //dump($order_data);die;
            $order_id = D('Order')->add($order_data);
            if($order_id == false){
                $this->error('生成订单失败',U('Home/Cart/cart_list'));
            }
            
            //添加数据到订单商品表  zhouyuting_order_goods
            //遍历购物数组,一条提交数据都需要添加到订单商品表
            $order_goods = array();
            foreach($cart_data as $k => $v){
                $row = array(
                    'order_id' => $order_id,
                    'goods_id' => $v['goods_id'],
                    'goods_price' => $v['goods_price'],
                    'goods_attr_ids' => $v['goods_attr_ids'],
                    'number' => $v['number'],
                );
                $order_goods[] = $row;
            }
            //dump($order_goods);die;
            //批量添加
            D('OrderGoods')->addAll($order_goods);
            //删除购物车表中的数据
            D('Cart')->where("id in ({$data['ids']}) and user_id = $user_id")->delete();
            // dump($data['pay_type']);die;
            // $pay_type=$data['pay_type'];
            // if(pay_type == 2){
                // 组装一个form表单并实现自动提交的代码
                $str = "<form action='/Application/Tools/alipay/alipayapi.php' id='alipay_form' method='post' style='display:none;'>
                <input type='text' name='WIDout_trade_no' id='out_trade_no' value='{$order_sn}'>
                <input type='text' name='WIDsubject' value='php58'>
                <input type='text' name='WIDtotal_fee' value='{$order_amount}'>
                <input type='text' name='WIDbody' value='一手交钱一手交货'>
                </form>
                <script>document.getElementById('alipay_form').submit();</script>";
                echo $str;
            // }else{
                // $total_fee = $order_amount;
                //支付流程模拟支付成功跳转到支付页面
                // $this->redirect('Home/Cart/flow3/total_fee/'.$total_fee);
            // }
            
            
            

        }
    }
    
    //支付成功页面
    public function flow3(){
        $this->display();
    }

    //在订单页面添加新地址
    public function create_address(){
        $data = I('post.');
        //dump($data);die;
        $res = D('Address')->add($data);
        if($res){
            $this->success('添加新地址成功!',U('Home/Cart/flow2/ids/'.$data['ids']));
        }else{
            $this->error('添加新地址失败!');
        }
    }

    //删除地址
    public function del_address(){
        $id = I('get.id');
        //dump($id);
        $res = D('Address')->delete($id);
        if($res){
            $ids = I('get.ids');
            if($ids){
                $this->success('删除地址成功!',U('Home/Cart/flow2/ids/'.$ids));
            }else{
                $this->success('删除地址成功!',U('Home/Cart/address_list'));
            }
            
        }else{
            $this->error('删除地址失败!');
        }
    }

    //在个人中心展示地址
    public function address_list(){
        //判断用户是否登录
        if(!session('userinfo')){
            //跳转到登录页面
            $this->redirect('Home/User/login');
        }
        if(IS_POST){
            $data = I('post.');
            $data['user_id'] = session('userinfo.id');
            //dump($data);die;
            $res = D('Address')->add($data);
            if($res){
                $this->success('添加新地址成功!',U('Home/Cart/address_list'));
            }else{
                $this->error('添加新地址失败!');
            }
        }else{
            //获取收货人的信息
            $user_id = session('userinfo.id');
            $address = D('Address')->where("user_id = $user_id")->select();
            //dump($address);die;
            $this->assign('address',$address);
            $this->display();
        }
    }

    //设置默认地址,修改其他地址
    public function setdefault_addr(){
      //接收传递的参数
      $user_id = session('userinfo.id');
      $id = I('get.id');
      //dump($id);
      $old_user = D('Address')->where("default_addr=1 and user_id={$user_id}")->find();
      //dump($old_user);
      $old_user['default_addr'] = 0;
      D('Address')->save($old_user);
      $data = array('default_addr' => 1, );
      $res = D('Address')->where("user_id={$user_id} and id={$id}")->save($data);
      if($res){
        $this->redirect('Home/Cart/address_list');
      }else{
        $this->error('修改默认地址失败');
      }
    }

    public function information(){
        //判断用户是否登录
        if(!session('userinfo')){
            //跳转到登录页面
            $this->redirect('Home/User/login');
        }
        if(IS_POST){
            //修改用户信息
            $data = I('post.');
            // dump($data);
            $res = D('User')->save($data);
            if($res){
                $this->success('个人资料修改成功!',U('Home/Cart/information'));
            }else{
                $this->error('个人资料修改失败!');
            }
        }else{
            //展示页面
            $user_id = session('userinfo.id');
            $user = D('User')->where("id = $user_id")->select();
            // dump($user);
            $this->assign('user',$user);
            $this->display();
        }
        
    }
   


}