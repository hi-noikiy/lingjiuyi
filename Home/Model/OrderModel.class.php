<?php
namespace Home\Model;
use Think\Model\RelationModel;
class OrderModel extends RelationModel{

    protected $pk     = 'id';

    protected $_link = array(
        'Order_goods'=>array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'Order_goods',
            'foreign_key'   => 'order_id',
        ),
    );

}