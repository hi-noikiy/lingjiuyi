<?php
namespace Admin\Model;
use Think\Model;

class SettingModel extends Model {
    protected $pk = 'id';

    protected $_validate = array(
        array('name','require','设置名称为必填！'),
        array('desc','require','中文注释为必填！'),
        array('content','require','存放内容为必填！'),
    );

}