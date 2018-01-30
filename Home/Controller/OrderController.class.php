<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
require_once './Application/Tools/Qiniu/autoload.php';
use Qiniu\Auth;
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
            if($res){
                $status['id'] = $order_id;
                $status['pay_status'] = 2;
                M('Order') -> save($status);//修改订单状态
                $this -> ajaxReturnData();
            }else{
                $this -> ajaxReturnData(0,'提交失败！');
            }
        }else{
            $this -> ajaxReturnData(0,'访问方式错误');
        }

    }

    //取消订单
    public function edit_MyOrder(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误') : true;
        $this -> check_login();

        I('post.order_id','','intval') ? $id  = I('post.order_id','','intval') : $this -> ajaxReturnData(0,'订单编号错误');
        I('post.type','','intval') ? $type = I('post.type','','intval') : $this -> ajaxReturnData(0,'类型错误');
        //当前订单状态
        $order = D('Order') -> find($id);
        empty($order) ? $this -> ajaxReturnData(0,'没有此订单！') : true;

        $data['id'] = $order['id'];

        $uid = session('userinfo.id');
        $uid == $order['user_id'] ? true : $this -> ajaxReturnData('订单编号和用户不一致！');//验证当前登录用户是否是订单用户

        if($type == 5){
            //申请退款
            if($order['pay_status'] == 0 && $order['order_status'] == 0){
                $data['pay_status']   = 2;
                $data['order_status'] = 10;//未付款，直接取消订单即可
            }elseif($order['pay_status'] == 1 && $order['order_status'] == 1){
                $data['order_status'] = 5;//已付款，需要退钱,请求后台管理员退钱
            }elseif($order['pay_status'] == 1 && $order['order_status'] == 2){
                $data['order_status'] = 6;//退货退款
            }else{
                $this -> ajaxReturnData(0,'*订单状态错误*！');
            }
            $res = D('Order') -> save($data);
            $res !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'取消订单失败！');
        }elseif($type == 3){
            //确认收货
            if($order['pay_status'] == 1 && $order['order_status'] == 2){
                $data['order_status'] = 3;
                $res = D('Order') -> save($data);
                $res !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'取消订单失败！');
            }else{
                $this -> ajaxReturnData(0,'&订单状态错误&！');
            }
        }

    }

    //提醒发货
    public function ajax_deliver(){
        !IS_GET && !IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误') : true;
        $this -> check_login();

        I('get.id','','intval') ? $id = I('get.id','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $order_id = D('Order') -> find($id);
        $time     = time();
        $filename = 'Public/deliver.txt';
        $res      = file_put_contents($filename,$order_id.":".$time.";\r\n",FILE_APPEND);

        $res == false ? $this -> ajaxReturnData(0,'发送错误！') : $this -> ajaxReturnData();
    }

    //查看物流
    public function ajax_logistics(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误') : true;
        $this -> check_login();

        I('get.id','','intval') ? $id = I('get.id','','intval') : $this -> ajaxReturnData(0,'参数错误');
        $data     = D('Order') -> field('shipping_type,express_sn') -> where("id = $id") -> find();
        $params   = array(
            'id'  => C('WULIU_KEY'),
            'com' => cshipping_type($data['shipping_type']),
            'nu'  => $data['express_sn']
        );
        $express  = curl_request('http://api.kuaidi.com/openapi.html?id='.$params['id'].'&com='.$params['com'].'&nu='.$params['nu']);
        $res      = json_decode($express,true);
        $res['success'] ? $this -> ajaxReturnData(10000,'success',$res['data']) : $this -> ajaxReturnData(0,'当前没有物流信息，请稍后再试！');
    }

    //获取需要评论的商品信息
    public function get_comment_goods(){
        !IS_GET && !IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误') : true;
        $this -> check_login();

        I('get.id','','intval') ? $id = I('get.id','','intval') : $this -> ajaxReturnData(0,'参数错误！');
        $goods = D('Order') -> alias('a') -> field('a.user_id uid,b.id order_goods_id,b.goods_id gid,c.goods_small_img img') -> where("a.id = $id") -> join('zhouyuting_order_goods b on a.id = b.order_id') -> join('zhouyuting_goods c on b.goods_id = c.goods_id') -> select();
        $goods[0]['uid'] == session('userinfo.id') ? true : $this -> ajaxReturnData(0,'用户与订单不符！');
        empty($goods) ? $this -> ajaxReturnData(0,'没有订单！') : $this -> ajaxReturnData(10000,'success',$goods);
    }

    //添加商品评论
    public function add_comment(){
        !IS_POST && !IS_AJAX ? $this -> ajaxReturnData(0,'请求方式错误') : true;
        $this -> check_login();

        I('post.order_goods_id','','string') ? $order_gids = explode('+',I('post.order_goods_id','','string')) : $this -> ajaxReturnData(0,'订单编号错误');
        I('post.goods_id','','string')       ? $goods_ids  = explode('+',I('post.goods_id','','string'))       : $this -> ajaxReturnData(0,'商品编号错误');
        I('post.g_level','','string')        ? $g_levels   = explode('+',I('post.g_level','','string'))        : $this -> ajaxReturnData(0,'商品评论星级错误');
        I('post.content','','string')        ? $contents   = explode('+',I('post.content','','string'))        : $this -> ajaxReturnData(0,'评论内容错误');
        I('post.level','','string')          ? $levels     = explode('+',I('post.level','','string'))          : $this -> ajaxReturnData(0,'评论星级错误');
        I('post.w_level','','string')        ? $w_level    = I('post.w_level','','string') : $this -> ajaxReturnData(0,'物流评价错误');
        I('post.f_level','','string')        ? $f_level    = I('post.f_level','','string') : $this -> ajaxReturnData(0,'服务评价错误');
        count($order_gids) != count($goods_ids) || count($order_gids) != count($g_levels) || count($order_gids) != count($contents) || count($order_gids) != count($levels) ? $this -> ajaxReturnData(0,'参数错误') : true;
        $where = array(
            'a.user_id'  => session('userinfo.id'),
            'b.id'       => ['in',implode(',',$order_gids)],
            'b.goods_id' => ['in',implode(',',$goods_ids)],
        );
        $Order = D('Order') -> alias('a') -> field('a.id') -> where($where) -> join('zhouyuting_order_goods b on a.id = b.order_id') -> select();//验证订单号和商品号

        empty($Order) ? $this -> ajaxReturnData(0,'订单信息不匹配') : true;
        foreach($_FILES as $key => $value){
            if(empty($value['name'])){
                unset($_FILES[$key]);
            }
        }
        unset($where,$key,$value);
        if(!empty($_FILES)){
            //如果存在图片
            $setting   = C('UPLOAD_QINIU');
            $Upload  = new \Think\Upload($setting);
            $info    = $Upload -> upload($_FILES);
            empty($info) ? $this -> ajaxReturnData(0,'上传图片失败！') : true;
            $preKey = session('preKey');
            foreach($info as $key => $value){  //拼接图片数据
                if(isset($preKey)){  //如果存在，则比较
                    if($preKey == substr($key,strpos($key,'_'),2)){  //如果相等，则添加
                        $img[substr($key,0,strpos($key,'_') + 2)][] = $value['url'];
                    }else{
                        $img[substr($key,0,strpos($key,'_') + 2)][] = $value['url'];
                        session('preKey',substr($key,strpos($key,'_'),2));
                    }
                }else{
                    $img[substr($key,0,strpos($key,'_') + 2)][] = $value['url'];
                    session('preKey',substr($key,strpos($key,'_'),2));
                }
            }
            unset($key,$value);
            session('preKey',null);//删除preKey
            $i = 0;
            foreach($img as $key => $value){
                foreach($value as $k => $v){
                    $cimg[$i] = implode(',',$value);
                }
                $i ++;
            }
            unset($key,$value,$k,$v);
        }


        $word = $this -> setting_cache();//获取敏感词汇
        //循环数据
        foreach($order_gids as $key => $value){
            $data[] = array(
                'order_goods_id'  => $value,
                'goods_id'        => $goods_ids[$key],
                'user_id'         => session('userinfo.id'),
                'comment_content' => remove_xss(filter_vocabulary($contents[$key],$word['zhouyuting_filter_vocabulary'])),
                'comment_img'     => $cimg[$key] ? $cimg[$key] : '',
                'level'           => $levels[$key],
                'g_starlevel'     => $g_levels[$key],
                'w_starlevel'     => $w_level,
                'f_starlevel'     => $f_level,
                'is_anonymous'    => I('post.is_n','','intval'),
                'add_time'        => time(),
            );
        }
        $res = D('Comment') -> addAll($data);//添加评论
        $string = '';
        D('Order') -> where("id = $Order") -> setField('order_status',4);//修改订单状态
        $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'添加失败！');
    }



}