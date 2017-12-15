<?php
namespace Admin\Controller;
class TypeController extends CommonController{
	//商品类型列表页
	public function type_list(){
		//获取商品类型数据
		$type = M('Type')->select();
		$this->assign('type',$type);
		$this->display();
	}

	//商品类型添加页面
	public function type_add(){
		if(IS_POST){
			//接收数据
			$data = I('post.');
			if(empty($data['type_name'])){
				$this->error('必填项不能为空!');
			}
			//保存数据到数据表
			$res = M('Type')->add($data);
			if($res){
				$this->success('添加成功!',U('Admin/Type/type_list'));
			}else{
				$this->error('添加失败!');
			}
		}else{
			$this->display();
		}
		
	}

	public function type_edit(){
		if(IS_POST){
			$type = I('post.');
			// dump($type);die;
			$res = M('Type')->save($type);
			if($res){
				$this->success("修改成功!",U('Admin/Type/type_list'));
			}else{
				$this->error("修改失败!");
			}
		}else{
			$type_id = I('get.id');
			// dump($type_id);die;
			$type = M('Type')->where("type_id = $type_id")->find();
			$this->assign('type',$type);
			$this->display();
		}
		
	}

	public function type_del(){
		$type_id = I('get.type_id');
		$res = M('Type')->delete($type_id);
		if($res != false){
			$this->success('删除成功!',U('Admin/Type/type_list'));
		}else{
			$this->error('删除失败!');
		}
	}
}