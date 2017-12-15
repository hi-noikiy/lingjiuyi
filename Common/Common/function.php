<?php
/**
 * @return bool
 * 作者:周雨婷
 * 时间:
 * 描述：判断是否是移动端设备
 */
function isMobile(){
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
    //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * @return bool
 * 作者:
 * 时间:
 * 描述：判断是否是微信浏览器
 */
function is_weixin_browser()
{
    return strpos($_SERVER['HTTP_USER_AGENT'], "MicroMessenger") !== false;
}

/**
 * @param $url
 * @param bool|true $post
 * @param array $data
 * @param bool|false $https
 * @return mixed
 * 作者:周雨婷
 * 时间:
 * 描述：发送curl请求
 */
function curl_request($url, $post = true, $data = array(), $https = false){
    $ch = curl_init($url);//A、使用curl_init函数初始化curl请求(设置请求地址)
    if($post)
    {//判断post请求
        curl_setopt($ch, CURLOPT_POST, true);// B、使用curl_setopt函数配置curl请求(设置请求方式请求参数等)//设置请求方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置请求参数
    }
    if($https)
    { //https协议请求 默认发送http协议的请求，如果是https的请求，需要做以下设置
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //检测ssl证书 这里是禁用检测
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //检测ssl证书和域名匹配 这里是禁用检测
    }
    // C、使用curl_exec函数发送curl请求
    //默认返回true|false  如果需要获取请求的执行结果，需要设置CURLOPT_RETURNTRANSFER
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//如果不设置，默认直接输出执行结果
    $result = curl_exec($ch);
    curl_close($ch); // D、请求结束使用curl_close函数关闭curl请求，释放资源
    return $result;//返回结果给调用方
}


/**
 * @param $email String 收件人
 * @param $subject String 邮件主题
 * @param $body String 邮件内容
 * @return bool|string
 * 作者:周雨婷
 * 时间:
 * 描述：发送邮件
 */
function sendmail($email,$id,$email_code){

    $subject = '零玖一商城激活';

    $url ="http://www.lingjiuyi.cn/Home/User/jihuo/id/{$id}/code/{$email_code}";
    $body = "点击一下连接进行激活:<br /><a href='{$url}'>{$url}</a>;<br/>如果点击无法跳转,请复制以上链接直接在浏览器打开.";
    require_once './Application/Tools/PHPMailer/PHPMailerAutoload.php';

    $mail = new \PHPMailer();
    $mail->isSMTP();                                        //设置使用SMTP协议服务
    $mail->Host = 'smtp.sina.com';                          //SMTP邮件服务器的地址
    $mail->CharSet='UTF-8';                                 // 设置邮件内容的编码
    $mail->SMTPAuth = true;                                 //开启SMTP权限认证
    $mail->Username = 'miyuermi0@sina.com';                 // 邮箱地址
    $mail->Password = '1314zyt520stone';                    // 邮箱密码（授权码）
    // $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                      // TCP port to connect to

    $mail->setFrom('miyuermi0@sina.com');                   //发件人
    $mail->addAddress($email);                              //收件人
    // $mail->addAddress('ellen@example.com');              // Name is optional
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    // $mail->addAttachment('/var/tmp/file.tar.gz');         // 添加附件
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                     //设置邮箱的内容格式为html

    $mail->Subject = $subject;                               //邮件主题
    $mail->Body    = $body;//邮件内容
    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if(!$mail->send()) {
        // return false;
        // echo 'Mailer Error: ' . $mail->ErrorInfo;
        return $mail->ErrorInfo;
    } else {
        return true;
    }


}

function test(){
    $mail->SetLanguage('zh_cn',VENDOR_PATH.'PHPMailer/language/phpmailer.lang-zh_cn.php');// 设置报错提示语言

    $mail->SMTPDebug = 3;                                // 启用详细调试输出Enable verbose debug output 2:邮件调试模式

    $mail->isSMTP();                                     //设置邮件使用SMTP协议服务
    $mail->Host = 'smtp.mxhichina.com';                       //SMTP邮件服务器的地址
//    $mail->Host = 'smtp.163.com';                       //SMTP邮件服务器的地址
    $mail->SMTPAuth = true;                              //开启SMTP权限认证
    $mail->Username = 'postmaster@lingjiuyi.cn';              // 邮箱地址
//    $mail->Username = 'elisa_zhouyuting@163.com';              // 邮箱地址
    $mail->Password = '1314Zyt520';                      //邮箱密码（授权码）
//     $mail->SMTPSecure = 'ssl';                        //Enable TLS encryption,`ssl` also accepted加密方式TLS或ssl
    $mail->Port = 25;                                    // TCP port to connect to TCP端口连接 根据smtp服务器商定

    $mail->setFrom('postmaster@lingjiuyi.cn');                  //发件人
    $mail->addAddress($email,'嗨！亲~~');               //收件人
    // $mail->addAddress('ellen@example.com');               // Name is optional
    $mail->addReplyTo('postmaster@lingjiuyi.cn', 'zhouyuting'); // 增加一个回复地址(别人回复时的地址).
    // $mail->addCC('cc@example.com'); // 抄送地址
    // $mail->addBCC('bcc@example.com'); // 密送地址

    // $mail->addAttachment('/var/tmp/file.tar.gz');         // 添加附件
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                  //设置邮箱的内容格式为html

    $mail->Subject = $subject;                 //邮件主题
    $mail->Body    = $body;//邮件内容
    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if(!$mail->send()) {
        // return false;
        // echo 'Mailer Error: ' . $mail->ErrorInfo;
        return $mail->ErrorInfo;
    } else {
        return true;
    }
}

/**
 * @param $str
 * @param int $start
 * @param $length
 * @param string $charset
 * @param bool|true $suffix
 * @return string
 * 作者:周雨婷
 * 时间:
 * 描述：截取中文字符串
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(function_exists("mb_substr")){
        if($suffix){
            return mb_substr($str, $start, $length, $charset)."...";
        }else{
            return mb_substr($str, $start, $length, $charset);
        }
    }elseif(function_exists('iconv_substr')) {
        if($suffix){
            return iconv_substr($str,$start,$length,$charset)."...";
        }else{
            return iconv_substr($str,$start,$length,$charset);
        }
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix){
        return $slice."...";
    }else{
        return $slice;
    }
}

/**
 * @param $url: 二维码URL参数
 * @param $filename: 二维码图片保存路径(若不生成文件则设置为false)
 * @param $userimg: 二维码logo
 * @param string $level 二维码容错率，默认L
 * @param int $size 二维码图片每个黑点的像素，默认4
 * 作者:周雨婷
 * 时间:2017-09-27
 * 描述：生成的带logo的二维码
 */
function makecode($url, $filename, $userimg, $level= 'H', $size = 10){
    ob_clean ();
    Vendor('phpqrcode.phpqrcode');
    $errorCorrectionLevel = $level ;//容错级别
    $matrixPointSize = intval($size);//生成图片大小
    //生成二维码图片
    $object = new \QRcode();
    $object->png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

    $QR = imagecreatefromstring(file_get_contents($filename));//imagecreatefromstring:创建一个图像资源从字符串中的图像流
    $logo = imagecreatefromstring(file_get_contents($userimg));
    if (imageistruecolor($logo)) imagetruecolortopalette($logo, false, 65535);//修改图片的色彩
    $QR_width = imagesx($QR);// 获取图像宽度函数
    $QR_height = imagesy($QR);//获取图像高度函数
    $logo_width = imagesx($logo);// 获取图像宽度函数
    $logo_height = imagesy($logo);//获取图像高度函数
    $logo_qr_width = $QR_width / 4;//logo的宽度
    $scale = $logo_width / $logo_qr_width;//计算比例
    $logo_qr_height = $logo_height / $scale;//计算logo高度
    $from_width = ($QR_width - $logo_qr_width) / 2;//规定logo的坐标位置
    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
    /**     imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
     *      参数详情：
     *      $dst_image:目标图象连接资源。
     *      $src_image:源图象连接资源。
     *      $dst_x:目标 X 坐标点。
     *      $dst_y:目标 Y 坐标点。
     *      $src_x:源的 X 坐标点。
     *      $src_y:源的 Y 坐标点。
     *      $dst_w:目标宽度。
     *      $dst_h:目标高度。
     *      $src_w:源图象的宽度。
     *      $src_h:源图象的高度。
     * */
    Header("Content-type: image/png");
    //$url:定义生成带logo的二维码的地址及名称
    imagepng($QR,$filename);
}

/**
 * @param String $url 需要生成二维码的内容
 * @param String $filename 图片的保存地址
 * @param string $level 容错级别
 * @param int $size二维码尺寸大小
 * 作者:周雨婷
 * 时间:2017-19-27
 * 描述：生成的不带logo的二维码
 */
function qrcode($url, $filename, $level= 'H', $size = 10){
    Vendor('phpqrcode.phpqrcode');
    $errorCorrectionLevel = $level ;//容错级别
    $matrixPointSize = intval($size);//生成图片大小
    //生成二维码图片
    $object = new \QRcode();
    $object->png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
}

/**
 * @param $string
 * @return string
 * 作者:
 * 时间:
 * 描述：使用htmlpurifier防范xss攻击
 */
function remove_xss($string){
    //相对index.php入口文件，引入HTMLPurifier.auto.php核心文件
    require_once './Public/Plugins/htmlpurifier/HTMLPurifier.auto.php';
    // 生成配置对象
    $cfg = HTMLPurifier_Config::createDefault();
    // 以下就是配置：
    $cfg -> set('Core.Encoding', 'UTF-8');
    // 设置允许使用的HTML标签
    $cfg -> set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,br,p[style],span[style],img[width|height|alt|src]');
    // 设置允许出现的CSS样式属性
    $cfg -> set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
    // 设置a标签上是否允许使用target="_blank"
    $cfg -> set('HTML.TargetBlank', TRUE);
    // 使用配置生成过滤用的对象
    $obj = new HTMLPurifier($cfg);
    // 过滤字符串
    return $obj -> purify($string);
}