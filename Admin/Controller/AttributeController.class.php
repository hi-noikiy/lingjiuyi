<?php
namespace Admin\Controller;
class AttributeController extends BaseController{
	public function index(){
		$cate = M('Category') -> where("pid = 0") -> select();
		$this->assign('cate',$cate);
		$this->display();
	}

	public function lst(){

		I('get.attr_id','','intval')    ? $where['attr_id'] = I('get.attr_id','','intval') : false;
		I('get.cate_id','','intval')    ? $where['cate_id'] = I('get.cate_id','','intval') : false;
		I('get.attr_input_type')        ||  (I('get.attr_input_type') === '0') ? $where['attr_input_type'] = I('get.attr_input_type','','intval') : false;
		I('get.attr_type')              || (I('get.attr_type') === '0') ? $where['attr_type'] = I('get.attr_type','','intval') : false;
		I('get.attr_name','','string')   ? $attr_name = I('get.attr_name','','string') : false;
		I('get.attr_values','','string') ? $attr_values = I('get.attr_values','','string') : false;
		if(isset($attr_name)){
			$where['attr_name'] = ['LIKE',"%$attr_name%"];
		}
		if(isset($attr_values)){
			$where['attr_values'] = ['LIKE',"%$attr_values%"];
		}

		$column = $_GET['order'][0]['column'];
		switch($column){
			case 0:
				$order = 'attr_id';
				break;
			case 1:
				$order = 'cate_name';
				break;
			case 2:
				$order = 'attr_name';
				break;
			case 3:
				$order = 'attr_type';
				break;
			case 4:
				$order = 'attr_input_type';
				break;
			case 5:
				$order = 'attr_values';
				break;
		}
		$dir    = $_GET['order'][0]['dir'];
		$order  = $order.' '.$dir;
		$start  = I('get.start','','intval');
		$length = I('get.length','','intval');
		$count  = D('Attribute') -> alias('t1') -> where($where) -> join("left join zhouyuting_category t2 on t1.cate_id=t2.id ") -> count();
		$attr   = D('Attribute') -> alias('t1') -> where($where) -> join("left join zhouyuting_category t2 on t1.cate_id=t2.id ") -> limit($start,$length) -> order($order) -> select();
		$data   = array(
			'data'            => $attr, //array 需要在表格中显示的数据
			'draw'            => I('get.draw','','intval'),//integer 请求序号
			'recordsTotal'    => $count,    //integer 过滤之前的总数据量
			'recordsFiltered' => $count,    //integer 过滤之后的总数据量
		);
		$this -> ajaxReturn($data);
	}

	public function add(){
		if(IS_POST){
			$data = I('post.');
			$res = M('Attribute')->add($data);
			if($res){
				$this->success('添加成功!',U('Admin/Attribute/index'));
			}else{
				$this->error('添加失败!');
			}

		}else{
			$cate = M('Category') -> where("pid = 0") -> select();
			$this->assign('cate',$cate);
			$this->display();
		}
		
	}

	public function edit(){
		if(IS_POST){
			$data = I('post.');
			$data['attr_id'] = I('post.id','','intval');
			$res = D('Attribute') -> save($data);
			$res !== false ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'修改失败！');
		}else{
			$this -> ajaxReturnData(0,'请求方式错误');
		}
		
	}

	public function del(){
		$attr_id = I('get.id');
		$res = M('Attribute')->delete($attr_id);
		$res ? $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'删除失败！');
	}

	
}