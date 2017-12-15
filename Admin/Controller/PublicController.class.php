<?php
    namespace Admin\Controller;
    use Think\Controller;
    use Think\Upload;

    class PublicController extends Controller {

        protected function ajaxReturnData($code = 10000, $msg = 'success',$info = [])
        {
            //设置返回数据格式
            $data = array(
                'code' => $code,
                'msg'  => $msg,
                'info' => $info,
            );
            //返回数据
            $this->ajaxReturn($data);
        }

        public function login() {
            $this->display('Public/login');
        }

        public function check() {
            if(IS_POST) {
                $username = I('post.username');
                $password = I('post.password');
                if(empty($username) || empty($password)) {
                    $this->error('用户名或密码不能为空');
                }
                $adminmodel = D('Admin');
                //调用模型里面的login方法
                if($adminmodel->login($username,$password)) {
                    $this->success('登录成功',U('Index/index'));exit;
                } else {
                    $this->error('用户名或密码错误');
                }
            } else {
                $this->error('非法请求');
            }
        }
        public function logout() {
            //删除指定session
            session('username',null);
            session('userid',null);
            //删除所有session
            session(null);
            $this->success('退出成功','login');
        }

        public function upload_img(){

            $setting = C('UPLOAD_QINIU');
            $Upload  = new \Think\Upload($setting);
            $info    = $Upload -> upload($_FILES);
            if(empty($info)){
                $data['msg']  = '上传图片失败！';
                $data['code'] = 0;
            }else{
                $data['msg']  = '上传图片成功！';
                $data['code'] = 10000;
                $data['info'] = $info['upload_img']['url'];
            }

            echo json_encode($data);die;



        }
    }
?>