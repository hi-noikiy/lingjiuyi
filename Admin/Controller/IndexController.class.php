<?php
namespace Admin\Controller;

class IndexController extends BaseController {
    public function index() {
        if(!IS_AJAX){
            $menu   = D('Menu');
            $row    = M('Admin') -> where("username='".session('username')."'")->find();
            $roleid = $row['roleid'];//角色id
            $menuA  = $menu->query('select * from zhouyuting_menu where menuid in (select menuid from zhouyuting_privileges where roleid='.$roleid.') and parentid=0 and is_show = 1 order by listorder asc');//顶级显示菜单

            $menuB  = $menu->query('select * from zhouyuting_menu where menuid in (select menuid from zhouyuting_privileges where roleid='.$roleid.') and is_show = 1  order by listorder asc');//二级显示菜单

            $this -> assign('menuA',$menuA);
            $this -> assign('menuB',$menuB);
            $this -> display('Index/index');
        }else{
            //发货信息
            $msg = file_get_contents('Public/deliver.txt');//读取发货消息
            $msg = rtrim(str_replace(PHP_EOL, '', $msg),';');//去除末尾符号，以免多出一个数组元素
            $msg = explode(';',$msg);//转换为数组
            foreach($msg as $key => $value){
                $new_msg[$key]['msg']  = substr($value,0,strpos($value,':'));
                $new_msg[$key]['time'] = date('m/d H:i',substr($value,strpos($value,':')+1));
            }
            unset($key,$value);
            foreach($new_msg as $key => $value){
                $new[$value['msg']]['msg']  = $value['msg'];
                $new[$value['msg']]['time'] = $value['time'];
                $new[$value['msg']]['num'] += 1;
            }//转换发货消息的显示格式
            $this -> ajaxReturnData(10000,'success',$new);
        }

    }

    public function main() {
        $os = get_os();
        $software = $_SERVER['SERVER_SOFTWARE'];
        $year = date('Y');
        $mon  = date('Y-m');
        $day  = date('Y-m-d');

        $fields = 'SUM(sale_money) sum,SUM(goods_smallprice) price';
        $join = 'zhouyuting_goods b on a.goods_id = b. goods_id';


        $money[]  = D('Saleonline') -> alias('a') ->field($fields) -> join($join) -> find();//成本
        $money[] = D('Saleonline') -> alias('a') ->field($fields) -> where(['a.add_time' => ['LIKE',"$year%"]]) -> join($join) -> find();//成本
        $money[]  = D('Saleonline') -> alias('a') ->field($fields) -> where(['a.add_time' => ['LIKE',"$mon%"]])  -> join($join) -> find();//成本
        $money[]  = D('Saleonline') -> alias('a') ->field($fields) -> where(['a.add_time' => ['LIKE',"$day%"]])  -> join($join) -> find();//成本

        $this -> assign('money',$money);
        $this -> assign('os',$os);
        $this -> assign('software',$software);
        $this -> assign('version',PHP_VERSION);
        $this -> assign('sapi',php_sapi_name());
        $this -> assign('mysql','mysql');
        $this -> assign('tp',THINK_VERSION);
        $this -> assign('uploadmax',ini_get('upload_max_filesize'));
        $this -> display('Index/main');
    }

    public function charts(){
        //订单汇总
        //title:'商品销售汇总'  num:num+num mintitle:'销售总数量' name:name:price元 y:num
        $fields = 'c.goods_name name,sum(b.goods_price) prices,sum(b.number) num,b.goods_id';
        $where  = "a.pay_status = 1";
        $goods  = D('Order')
                -> field($fields)
                -> alias('a')
                -> where($where)
                -> join('zhouyuting_order_goods b on a.id = b.order_id')
                -> join('zhouyuting_goods c on b.goods_id = c.goods_id')
                -> group('b.goods_id')
                -> select();
        $goods[0]['title']    = '商品销售汇总';
        $goods[0]['mintitle'] = '商品销售总数量';
        $user  = D('Order')
                -> field('b.username name,sum(order_amount) prices,count(a.id) num,user_id')
                -> alias('a')
                -> where($where)
                -> join('zhouyuting_user b on a.user_id = b.id')
                -> group('user_id')
                -> select();
        $user[0]['title']    = '金牌买家汇总';
        $user[0]['mintitle'] = '订单购买总数量';
        $list = array(
            0 => $goods,
            1 => $user,
        );
        foreach($list as $key => $value){
            foreach($value as $k => $v){
                $list[$key][$k]['name'] = $v['name'].':'.$v['prices'].'元';
            }
        }
        $list ? $this -> ajaxReturnSuccess($list) : $this -> ajaxReturnData(0,'没有数据');
    }
}