<?php
//1、定义命名空间
namespace H5\Controller;
//2、引入核心控制器
use Think\Controller;
use Think\Page;

//3、定义News控制器
class GoodsController extends CommonController {
    //特惠精品
    public function index(){
        $this -> display();
    }

    //普通商品详情页
    public function detail(){
        layout(false);
        $this -> display();
    }

    //活动商品详情页
    public function goods(){
        layout(false);
        $this -> display();
    }

    //ajax获取特惠精品商品
    public function ajax_index(){
        $p = $_GET['p'] ? $_GET['p'] : 1;
        $fields = 'goods_id,goods_name,goods_price,goods_sn,goods_small_img';
        $where = 'is_act = 0';
        $count = M('Goods') -> where($where) -> count('goods_id');
        $pagesize = 10;//每次获取的数据条数
        $pager = new Page($count,$pagesize);
        $totalPages = $pager -> totalPages;
        $firstRow   = $pager -> firstRow;

        $sort     = $_GET['sort'] ? $_GET['sort'] : 'id';
        $rules    = $_GET['rules'] ? $_GET['rules'] : 'desc';
        $order    = "goods_".$sort." ".$rules;
        $keywords = $_GET['keywords'] ? $_GET['keywords'] : '';
        $where   .= " AND `goods_name` LIKE '%$keywords%'";

        $goodslist = M('Goods') -> field($fields) -> where($where) -> limit($firstRow,$pagesize) -> order($order) -> select();

        $pagesize = ($count < $pagesize) ? $count : $pagesize;
        $pager = array(
            'nowPage' => (int)$p,//当前页
            'pagesize'   => (int)$pagesize,
            'totalPages' => $totalPages
        );

        empty($goodslist) ? $this -> ajaxReturnData(0,'无数据') : $this -> ajaxReturnSuccess(compact('goodslist','pager'));

    }

    //ajax商品详情页
    public function ajax_detail(){
        $id = $_GET['gid'];//接收商品id
        if(empty($id)){
            $code = 0;
            $msg  = '参数错误';
        }else{
            $field = 'goods_id,goods_name,goods_bigprice,goods_price,goods_number,goods_introduce,goods_small_img';
            $model = M('Goods');
            $good = $model -> field($field) -> where(array('goods_id' => $id)) -> find();
            $good['goods_introduce'] = explode(',',$good['goods_introduce']);
            $pics_big = M('Goodspics') -> field('pics_big') -> where(array('goods_id' => $id)) -> select();
            $goodsattrs = M('Goods_attr') -> alias('a') -> field('a.id,a.attr_value,b.attr_name') -> where(array('a.goods_id' => $id,'b.attr_type' => 1)) -> join('zhouyuting_attribute b on a.attr_id = b.attr_id') -> select();
            foreach($goodsattrs as $k => $v){
                $new_attr_radio[$v['attr_name']][] = $v;
            }
            $num = 0;
            foreach($new_attr_radio as $key =>$value){
                $attr_radio[$num] = $value;
                $num++;
            }
            if(empty($good)){
                //设置返回数据格式
                $data = array(
                    'code' => 10001,
                    'msg'  => '没有商品',
                    'info' => compact('good','pics_big','attr_radio')
                );
            }else{
                //设置返回数据格式
                $data = array(
                    'code' => 10000,
                    'msg'  => 'success',
                    'info' => compact('good','pics_big','attr_radio')
                );
            }
        }
        //返回数据
        $this->ajaxReturn($data);

    }

    //ajax活动商品详情
    public function ajax_goods(){
        //商品名称，商品价格，参考价格，商品相册，商品详情
        $goods_id = I('get.gid');
        $fields = 'goods_id,goods_name,goods_price,goods_bigprice,goods_introduce,goods_small_img,goods_number';
        $where = "goods_id = $goods_id";
        $goodsinfo = M('Goods') -> field($fields) -> where($where) -> find();
        $goodsinfo['goods_introduce'] = explode(',',$goodsinfo['goods_introduce']);
        $goodspics = M('Goodspics') -> field('pics_sma') -> where($where) -> select();
        $goodsattrs = M('Goods_attr')
            -> alias('a')
            -> field('a.id,a.attr_value,b.attr_name')
            -> where(array('a.goods_id' => $goods_id,'b.attr_type' => 1))
            -> join('zhouyuting_attribute b on a.attr_id = b.attr_id')
            -> select();
        foreach($goodsattrs as $k => $v){
            $new_attr_radio[$v['attr_name']][] = $v;
        }
        $num = 0;
        foreach($new_attr_radio as $key =>$value){
            $attr_radio[$num] = $value;
            $num++;
        }
        if(empty($goodspics)){
            $this -> ajaxReturnError(0,'没有商品详情');
        }else{
            $this -> ajaxReturnSuccess(compact('goodsinfo','goodspics','attr_radio'));
        }
    }

    public function test(){
        if(IS_AJAX){
            $data = D('Category') -> where("pid = 0")-> select();
            $data ? $this -> ajaxReturnSuccess($data) : $this -> ajaxReturnData(0,'没有数据');
        }else{
            $this -> display();
        }


    }

    public function test1(){
        layout(false);
        $order_id = 222;
        $price = '0.01';

        $orderid = 345;
        $host = 'http://h5.lingjiuyi.cn'; //获取域名

        $url = $host.u('Goods/dopay');
        $url2 = $host.u('Goods/showpay');
        require_once './Application/Tools/alipay_v3/alipay.php';

        $alipay = new \publicAlipayV3;
        $alipay->setConfig();
        $alipay->dopay($order_id,$orderid,$price,$url,$url2);
    }
    public function dopay(){
        require_once './Application/Tools/alipay_v3/wap/lib/alipay_notify.class.php';
        $alipay_config = $_POST;
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        $data=array(
            'type' => 1,
            'uid'  => 1,
            'prices' => '0.01',
        );
        D('User_recharge')->add($data);
        session('$verify_result',$verify_result);
        dump($verify_result);
        die;
        if($verify_result)
        {
            if(!empty($_POST['out_trade_no']))
            {
                $trade_status=$_POST['trade_status'];
                if ($trade_status == 'TRADE_SUCCESS')
                {

                    dump($verify_result);die;
                    $result = function(){};   // 你的业务逻辑，当操作成功的时候返回true
                    if ($result)
                    {
                        echo "success";        //请不要修改或删除
                    }
                    else
                    {
                        echo 'fail';
                    }
        }
                elseif($trade_status == 'TRADE_FINISHED')
                {
                    echo "success";        //请不要修改或删除
                }
            }
        }
    }
    public function showpay(){
        dump(session('$verify_result'));
        http://h5.lingjiuyi.cn/Goods/showpay?is_success=T&notify_id=RqPnCoPT3K9%252Fvwbh3Ih32tTB9K5ZMDKjJAhoKz4PR%252BbZV%252FyOGBvo4cQSZuaJbQZmxuXs&notify_time=2018-02-26+10%3A05%3A30&notify_type=trade_status_sync&out_trade_no=111&payment_type=1&seller_id=2088811228311076&service=alipay.wap.create.direct.pay.by.user&subject=234&total_fee=0.01&trade_no=2018022621001004580584966354&trade_status=TRADE_SUCCESS&sign=14e017a68184f8bb75d2bc16cf0a9e72&sign_type=MD5
    }



}