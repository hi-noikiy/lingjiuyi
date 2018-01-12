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
                    if($postObj -> Event === 'subscribe'){          //说明发生的是用户关注事件
                        $msgType = 'text';
                        $contentStr = '终于等到你！~~';
                        $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                    }elseif($postObj -> Event === 'unsubscribe'){   //说明发生的是用户取消关注事件
                        $msgType = 'text';
                        $contentStr = '就要说拜拜，祝你一切顺心！';
                        $resultStr  = sprintf($tpl['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
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
                  "name":"今日歌曲",
                  "key":"V1001_TODAY_MUSIC"
             },
             {
                  "name":"菜单",
                   "sub_button":[
                   {
                        "type":"view",
                        "name":"搜索",
                        "url":"http://www.soso.com/"
                   },
                   {
                        "type":"miniprogram",
                        "name":"wxa",
                        "url":"http://mp.weixin.qq.com",
                        "appid":"wx286b93c14bbf93aa",
                        "pagepath":"pages/lunar/index"
                   },
                   {
                        "type": "scancode_waitmsg",
                        "name": "扫码带提示",
                        "key": "rselfmenu_0_0",
                        "sub_button": [ ]
                   },
                   {
                        "type": "scancode_push",
                        "name": "扫码推事件",
                        "key": "rselfmenu_0_1",
                        "sub_button": [ ]
                   },
                   {
                        "name": "发送位置",
                        "type": "location_select",
                        "key": "rselfmenu_2_0"
                   }]
             },
             {
                  "name":"发图",
                   "sub_button":[
                   {
                        "type": "pic_sysphoto",
                        "name": "系统拍照发图",
                        "key": "rselfmenu_1_0",
                        "sub_button": [ ]
                   },
                   {
                        "type": "pic_photo_or_album",
                        "name": "拍照或者相册发图",
                        "key": "rselfmenu_1_1",
                        "sub_button": [ ]
                   },
                   {
                        "type": "pic_weixin",
                        "name": "微信相册发图",
                        "key": "rselfmenu_1_2",
                        "sub_button": [ ]
                   },
                   {
                       "type": "media_id",
                       "name": "图片",
                       "media_id": "92v-BwMSfOlWbhHwxLLODAAzaByRAPoEZRmZgfo-H-sx5tTmWS7UdCQufp21Cj5O"
                   },
                   {
                       "type": "view_limited",
                       "name": "图文消息",
                       "media_id": "92v-BwMSfOlWbhHwxLLODAAzaByRAPoEZRmZgfo-H-sx5tTmWS7UdCQufp21Cj5O"
                   }]
             }]
        }';

        $res = curl_request($url,true,$data);//发送POST请求
        dump($access_token);
        dump($url);
        dump($data);
        dump($res);
//        echo $res;

    }

}