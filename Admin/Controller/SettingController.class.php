<?php
namespace Admin\Controller;


class SettingController extends BaseController{

    //添加设置
    public function add(){
        $data = I('post.');
        $model = D('Setting');
        if(!$model -> create()){
            $msg = $model -> getError();
            $this -> ajaxReturnData(0,$msg);
        }else{
            $res = M('Setting') -> add($data);
            $res ?  $this -> ajaxReturnSuccess() : $this -> ajaxReturnData(0,'添加失败！');
        }
    }

    //修改设置
    public function edit(){
        $model    = D('Setting');
        $name     = array_keys($_POST);
        $content  = array_values($_POST);
        foreach($name as $key => $value){
            $id = $model -> where("name = '$value'") -> find();
            empty($id) ? $this -> ajaxReturnData(0,'需要修改的名称错误！')   : true;

            $data['content'] = $content[$key];
            $res = M('Setting') -> where("name = '$value'") -> save($data);
            $res !== false ?  true : $this -> ajaxReturnData(0,'添加失败！');
        }
        unset($key,$value,$k,$v);
        $this -> ajaxReturnSuccess();
    }


}