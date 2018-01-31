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

    /**
     * 获取配置信息写入缓存
     */
    public function setting_cache()
    {
        $setting = array();
        $res     = $this->getField('name,content');
        foreach ($res as $key => $val) {
            $setting['zhouyuting_' . $key] = unserialize($val) ? unserialize($val) : $val;
        }
        F('setting', $setting);
    }

}