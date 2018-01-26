<?php

/**
 * @param $password
 * @return bool|string
 * 作者:周雨婷
 * 时间:
 * 描述：密码加密的函数password hashing API
 */
function encrypt_pwd($password){
    //提供盐值(salt)和消耗值
    $options = [
        //'salt'  =>  custom_function_for_salt(), //write your own code to generate a suitable salt
        'cost'  => 12, //the default cost is 10
    ];
    $hash = password_hash($password,PASSWORD_DEFAULT,$options);
    return $hash;
}

/**
 * @param $phone
 * @return string
 * 作者:
 * 时间:
 * 描述：手机号显示时加密的函数
 */
function encrypt_phone($phone){
    return substr($phone,0,3).'****'.substr($phone,7,4);
}

#递归方法实现无限极分类
/**
 * @param $list
 * @param int $pid
 * @param int $level
 * @return array
 * 作者:
 * 时间:
 * 描述：递归方法实现无限极分类
 */
function getTree($list,$pid=0,$level=0) {
    static $tree = array();
    foreach($list as $row) {
        if($row['pid']==$pid) {
            $row['level'] = $level;
            $tree[] = $row;
            getTree($list, $row['id'], $level + 1);
        }
    }
    return $tree;
}

/**
 * @param $type
 * @return string
 * 作者:周雨婷
 * 时间:
 * 描述：显示配送方式
 */
function shipping_type($type){
    //0圆通 1申通 2韵达 3中通 4顺丰
    switch($type){
        case 0:
            return '圆通';
        case 1:
            return '申通';
        case 2:
            return '韵达';
        case 3:
            return '中通';
        case 4:
            return '顺丰';
    }

}

/**
 * @param $type
 * @return string
 * 作者:周雨婷
 * 时间:
 * 描述：选择配送方式
 */
function cshipping_type($type){
    //0圆通 1申通 2韵达 3中通 4顺丰
    switch($type){
        case 0:
            return 'yuantong';
        case 1:
            return 'shentong';
        case 2:
            return 'yunda';
        case 3:
            return 'zhongtong';
        case 4:
            return 'shunfeng';
    }

}

/**
 * @param $status
 * @return string
 * 作者:周雨婷
 * 时间:
 * 描述：判断支付状态
 */
function pay_status($status){
    //0未付款 1已付款
    switch($status){
        case 0:
            return '未付款';
        case 1:
            return '已付款';
        case 2:
            return '已取消';
    }
}

/**
 * @param $type
 * @return string
 * 作者:周雨婷
 * 时间:
 * 描述：判断支付方式
 */
function pay_type($type){
    //0银联 1微信 2支付宝
    switch($type){
        case 0:
            return '银联';
        case 1:
            return '微信';
        case 2:
            return '支付宝';
        case 3:
            return '余额';
    }
}

/**
 * @param $status
 * @return string
 * 作者:周雨婷
 * 时间:
 * 描述：判断订单状态
 */
function order_status($status){
    //0待付款，1,待发货，2已发货，3，已收货，4已评价，5取消订单，6申请退货，7退货成功，8退款成功
    switch($status){
        case 0:
            return '待付款';
        case 1:
            return '待发货';
        case 2:
            return '已发货';
        case 3:
            return '已收货';
        case 4:
            return '已完成';
        case 5:
            return '申请退款中';
        case 6:
            return '申请退货/退款中';
        case 7:
            return '可退货';
        case 8:
            return '等待卖家确认收货';
        case 9:
            return '退货成功，等待退款中...';
        case 10:
            return '取消订单成功';
    }

}

/**
 * @param $type
 * @return string
 * 作者:周雨婷
 * 时间:
 * 描述：判断订单类型
 */
function order_type($type){
    //0普通订单，1充值订单，2活动订单，3积分订单
    switch($type) {
        case 0:
            return '普通订单';
        case 1:
            return '充值订单';
        case 2:
            return '活动订单';
        case 3:
            return '积分订单';
    }
}

/**
 * @param $type
 * @return string
 * 作者:周雨婷
 * 时间:
 * 描述：判断提现审核状态
 */
function cash_status($type){
    //0普通订单，1充值订单，2活动订单，3积分订单
    switch($type) {
        case 0:
            return '审核中';
        case 1:
            return '成功';
        case 2:
            return '失败';
    }
}

/**
 * @param $arr 需要转换的数组
 * @param $type 需要的新的数组下标
 * @return array 转换成功之后的数组
 * 作者:周雨婷
 * 时间:2017-12-12
 * 描述：将无序的数组根据分类重新归纳排列
 */
function change_array($arr,$type){
    $nthree_goods = [];
    foreach($arr as $key => $value){
        $nthree_goods[$value[$type]][] = $value;
    }
    unset($key,$value);
    $j = 0;
    $new_three_goods = [];
    foreach($nthree_goods as $key => $value){
        $new_three_goods[$j] = $value;
        $j++;
    }
    unset($key,$value);
    return $new_three_goods;
}

/**
 * 对提供的数据进行urlsafe的base64编码。
 *
 * @param string $data 待编码的数据，一般为字符串
 *
 * @return string 编码后的字符串
 * @link http://developer.qiniu.com/docs/v6/api/overview/appendix.html#urlsafe-base64
 */
function base64_urlSafeEncode($data)
{
    $find = array('+', '/');
    $replace = array('-', '_');
    return str_replace($find, $replace, base64_encode($data));
}

/**
 * 对提供的urlsafe的base64编码的数据进行解码
 *
 * @param string $str 待解码的数据，一般为字符串
 *
 * @return string 解码后的字符串
 */
function base64_urlSafeDecode($str)
{
    $find = array('-', '_');
    $replace = array('+', '/');
    return base64_decode(str_replace($find, $replace, $str));
}


function request_by_curl($remote_server,$post_string,$upToken) {

    $headers = array();
    $headers[] = 'Content-Type:image/png';
    $headers[] = 'Authorization:UpToken '.$upToken;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$remote_server);
    //curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER ,$headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

