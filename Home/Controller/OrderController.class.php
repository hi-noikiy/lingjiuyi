<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
class OrderController extends CommonController
{

    //创建订单
    public function create_order(){
        //配送方式、支付方式、地址编号、购物车编号,s_type,p_type,address_id,cart_id
        !IS_POST ? $this -> ajaxReturnData(0,'请求方式错误') : true;
        $this -> check_login();
        $shipping_type = I('post.s_type');//参数可能为0
        I('post.p_type','','intval')     ? $pay_type = I('post.p_type','','intval')       : $this -> ajaxReturnData(0,'支付方式参数错误');//参数可能为0，但是目前没有银联
        I('post.address_id','','intval') ? $address_id = I('post.address_id','','intval') : $this -> ajaxReturnData(0,'地址编号参数错误');
        I('post.cart_id','','string')    ? $cart_id = I('post.cart_id','','string')       : $this -> ajaxReturnData(0,'购物车编号参数错误');
        //后期可以查询当前购物车商品是否属于当前登录用户
        $fields = 'a.*,b.goods_price';
        $cart = M('Cart') -> alias('a') -> field($fields) -> where("id in ($cart_id)") -> join('zhouyuting_goods b on a.goods_id = b.goods_id') -> select();//查询购物车中的数据
        $address = M('Address') -> where("id = $address_id") -> find();//查询选择的地址详情
        $order_sn = date("Ymd").time().rand(1000,9999);//设置长的订单编号
        $order_amount = 0;//初始化订单金额
        foreach($cart as $key => $value){
            $price = $value['number'] * $value['goods_price'];
            $order_amount += $price;//订单总金额  ,还需要商品邮费，后期要添加
        }
        $address_info = $address['consignee'].' '.$address['phone'].' '.$address['address'].' '.$address['zipcode'];//拼接地址详情

        //订单表中的字段
        $order = array(
            'order_sn'     => $order_sn,//订单编号
            'order_amount' => $order_amount,//订单金额
            'user_id'      => session('userinfo.id'),//下单用户id
            'address_info' => $address_info,//收货人地址详细信息
            'shipping_type'=> $shipping_type,//配送方式 0圆通 1申通 2韵达 3中通 4顺丰  后期修改
            'pay_status'   => 0,//支付状态 0未付款 1已付款
            'pay_type'     => $pay_type,//支付方式 0银联 1微信 2支付宝 3余额 后期修改
            'create_time'  => time(),//订单创建时间
            'order_type'   => 0,//订单类型 0普通订单，1充值订单，2活动订单，3积分订单
        );

        //创建订单
        $res = M('Order') -> add($order);
        $res ? true : $this -> ajaxReturnData(0,'创建订单失败');

        $order_goods = array();
        if(count($cart) >= 1){
            foreach($cart as $key => $value){
                //订单和商品关联表中的字段
                $order_goods[] = array(
                    'order_id'    => $res,//订单id
                    'goods_id'    => $cart[$key]['goods_id'],//商品id
                    'goods_price' => $cart[$key]['goods_price'],//商品单价
                    'number'      => $cart[$key]['number'],//购买数量
                    'goods_attr_ids' => $cart[$key]['goods_attr_ids'],//商品属性ids
                );
            }
        }
        $result = D('Order_goods') -> addAll($order_goods);//添加订单明细
        $result ? true : $this -> ajaxReturnData(0,'订单商品明细添加失败！');

        //删除购物车中商品
        $where['id'] = ['in',$cart_id];
        $rescart = D('Cart') -> where($where) -> delete();
        $rescart ? true : $this -> ajaxReturnData(0,'删除购物车失败！');

        if($pay_type == 0){
            //0银联
            $this -> ajaxReturnData(10010,'暂时没有银联支付，请选择其他！');
        }elseif($pay_type == 1){
            //1微信
            $this -> ajaxReturnData(10020,'微信支付',$res);
        }elseif($pay_type == 2){
            //2支付宝
            $this -> ajaxReturnData(10030,'支付宝支付',$res);
        }elseif($pay_type == 3){
            //3 余额付款
            $uid = session('userinfo.id');
            $balance = M('User') -> where("id = $uid") -> getField('balance');//查询用户余额
            $balance < $order_amount ? $this -> ajaxReturnData(10001,'用户余额不足') : true;
            $user['balance'] = $balance - $order_amount;//用户余额扣除订单金额
            $resu = M('User') -> where("id = $uid") -> save($user);//保存扣除用户余额
            $resu !== false ? true : $this -> ajaxReturnData(0,'扣除用户余额失败！');
            $order['pay_status'] = 1; //支付状态 0未付款 1已付款
            $order['order_status'] = 1;//0待付款，1,待发货
            $reso = D('Order') -> where("id = $res") -> save($order);
            $reso !== false ? $this -> ajaxReturnData(10000,'success') : $this -> ajaxReturnData(0,'修改订单状态失败');

        }
    }

    //订单支付页
    public function pay(){
        $this -> check_login();
        if(IS_GET){
            //显示当前订单信息
            I('get.type','','string') ? $type = I('get.type','','string') : $this -> ajaxReturnData(0,'类型参数错误');
            I('get.order_id','','intval') ? $order_id = I('get.order_id','','intval') : $this -> ajaxReturnData(0,'参数错误');
            $isset_order = D('Order') -> find($order_id);
            empty($isset_order) ? $this -> ajaxReturnData(0,'订单不存在') : true;//验证订单号是否存在
            $isset_order['user_id'] == session('userinfo.id') ? true : $this -> ajaxReturnData(0,'订单号和用户不匹配');//验证订单号和用户是否匹配
            $where = array(
                'id'      => $order_id,
                'user_id' => session('userinfo.id'),
            );
            if($type === 'weixin'){
                $where['pay_type'] = 1;
                $swhere = "name = 'weixin_pay_img'";
                $name   = '微信账号';
                $desc   = '&nbsp;&nbsp;*&nbsp;当您提交之后会人工审核付款微信账号信息，所以请填写您付款的微信账号哦！';
            }else if($type == 'alipay'){
                $where['pay_type'] = 2;
                $swhere = "name = 'alipay_pay_img'";
                $name   = '支付宝账号';
                $desc   = '&nbsp;&nbsp;*&nbsp;当您提交之后会人工审核付款支付宝账号信息，所以请填写您付款的支付宝账号哦！';
            }
            $order = D('Order') -> field('order_sn,order_amount,address_info,shipping_type,pay_type') -> where($where) -> find();
            $order['shipping_type'] = shipping_type($order['shipping_type']);
            $order['pay_type']      = pay_type($order['pay_type']);
            $order['img']           = D('Setting') -> where($swhere) -> getField('content');
            $order['name']          = $name;
            $order['desc']          = $desc;
            $this -> assign('order',$order);
            $this -> display();
        }elseif(IS_POST){
            //提交审核数据
            I('post.type','','string') ? $type = I('post.type','','string') : $this -> ajaxReturnData(0,'类型参数错误');
            I('post.order_id','','intval') ? $order_id = I('post.order_id','','intval') : $this -> ajaxReturnData(0,'参数错误');
            $isset_order = D('Order') -> find($order_id);
            empty($isset_order) ? $this -> ajaxReturnData(0,'订单不存在') : true;//验证订单号是否存在
            $isset_order['user_id'] == session('userinfo.id') ? true : $this -> ajaxReturnData(0,'订单号和用户不匹配');//验证订单号和用户是否匹配
            I('post.tele','','string')    ? $tele = I('post.tele','','string')       : $this -> ajaxReturnData(0,'手机号不能为空');
            preg_match('/^\d{11}$/', $tele) == true ? true : $this -> ajaxReturnData(0,'手机号码格式不正确');
            I('post.WxOrPay','','string') ? $WxOrPay = I('post.WxOrPay','','string') : $this -> ajaxReturnData(0,'必填项不能为空');
            I('post.img','','string')     ? $img = I('post.img','','string')         : $this -> ajaxReturnData(0,'图片凭证不能为空');
            $setting = C('UPLOAD_QINIU');
            $Upload  = new \Think\Upload($setting);
            $info    = $Upload -> Upload($_FILES);
            $info ? true : $this -> error('上传图片失败！');//判断是否上传成功
            $data = array(
                'order_id'  => $order_id,
                'user_tele' => $tele,
                'prove_img' => $info['img']['url'],
                'add_time'  => time()
            );
            if($type === 'weixin'){
                $data['type'] = 1;
                $data['user_weixin'] = $WxOrPay;
            }else if($type == 'alipay'){
                $data['type'] = 2;
                $data['user_alipay'] = $WxOrPay;
            }
            $res = M('Order_img') -> add($data);
            $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'提交失败！');
        }else{
            $this -> ajaxReturnData(0,'访问方式错误');
        }

    }


}