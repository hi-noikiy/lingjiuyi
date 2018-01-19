<?php
namespace Home\Controller;
use Think\Controller;

define("TOKEN", "zhouyuting");
define('DEBUG', true);
class WeixinController extends CommonController{

    //初始化验证
    public function valid()
    {
        /*
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
        //验证成功之后注释
        */

        //调用回复功能
        $this->responseMsg();
    }

    //验证token
    private function checkSignature()
    {
        //暂时不需要
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token =TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    //回复消息
    public function responseMsg()
    {

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];  //接收客户端发送的数据

        global $tpl;                                //在函数内部想要使用全局变量，需要global声明一下
        require_once "./Application/Home/Controller/ReplyTemplate.php"; //引入回复消息模板文件
        if (!empty($postStr)){

            libxml_disable_entity_loader(true);     //过滤xml实体中嵌入的代码

            $postObj      = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

            $fromUsername = $postObj -> FromUserName;
            $toUsername   = $postObj -> ToUserName;           //接收者信息（公众平台）
            $keyword      = trim($postObj -> Content);        //发送者发送的关键字
            $time         = time();
            $MsgType      = $postObj -> MsgType;                //接收消息类型

            switch($MsgType){                   //根据消息类型，进行判断
                case 'text':                    //1.说明手机端发送的是文本消息
                    switch($keyword){
                        case '?':
                            $msgType = 'text';          //定义回复消息的类型
                            $contentStr = "你发送的是文本消息，我回复的也是文本消息\r\n【1】特种服务号码\r\n【2】通讯服务号码\r\n【3】银行服务号码\r\n"; //定义回复消息的文本内容
                            //利用sprintf函数填充模板中的数据，sprintf函数是格式化数据的一个函数
                            $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;

                        case '1':
                            $msgType = 'text';
                            $contentStr = "常用特种服务号码：\r\n匪警：110\r\n火警：119\r\n急救中心：120";
                            $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;

                        case '2':
                            $msgType = 'text';
                            $contentStr = "常用通讯服务号码：\r\n中国移动：10086\r\n中国电信：10000\r\n中国联通：10010";
                            $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;

                        case '3':
                            $msgType = 'text';
                            $contentStr = "银行服务号码：\r\n建设银行：95533\r\n工商银行：99588\r\n农业银行：95599";
                            $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;

                        case '图片':
                            $MediaId = "92v-BwMSfOlWbhHwxLLODAAzaByRAPoEZRmZgfo-H-sx5tTmWS7UdCQufp21Cj5O"; //测试号
//                            $MediaId = "IA5J5trE3KwCo3Ch8Qkzou9-wdm7yHVYxGM1i07UvHcvm7cTo_-kNUduzoFtMHrN"; //正式号
                            $msgType = 'image';
                            $resultStr  = sprintf($tpl['image'], $fromUsername, $toUsername, $time, $msgType, $MediaId);
                            echo $resultStr;
                            break;

                        case '音乐':
                            $msgType = 'music';
                            $title   = '原声大碟';
                            $desc    = '非常动听的音乐';
                            $url     = 'http://owtcx73yl.bkt.clouddn.com/Kalimba.mp3';
                            $resultStr  = sprintf($tpl['music'], $fromUsername, $toUsername, $time, $msgType, $title, $desc, $url, $url);
                            echo $resultStr;
                            break;

                        case '单图文':
                            $msgType = 'news';
                            $count = 1;
                            $item = "<item>
                                    <Title><![CDATA[美味食谱]]></Title>
                                    <Description><![CDATA[一份独特的番茄鸡蛋]]></Description>
                                    <PicUrl><![CDATA[http://owtcx73yl.bkt.clouddn.com/FkfPENXp81ds9lP3ZjG3Z3DZXXze]]></PicUrl>
                                    <Url><![CDATA[http://www.lingjiuyi.cn/]]></Url>
                                    </item>";
                            $resultStr  = sprintf($tpl['news'], $fromUsername, $toUsername, $time, $msgType, $count, $item);
                            echo $resultStr;
                            break;
                        case '多图文':
                            $msgType = 'news';
                            $count = 4;
                            $item = "";
                            for($i = 1; $i <= $count; $i++){
                                $item .= "<item>
                                          <Title><![CDATA[美味食谱{$i}]]></Title>
                                          <Description><![CDATA[一份独特的番茄鸡蛋{$i}]]></Description>
                                          <PicUrl><![CDATA[http://owtcx73yl.bkt.clouddn.com/FkfPENXp81ds9lP3ZjG3Z3DZXXze]]></PicUrl>
                                          <Url><![CDATA[http://www.lingjiuyi.cn/]]></Url>
                                          </item>";
                            }
                            $resultStr  = sprintf($tpl['news'], $fromUsername, $toUsername, $time, $msgType, $count, $item);
                            echo $resultStr;
                            break;

                        default:
                            //接入图灵机器人
                            $key = '9719c6ea3dca4a59a743359d7ec75645';
                            $url = "http://www.tuling123.com/openapi/api?key={$key}&info={$keyword}";
                            $res = file_get_contents($url);
                            $info = json_decode($res);
                            $contentStr = $info -> text;

                            $msgType = 'text';
                            $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;

                    }
                    break;

                case 'image':                   //2.说明手机端发送的是图文消息
                    $msgType = 'text';
                    $contentStr = '你发送的是图片消息，我回复的是文本消息';
                    $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    break;

                case 'voice':                   //3.说明手机端发送的是语音消息
                    $Recognition = $postObj -> Recognition;//开启语音识别之后，获取语音识别结果，把声音转换成文字的结果
                    $key = '9719c6ea3dca4a59a743359d7ec75645';
                    $url = "http://www.tuling123.com/openapi/api?key={$key}&info={$Recognition}";
                    $res = file_get_contents($url);
                    $info = json_decode($res);
                    $contentStr = $info -> text;

                    $msgType = 'text';
                    $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    break;

                case 'video':                       //4.1说明手机端发送的是视频消息
                    $msgType = 'text';
                    $contentStr = '你发送的是视频消息，我回复的是文本消息';
                    $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    break;

                case 'shortvideo':                  //4.2说明手机端发送的是小视频消息
                    $msgType = 'text';
                    $contentStr = '你发送的是小视频消息，我回复的是文本消息';
                    $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    break;

                case 'location':                    //5.说明手机端发送的是地理位置消息
                    $Location_X = $postObj -> Location_X;   //获取纬度
                    $Location_Y = $postObj -> Location_Y;   //获取经度
                    $msgType = 'text';
                    $contentStr = "你发送的是地理位置消息，我回复的是文本消息，经度是{$Location_X},纬度是{$Location_Y}";
                    $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    break;

                case 'link':                        //6.说明手机端发送的是地理位置消息
                    $msgType = 'text';
                    $contentStr = '你发送的是链接消息，我回复的是文本消息';
                    $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    break;

                case 'event':                        //说明手机端发送的消息类型是event
                    if($postObj -> Event == 'subscribe'){          //说明发生的是用户关注事件
                        $msgType = 'text';
                        $contentStr = '终于等到你！';
                        $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;

                    }elseif($postObj -> Event == 'unsubscribe'){   //说明发生的是用户取消关注事件
                        $msgType = 'text';
                        $contentStr = '就要说拜拜，祝你一切顺心！';
                        $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                    }elseif($postObj -> Event == 'CLICK'){  //响应菜单中的click类型菜单
                        if($postObj -> EventKey == 'V1001_TODAY_MUSIC'){ //单击的是今日推荐
                            //在zhouyuting_weixin_today_recommends表中获取类型，连表查询出 跳转链接 等数据
                            //根据类型判断是展示文字 还是 图片 还是网页，然后制作网页模板
                            $msgType = 'news';
                            $count = 1;
                            $item = "<item>
                                    <Title><![CDATA[美味食谱]]></Title>
                                    <Description><![CDATA[一份独特的番茄鸡蛋]]></Description>
                                    <PicUrl><![CDATA[http://owtcx73yl.bkt.clouddn.com/FkfPENXp81ds9lP3ZjG3Z3DZXXze]]></PicUrl>
                                    <Url><![CDATA[http://www.lingjiuyi.cn/]]></Url>
                                    </item>";
                            $resultStr  = sprintf($tpl['news'], $fromUsername, $toUsername, $time, $msgType, $count, $item);
                            echo $resultStr;
                            break;
                        }

                    }
                    break;

            }


        }else {
            echo '咋不说哈呢';
            exit;
        }
    }

    //创建微信公众号自定义菜单
    public function createMenu(){
        $access_token = getToken();//获取access_token
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";//接口调用
        //设置菜单信息
        $data = '{
            "button":[
             {
                 "type":"click",
                  "name":"今日推荐",
                  "key":"V1001_TODAY_MUSIC"
             },
             {
                  "name":"友情链接",
                   "sub_button":[
                   {
                        "type":"view",
                        "name":"花瓣网",
                        "url":"http://huaban.com/"
                   },
                   {
                        "type":"view",
                        "name":"加入我们",
                        "url":"http://www.lingjiuyi.cn/Index/contact"
                   }]
             },
             {
                 "type":"view",
                  "name":"商城",
                  "url":"http://h5.lingjiuyi.cn/"
             }]
        }';

        $res = curl_request($url,true,$data);//发送POST请求
        $code = $res -> errcode;  //{"errcode":0,"errmsg":"ok"} 创建成功
        $msg = $res -> errmsg;
        $this -> ajaxReturnData($code,$msg);
    }

    //删除自定义菜单
    public function delMenu(){
        $access_token = getToken();//获取access_token
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=$access_token";//接口调用
        $res = curl_request($url,false);//发送get请求
        $res = json_decode($res);
        $code = $res -> errcode;  //{"errcode":0,"errmsg":"ok"} 删除成功
        $msg = $res -> errmsg;
        $this -> ajaxReturnData($code,$msg);
    }

    //获取自定义菜单
    public function getMenu(){
        $access_token = getToken();//获取access_token
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=$access_token";//接口调用
        $res = curl_request($url,false);//发送get请求
        $res = json_decode($res);
        empty($res) ? $this -> ajaxReturnData(0,'没有菜单') : $this -> ajaxReturnData(10000,'success',$res);

    }

    //获取单个用户信息
    public function getUserInfo(){
        $access_token = getToken();//获取access_token
        $openid = 'ocyEZ02LaJByLnS5ZO2ByYqHdNbI';//用户openid
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
        $res = curl_request($url,false);//发送get请求
        $res = json_decode($res);
        empty($res) ? $this -> ajaxReturnData(0,'没有用户信息') : $this -> ajaxReturnData(10000,'success',$res);
    }

    //获取用户列表
    public function getUserInfoList($next_openid){
        $access_token = getToken();//获取access_token
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$access_token&next_openid=";
        isset($next_openid) ? $url .= $next_openid : false;
        $res = curl_request($url,false);
        $res = json_decode($res,true);
        $count = $res -> count;//如果 count > 10000 $next_openid = $res -> next_openid;需要拼接参数
        return compact('count','res');
    }

    //新增临时素材
    public function upload_file(){
        $access_token = getToken();//获取access_token
        $type = I('get.type','','string'); //获取上传文件类型
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access_token&type=$type";
        $data['media'] = '@'.dirname(__FILE__).'/sec-img-9.jpg';
        $res = curl_request($url,true,$data);
        $res = json_decode($res);
        $this -> ajaxReturn($res);
    }

    //获取临时素材
    public function get_file(){
        $access_token = getToken();//获取access_token
        $media_id = I('get.media_id','','string');
        $type = I('get.type','','string');
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";
        if($type == 'video'){
            $res = curl_request($url,false);
            $res = json_decode($res);
            $this -> ajaxReturn($res);
        }else{
            $head_arr_index = get_headers($url,1);
            dump($head_arr_index);
        }

    }


    //发送客服消息
    public function send_msg(){
        $access_token = getToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
        //设置发送的数据
        $data = '{
            "touser":"ocyEZ02LaJByLnS5ZO2ByYqHdNbI",
            "msgtype":"text",
            "text":
                {
                "content":"hello,感谢您的咨询，一会给你回复"
                }
        }';
        $res = curl_request($url,true,$data);
        echo $res;

    }

    //群发消息
    public function send_msg_all(){
        $access_token = getToken();
        $userList = $this -> getUserInfoList();//获取用户列表
        if($userList['count'] >= 10000){
            $userList = $this -> getUserInfoList($userList['res']['next_openid']);
            $touser = $userList['res']['data']['openid'];
        }else{
            $touser = $userList['res']['data']['openid'];
        }
        $touser = implode('","',$touser);

        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$access_token";
        $media_id = "Zrn92E6ALUfrKDnGTX4tXaypCVIRx4aJAnPVVH8_LXKS7WV150Xuj0HSuiTMAW0Y";
        $dataa = '
        {
           "touser":["'.$touser.'"],
           "mpnews":{
              "media_id":"'.$media_id.'"
           },
            "msgtype":"mpnews"，
            "send_ignore_reprint":0,
        }';//图文消息

        $data = '{
           "touser":["'.$touser.'"],
            "msgtype": "text",
            "text": { "content": "hell 柯尊龙."}
        }';//文本消息

        $res = curl_request($url,true,$data);
        $res = json_decode($res);
        $this -> ajaxReturn($res);  //errcode == 0 成功

    }

    //发送模板消息，当用户购买成功之后发送消息给用户
    public function pay_success(){
        $access_token = getToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";

        $touser = "ocyEZ0-hzjlg5DD_u8JreGGuNEYE";//接收人

        $temp_id = "unukeDybxOQOvuTBFDZ9SreGtB_1vHxuBOv3-7_hIpg";

        $data = '{
           "touser":"'.$touser.'",
           "template_id":"'.$temp_id.'",
           "data":{
               "goods_name": {
                   "value":"德芙巧克力青柠味--规格：套装大盒礼品",
                   "color":"#173177"
               },
               "buy_price":{
                   "value":"120.98",
                   "color":"#173177"
               },
               "buy_time":{
                   "value":"2018年1月14日",
                   "color":"#173177"
               }
           }
        }';

        $res = curl_request($url,true,$data);
        dump($res);
    }

    //生成带参数的二维码
    public function qrcode(){
        $access_token = getToken();
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
        $data = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}';
        $res = curl_request($url,true,$data);//发送请求，获取ticket
        $info = json_decode($res);
        unset($url,$res);
        $ticket = urlencode($info -> ticket);
        dump($info);
        dump($ticket);
        $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$ticket}";
        $res = curl_request($url,false);//返回值就是二维码的图片
        dump(get_headers($res));
        file_put_contents('./Public/Uploads/erweima.jpg',$res);

    }


    //当用户关注之后保存用户openid
    public function save_code(){

        $appid     = C('TEST_WEIXIN_PUBLIC_CONFIG.appID');
        $appsecret = C('TEST_WEIXIN_PUBLIC_CONFIG.appsecret');

        $code = $_GET["code"];
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';

        $res = curl_request($url,false);
        $json_obj = json_decode($res,true);

        //根据openid和access_token查询用户信息
        $access_token = getToken();//获取access_token

        $openid = $json_obj['openid'];

        $data = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';

        $res = curl_request($url,true,$data);

        $user_obj = json_decode($res,true);
        $_SESSION['user'] = $user_obj;
        print_r($user_obj);
    }


}