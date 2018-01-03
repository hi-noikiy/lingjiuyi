<?php
namespace Admin\Controller;
class CategoryController extends BaseController{
	//首页
	public function index(){
		if(IS_AJAX){
			$cate = D('Category') -> select();
			$cate = getTree($cate);
			$data = ['data' => $cate];
			$this -> ajaxReturn($data);
		}else{
			$this->display();
		}

	}

	//ajax获取分类的三级联动
	public function get_cate(){
		//接收id
		$id    = I('post.id','','intval');
		$cate  = D('Category') -> where("pid = $id") -> select();//分类
		$pid   = D('Category') -> where("id  = $id") -> getField('pid');
		if($pid == 0){
			$attrs = D('Attribute') -> where("cate_id = $id") -> select();
			foreach($attrs as $k => $v){
				$attrs[$k]['attr_values'] = explode(',',$attrs[$k]['attr_values']);
			}
			session_start();
			$_SESSION['attrs'] = $attrs;//将数据存储在session中
		}else{
			$attrs = $_SESSION['attrs'];
		}
		$cate ? $this -> ajaxReturnSuccess(compact('cate','attrs')) : $this -> ajaxReturnData(0,'只有三级分类哦！');
	}


	public function add(){
		if(IS_POST){
			$data = I('post.');
			// dump($data);
			$res = M('Category')->add($data);
			if($res){
				$this->success('添加成功!',U('Admin/Category/cate_list'));
			}else{
				$this->error('添加失败');
			}
		}else{
			$cate = M('Category')->select();
			$cate = getTree($cate);
			$this->assign('cate',$cate);
			$this->display();
		}
		
	}

	//编辑
	public function edit(){
		//保存修改数据
		if(IS_POST){
			$data = I('post.');
			$data['id'] == $data['pid'] ? $this -> ajaxReturnData(0,'不能选择自己作为父级') : true;
			$pid = D('Category') -> where(['id' => $data['id']]) -> getField('pid');
			($pid == 0 && $data['pid'] != 0) ? $this -> ajaxReturnData(0,'顶部分类不能修改父级分类') : true;
			$ids = D('Category') -> field('id') -> where(['pid' => $data['id']]) -> select();
			in_array($data['pid'],$ids[0]) ? $this -> ajaxReturnData(0,'不能选择自己的子级作为父级分类') : true;
			$res = D('Category')->save($data);
			if($res !== false){
				$this -> ajaxReturnData(10000,'修改成功');
			}else{
				$this -> ajaxReturnData(0,'修改失败！');
			}

		}elseif(IS_GET && IS_AJAX){
			//展示需要编辑的内容
			$id       = I('get.id');
			$cate_one = D('Category')->find($id);
			if($cate_one['pid'] != 0){
				$p_cate_name1 = D('Category')-> field('id,cate_name,pid')-> where(['id' => $cate_one['pid']])-> find();
				$cate_one['p1_id']   = $p_cate_name1['id'];
				$cate_one['p1_name'] = $p_cate_name1['cate_name'];
				if($p_cate_name1['pid'] != 0){
					$p_cate_name2 = D('Category')-> field('id,cate_name,pid')-> where(['id' => $p_cate_name1['pid']])-> find();
					$cate_one['p2_id']   = $p_cate_name2['id'];
					$cate_one['p2_name'] = $p_cate_name2['cate_name'];
				}
			}
			$catelist = D('Category') -> where('pid = 0') -> select();

			empty($cate_one) ? $this -> ajaxReturnData(0,'没有数据！') : $this -> ajaxReturnSuccess(compact('cate_one','catelist'));
		}
		
	}

	//测试方法，假数据
	public function get_test(){
		$id = I('get.id','','intval');
		empty($id) ? $this -> ajaxReturnData(0,'参数错误') : true;

	}

	public function del(){
		$id = I('get.id');
		$res = M('Category')->delete($id);
		if($res){
			$this->success('删除成功!',U('Admin/Category/cate_list'));
		}else{
			$this->error('删除失败!');
		}
	}
}