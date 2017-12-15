<?php
namespace Home\Model;
use Think\Model;
class GoodsModel extends Model{
    //protected $trueTableName = 'advice';

    //protected $tableName = 'goods';
    /*
    //设置数据表中的字段
    //使用字段定义,在查询时就不会查询数据库
    protected $fields = array('id','user_id','content','time');
    //主键字段默认为id
    protected $pk = 'id'; //如果主键字段名为id,可以省略
    */


    protected $_map = array(
        'gid' =>'goods_id', // 把表单中name映射到数据表的username字段
        'title'  =>'goods_name', // 把表单中的mail映射到数据表的email字段
        'prices' => 'goods_price',
        'sn' => 'goods_sn',
        '_img' => 'goods_small_img'
    );
}