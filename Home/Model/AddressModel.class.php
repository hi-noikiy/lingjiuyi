<?php
namespace Home\Model;
use Think\Model;
class AddressModel extends Model{
    protected $_validate = array(
        array('name','require','收货人姓名不能为空!'),
        array('tele','require','收货人手机号不能为空'),
        array('tele','/^\d{11}$/','手机号码格式不正确'),
        array('addrinfo','require','详细地址不能为空!'),
    );

}