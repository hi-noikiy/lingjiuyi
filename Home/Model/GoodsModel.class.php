<?php
namespace Home\Model;
use Think\Model\RelationModel;
class GoodsModel extends RelationModel{

    protected $pk     = 'goods_id';

    protected $fields = array('goods_id','goods_name','goods_bigprice','goods_price','goods_introduce','click_num','min_video','goods_sales');

    protected $_link = array(
        'Goodspics'=>array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'Goodspics',
            'foreign_key'   => 'goods_id',
        ),
        'Heart'=>array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'User_goods',
            'foreign_key'   => 'goods_id',
        ),
    );

    public function get_test(){
        return 1;
    }

}