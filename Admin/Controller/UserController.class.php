<?php
/**
 * Created by PhpStorm.
 * User: wcj
 * Date: 2017/10/13
 * Time: 13:30
 */

namespace Admin\Controller;


class UserController extends BaseController
{
    public function index(){
        if(IS_AJAX){
            $info = D('User') -> select();
            foreach($info as $key => $value){
                $info[$key]['birthday'] = substr($info[$key]['birthday'],0,10);
                $info[$key]['weixin']   ? $wx = $info[$key]['weixin'] : $wx = '空';
                $info[$key]['weibo']    ? $wb = $info[$key]['weibo']  : $wb = '空';
                $info[$key]['qq']       ? $qq = $info[$key]['qq']     : $qq = '空';
                $info[$key]['sanfang']  = $wx.'/<br/>'.$wb.'/<br/>'.$qq;
            }
            $data = ['data' => $info];
            $this -> ajaxReturn($data);
        }else{
            $this -> display();
        }
    }

    public function add(){
        $this ->display();
    }

    public function addrlst(){
        if(IS_AJAX){
            $id = I('get.id','','intval');
            empty($id) ? $this -> ajaxReturnData(0,'用户编号异常！') : true;
            $addr = D('Address') -> where(['user_id' => $id]) -> select();
            $addr ? $this -> ajaxReturnSuccess($addr) : $this -> ajaxReturnData(0,'没有地址信息');
        }else{
            $this -> ajaxReturnData(0,'请求方式错误！');
        }

    }

    public function pricelst(){
        if(IS_AJAX){
            $id = I('get.id','','intval');
            empty($id) ? $this -> ajaxReturnData(0,'用户编号异常！') : true;
            $price = D('User_price_log') -> where(['uid' => $id]) -> select();
            $price ? $this -> ajaxReturnSuccess($price) : $this -> ajaxReturnData(0,'用户未消费！');
        }else{
            $this -> ajaxReturnData(0,'请求方式错误！');
        }

    }

    public function reclst(){
        $rec = D('User_recharge')
            -> alias('a')
            -> field('a.*,b.username')
            -> join('left join zhouyuting_user b on a.uid = b.id')
            -> select();
        foreach($rec as $key => $value){
            $rec[$key]['pt'] = pay_type($rec[$key]['pay_type']);
            $rec[$key]['ps'] = pay_status($rec[$key]['pay_status']);
            $rec[$key]['cs'] = cash_status($rec[$key]['cash_status']);
        }
        $data = ['data' => $rec];
        $this -> ajaxReturn($data);
    }

    public function likelst(){
        $like = D('Userlike')
            -> alias('a')
            -> field('a.*,b.username')
            -> join('left join zhouyuting_user b on a.user_id = b.id')
            -> select();
        $data = ['data' => $like];
        $this -> ajaxReturn($data);
    }

}