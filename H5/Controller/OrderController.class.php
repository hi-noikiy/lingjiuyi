<?php
namespace H5\Controller;
//2、引入核心控制器
use Think\Controller;
//3、定义News控制器
class OrderController extends CommonController {

    //支付成功
    public function payed(){
        $this -> redirect(U('H5/User/order/type/0'),'支付成功');
    }

    //支付失败
    public function unpay(){
        $this -> redirect(U('H5/User/order/type/0'),'支付失败');
    }

    //订单详情
    public function detail(){
        layout(false);
        $this -> display();
    }

    //ajax订单详情
    public function ajax_detail(){
        $id = $_GET['id'];
        empty($id) ? $this -> ajaxReturnData(0,'参数错误') : $id;

        $ids = D('Order_goods') -> field('goods_attr_ids') -> where("order_id = $id") -> select();
        $string = '';
        foreach($ids as $k => $v){
            $string .= implode(',',$ids[$k]).',';
        }
        $string = rtrim($string,',');
        $where = array(
            'a.id' => $id,
            'd.id' => ['in',$string]
        );
        $fields = 'b.id,order_amount,order_sn,address_info,number,order_status,create_time,goods_small_img,attr_value,goods_name,b.goods_price,number,goods_attr_ids';

        $order = D('Order')
            -> field($fields)
            -> alias('a')
            -> where($where)
            -> join('zhouyuting_order_goods b on a.id = b.order_id')
            -> join('zhouyuting_goods c on b.goods_id = c.goods_id')
            -> join('zhouyuting_goods_attr d on b.goods_id = d.goods_id')
            -> select();
        $orderinfo = [];
        foreach($order as $key => $value){
            $orderinfo[$key]['id']       = $order[$key]['id'];//订单金额
            $orderinfo[$key]['oa']       = $order[$key]['order_amount'];//订单金额
            $orderinfo[$key]['osn']      = $order[$key]['order_sn'];//订单编号
            $addr = explode(' ',$order[$key]['address_info']);
            $orderinfo[$key]['addr_name'] = $addr[0];//收货地址
            $orderinfo[$key]['addr_tele'] = $addr[1];
            $orderinfo[$key]['addr_info'] = $addr[2];
            $orderinfo[$key]['os']        = order_status($order[$key]['order_status']);//订单状态
            $orderinfo[$key]['add_time']  = date('Y-m-d H:i',$order[$key]['create_time']);//下单时间
            $orderinfo[$key]['img']       = $order[$key]['goods_small_img'];//商品图片
            $orderinfo[$key]['attr']      = $order[$key]['attr_value'];//规格
            $orderinfo[$key]['name']      = $order[$key]['goods_name'];//名称
            $orderinfo[$key]['price']     = $order[$key]['goods_price'];//单价
            $orderinfo[$key]['number']    = $order[$key]['number'];//单价
        }

        $this -> ajaxReturnSuccess(compact('orderinfo'));
    }

    //ajax提醒卖家发货
    public function ajax_deliver(){
        $id       = $_GET['id'];
        empty($id) ? $this -> ajaxReturnData(0,'参数错误！') : true ;
        $order_sn = D('Order') -> where("id = $id") -> getField('order_sn');
        $time     = time();
        $filename = 'Public/deliver.txt';
        $res      = file_put_contents($filename,$order_sn.":".$time.";\r\n",FILE_APPEND);

        $res == false ? $this -> ajaxReturnData(10001,'发送错误！') : $this -> ajaxReturnData(10000,'提醒发货成功！');

    }

    //ajax申请退款
    public function ajax_refund(){
        $id  = $_GET['id'];
        $res = D('Order') -> where("id = $id") -> save(['order_status' => 9]);//9申请退款中
        $res !== false ? $this -> ajaxReturnData(10000,'申请退款成功') : $this -> ajaxReturnData(10001,'申请退款失败');
    }

    //ajax确认收货
    public function ajax_receipt(){
        $id  = $_GET['id'];
        $res = D('Order') -> where("id = $id") -> save(['order_status' => 3]);//3，已收货
        $res !== false ? $this -> ajaxReturnData(10000,'确认收货成功！') : $this -> ajaxReturnData(10001,'确认收货失败！');
    }

    //ajax查看物流
    public function ajax_seewl(){
        $id       = $_GET['id'];
        $data     = D('Order') -> field('shipping_type,express_sn') -> where("id = $id") -> find();
        $params   = array(
            'id'  => C('WULIU_KEY'),
            'com' => cshipping_type($data['shipping_type']),
            'nu'  => $data['express_sn']
        );
        $express  = curl_request('http://api.kuaidi.com/openapi.html?id='.$params['id'].'&com='.$params['com'].'&nu='.$params['nu']);
        $res      = json_decode($express,true);
        $res['success'] ? $this -> ajaxReturnSuccess($res['data']) : $this -> ajaxReturnData(10001,'当前没有物流信息，请稍后再试！');
    }
    //ajax申请退货
    public function ajax_tuihuo(){
        $id = $_GET['id'];
        $res = D('Order') -> where("id = $id") -> save(['order_status' => 6]); //6申请退货/退款中
        $res !== false ? $this -> ajaxReturnData(10000,'申请退货成功！') : $this -> ajaxReturnData(10001,'申请退货失败！');
    }

    //ajax填写退货信息
    public function ajax_tuihuoinfo(){
        $data = array(
            'order_id'     => I('post.id','','intval'),
            'express_code' => I('post.com'),
            'express_sn'   => I('post.nu')
        );
        $res = D('Order_refunds') -> add($data);
        $ress = D('Order') -> where(['id' => $data['order_id']]) -> save(['order_status' => 9]); //6申请退款中
        $res && ($ress !== false) ? $this -> ajaxReturnData(10000,'提交退货信息成功！') : $this -> ajaxReturnData(10001,'提交退货信息失败！');

    }

}