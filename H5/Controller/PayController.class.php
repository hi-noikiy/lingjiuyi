<?php
namespace H5\Controller;
//2、引入核心控制器
use Think\Controller;
//3、定义News控制器
class PayController extends CommonController {

    //在类初始化方法中，引入相关类库
    public function _initialize() {
        vendor('Alipay.Corefunction');
        vendor('Alipay.Md5function');
        vendor('Alipay.Notify');
        vendor('Alipay.Submit');
    }

    public function index(){
        $id    = $_GET['id'];
        $type  = D('Order') ->where("id = $id") -> getField('pay_type');
        switch ($type){
            case 0:
                //银联
                $this -> ajaxReturnData(0,'暂时不支持银联支付');
                break;
            case 1:
                //微信
                $this->redirect('Pay/weixinpay',['id' => $id]);
                break;
            case 2:
                //支付宝
                $this->redirect('Pay/alipay',['id' => $id]);
                break;
        }
        /*
        if(isset($_POST['APP_PAY'])){
            //APP调用接口
            $url = full_url('Pay/apppay');
            $this->ajaxReturnSuccess(compact('url'));
        }elseif(is_weixin_browser()){
            //微信中的支付
            $this->redirect('Pay/weixinpay');
        }else{
            //普通浏览器
            $this->redirect('Pay/doalipay',['id' => $id]);
        }
        */

    }

    //APP支付
    public function apppay(){

    }

    //微信支付
    public function weixinpay($id){
        $order = D('Order') -> where("id = $id") -> find();
        if($order['order_status'] != 0){
            $this->success('订单已支付成功',U('user/order'));
        }
        vendor('weixinpay.lib.WxPay#Api');
        vendor('weixinpay.example.WxPay#NativePay');
        vendor('weixinpay.example.WxPay#JsApiPay');
        vendor('weixinpay.example.log');


        //如果是web端 则扫码支付
        //b，进行相应的参数组装
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();

        $input->SetBody("零玖一" . $order['order_sn']);//商品描述
        $input->SetAttach("test");//附加数据
        $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));//商户订单号（网站自己的订单编号）
        $input->SetTotal_fee($order['order_amount'] * 100 ); //订单金额（单位：分）
        $input->SetTime_start(date("YmdHis"));//交易起始时间
        $input->SetTime_expire(date("YmdHis", time() + 1800));//交易结束时间
        $input->SetGoods_tag("test");//订单优惠标记
        $input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");//通知地址（异步通知），必须线上可以访问

        //如果是微信浏览器 则jsapi支付 暂时没有权限
        if(is_weixin_browser()){

            $input->SetTrade_type("JSAPI");//交易类型（MWEB为h5支付）JSAPI | NATIVE | APP | WAP
            $input->SetProduct_id("123456789");//商品ID
            //$openId = session('weixin_openid');
            $openId = "oEIZDuHaBLb89Q7WaAw7cYcYk9KQ";//先使用自己的测试
            $input->IsOpenidSet($openId);

            Vendor('weixinapi.system.WxPayPubHelper.WxPayPubHelper');
            //使用jsapi接口
            $jsApi = new \JsApi_pub();
            if(empty($openId)){

                //=========步骤1：网页授权获取用户openid============
                //通过code获得openid
                //if (!isset($_GET['code']))
                //{
                    //触发微信返回code码
                //    $url = $jsApi->createOauthUrlForCode(\WxPayConf_pub::JS_API_CALL_URL);
                //    Header("Location: $url");
                //}else
                //{
                    //获取code码，以获取openid
                //    $code = $_GET['code'];
                //    $jsApi->setCode($code);
                //    $openId = $jsApi->getOpenId();
                //}

                //或者
                //后期修改为登录获取用户的真实openid
                $config = C('WEIXIN_JSAPI_PAY');
                //$config['CALLBACK'] = "http://dg.v-town.cc/weixin/login_response";
                $config['CALLBACK'] = "http://192.168.0.63/H5/Pay/login_response";

                Vendor('weixinapi.public.class#authorize');
                $tool = new \weixintoolauthorize($config);
                $result = $tool->target();
                $this -> ajaxReturnData(0,'00',$result);
            }

            //c，获取支付地址
            $result = \WxPayApi::unifiedOrder($input);
            $prepay_id = $result['prepay_id'];
            $jsApi->setPrepayId($prepay_id);
            $jsApiParameters = $jsApi->getParameters();
            $this -> ajaxReturnData(10002,'微信浏览器支付',$jsApiParameters);

        }elseif(isMobile()){

            $config = C('WEIXIN_JSAPI_PAY');


            $subject = "零玖一" . $order['order_sn']; //商品描述
            $total_amount = $order['order_amount'] * 100; //金额
            $order_id = $order['order_sn']; ////订单号
            $nonce_str=MD5($order_id);    //随机字符串
            $spbill_create_ip = get_ip(); //终端ip
            $key = $config['KEY'];
            //以上参数接收不必纠结，按照正常接收就行，相信大家都看得懂

            //$spbill_create_ip = '118.144.37.98'; //终端ip测试
            $trade_type = 'MWEB';//交易类型 具体看API 里面有详细介绍

            $notify_url = 'http://jl.v-town.cc/'; //回调地址
            $scene_info ='{"h5_info":{"type":"Wap","wap_url":"http://www.123.com","wap_name":"测试支付"}}'; //场景信息
            //对参数按照key=value的格式，并按照参数名ASCII字典序排序生成字符串
            $signA = "appid=".$config['APPID']."&body=$subject&mch_id=".$config['MCHID']."&nonce_str=$nonce_str&notify_url=$notify_url&out_trade_no=$order_id
　　　　　　&scene_info=$scene_info&spbill_create_ip=$spbill_create_ip&total_fee=$total_amount&trade_type=$trade_type";

            $strSignTmp = $signA."&key=$key"; //拼接字符串

            $sign = strtoupper(MD5($strSignTmp)); // MD5 后转换成大写

            $post_data = "<xml>
                       <appid>".$config['APPID']."</appid>
                       <body>$subject</body>
                       <mch_id>".$config['MCHID']."</mch_id>
                       <nonce_str>$nonce_str</nonce_str>
                       <notify_url>$notify_url</notify_url>
                       <out_trade_no>$order_id</out_trade_no>
                       <scene_info>$scene_info</scene_info>
                       <spbill_create_ip>$spbill_create_ip</spbill_create_ip>
                       <total_fee>$total_amount</total_fee>
                       <trade_type>$trade_type</trade_type>
                       <sign>$sign</sign>
                       </xml>";//拼接成XML 格式

            $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";//微信传参地址
            $dataxml = http_post($url,$post_data); //后台POST微信传参地址  同时取得微信返回的参数，http_post方法请看下文
            dump($dataxml);
            $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA); //将微信返回的XML 转换成数组
            dump($objectxml);
            if($objectxml['return_code'] == 'SUCCESS')  {
                if($objectxml['result_code'] == 'SUCCESS'){//如果这两个都为此状态则返回mweb_url，详情看‘统一下单’接口文档
                    return $objectxml['mweb_url']; //mweb_url是微信返回的支付连接要把这个连接分配到前台
                }
                if($objectxml['result_code'] == 'FAIL'){
                    $err_code_des = $objectxml['err_code_des'];
                    return $err_code_des;
                }
            }




            //如果是移动端 ，但是非微信浏览器则h5支付 暂时没有权限
            //$input->SetTrade_type("MWEB");//交易类型（MWEB为h5支付）JSAPI | NATIVE | APP | WAP
            //$input->SetProduct_id("123456789");//商品ID
            echo '1';


            //c，获取支付地址
            //$result = \WxPayApi::unifiedOrder($input);
            //if($result['mweb_url']){
            //    $url = $result['mweb_url'].'&redirect_url=https%3A%2F%2Fwww.gujia.la';//希望用户支付完成后跳转至redirect_url
            //    $this -> ajaxReturnSuccess($url);
            //}else{
            //    $this -> ajaxReturnData(0,'目前只支持微信扫码支付，请在网页端进行扫码支付！<br/>'.$result['return_msg']);
            //}
            //$result = $wechatAppPay->unifiedOrder( $params );
            //$url = $result['mweb_url'].'&redirect_url=https%3A%2F%2Fwww.gujia.la';//希望用户支付完成后跳转至redirect_url
            //return $url;


        }else{

            //如果是web端 则扫码支付
            //交易类型（native为扫码支付）
            $input->SetTrade_type("NATIVE");
            //商品ID
            $input->SetProduct_id("123456789");

            //c，获取支付地址
            $result = $notify->GetPayUrl($input);
            $url2 = $result["code_url"];
            //d，输出二维码图片
            $url = "http://paysdk.weixin.qq.com/example/qrcode.php?data=" . urlencode($url2);
            $this -> ajaxReturnData(10001,'支付二维码图片',$url);

        }


    }

    public function login_response(){
        $config = C('WEIXIN_JSAPI_PAY');
        //$config['CALLBACK'] = "http://dg.v-town.cc/weixin/login_response";
        $config['CALLBACK'] = "http://192.168.0.63/H5/Pay/login_response";

        Vendor('weixinapi.public.class#authorize');
        $tool = new \weixintoolauthorize($config);
        $result = $tool->getAuthorize();
        //session('weixin_openid',$result['openid']);
        session('weixin_openid',"oEIZDuHaBLb89Q7WaAw7cYcYk9KQ");
    }


    //浏览器支付
    //alipay支付宝支付
    //该方法其实就是将接口文件包下alipayapi.php的内容复制过来
    //然后进行相关处理
    function alipay($id){
        $order = D('Order') -> where("id = $id") -> find();
        $_POST['trade_no']     = $order['order_sn'];
        $_POST['ordsubject']   = $order['order_sn'];
        $_POST['ordtotal_fee'] = $order['order_amount'];
        $_POST['ordbody']      = order_type($order['order_type']);
        $_POST['ordshow_url']  = 'User/Order/type/'.$order['order_type'];

        //这里我们通过TP的C函数把配置项参数读出，赋给$alipay_config；
        $alipay_config=C('ALIPAY_CONFIG');

        /**************************请求参数**************************/
        $payment_type = "1";                        //支付类型 //必填，不能修改
        $notify_url = C('ALIPAY.notify_url');       //服务器异步通知页面路径
        $return_url = C('ALIPAY.return_url');       //页面跳转同步通知页面路径
        $seller_email = C('ALIPAY.seller_email');   //卖家支付宝帐户必填
        $out_trade_no = $_POST['trade_no'];         //商户订单号 通过支付页面的表单进行传递，注意要唯一！
        $subject = $_POST['ordsubject'];            //订单名称 //必填 通过支付页面的表单进行传递
        $total_fee = $_POST['ordtotal_fee'];        //付款金额  //必填 通过支付页面的表单进行传递
        $body = $_POST['ordbody'];                  //订单描述 通过支付页面的表单进行传递
        $show_url = $_POST['ordshow_url'];          //商品展示地址 通过支付页面的表单进行传递
        $anti_phishing_key = "";                    //防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
        $exter_invoke_ip = get_client_ip();         //客户端的IP地址
        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($alipay_config['partner']),
            "payment_type"    => $payment_type,
            "notify_url"    => $notify_url,
            "return_url"    => $return_url,
            "seller_email"    => $seller_email,
            "out_trade_no"    => $out_trade_no,
            "subject"    => $subject,
            "total_fee"    => $total_fee,
            "body"            => $body,
            "show_url"    => $show_url,
            "anti_phishing_key"    => $anti_phishing_key,
            "exter_invoke_ip"    => $exter_invoke_ip,
            "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
        );
        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"post", "确认");
        echo $html_text;
    }


    //服务器异步通知页面方法
    //其实这里就是将notify_url.php文件中的代码复制过来进行处理
    function notifyurl(){

        //这里还是通过C函数来读取配置项，赋值给$alipay_config
        $alipay_config=C('ALIPAY_CONFIG');
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        //$verify_result = $alipayNotify->verifyNotify();
        //if($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no   = $_POST['out_trade_no'];      //商户订单号
            $trade_no       = $_POST['trade_no'];          //支付宝交易号
            $trade_status   = $_POST['trade_status'];      //交易状态
            $total_fee      = $_POST['total_fee'];         //交易金额
            $notify_id      = $_POST['notify_id'];         //通知校验ID。
            $notify_time    = $_POST['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
            $buyer_email    = $_POST['buyer_email'];       //买家支付宝帐号；
            $parameter = array(
                "out_trade_no"     => $out_trade_no, //商户订单编号；
                "trade_no"     => $trade_no,     //支付宝交易号；
                "total_fee"     => $total_fee,    //交易金额；
                "trade_status"     => $trade_status, //交易状态
                "notify_id"     => $notify_id,    //通知校验ID。
                "notify_time"   => $notify_time,  //通知的发送时间。
                "buyer_email"   => $buyer_email,  //买家支付宝帐号；
            );

            if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                //普通及时到账：不允许退款，支付成功返回交易状态为TRADE_FINISHED
                //高级及时到账：允许退款，支付成功返回交易状态为TRADE_SUCCESS
                //if(!checkorderstatus($out_trade_no)){
                //    orderhandle($parameter);
                    //进行订单处理，并传送从支付宝返回的参数；
                //}
                $data['order_status'] = 1;//订单状态：1,待发货
                $data['pay_status']   = 1;//支付状态：1已付款
                D('Order') ->where("order_sn = $out_trade_no") -> save($data);
                echo "success";        //请不要修改或删除
            }else {
                echo "trade_status=".$_GET['trade_status'];
                $this->redirect(C('alipay.errorpage'));//跳转到配置项中配置的支付失败页面；
            }

        //}else {
            //验证失败
            //echo "fail";
        //}
    }


    // 页面跳转处理方法；
    // 这里其实就是将return_url.php这个文件中的代码复制过来，进行处理；
    function returnurl(){
        //头部的处理跟上面两个方法一样，这里不罗嗦了！
        $alipay_config=C('ALIPAY_CONFIG');
        $alipayNotify = new \AlipayNotify($alipay_config);//计算得出通知验证结果
        //$verify_result = $alipayNotify->verifyReturn();
        //if($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $out_trade_no   = $_GET['out_trade_no'];      //商户订单号

            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                //if(!checkorderstatus($out_trade_no)){
                //    orderhandle($parameter);  //进行订单处理，并传送从支付宝返回的参数；
                //}
                $data['order_status'] = 1;//订单状态：1,待发货
                $data['pay_status']   = 1;//支付状态：1已付款
                D('Order') ->where("order_sn = $out_trade_no") -> save($data);
                $this->redirect(C('alipay.successpage'));//跳转到配置项中配置的支付成功页面；
            }else {
                echo "trade_status=".$_GET['trade_status'];
                $this->redirect(C('alipay.errorpage'));//跳转到配置项中配置的支付失败页面；
            }
        //}else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            //echo "支付失败！";
        //}
    }

}