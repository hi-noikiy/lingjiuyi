<?php
    namespace Admin\Controller;
    use Think\Controller;
    use Think\Upload;
    require_once './Application/Tools/Qiniu/autoload.php';
    use Qiniu\Auth;
    use Qiniu\Processing\PersistentFop;
    use Qiniu\Storage\BucketManager;

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
                    D('Setting') -> setting_cache();//初始化网站配置
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

        public function get_qiniuTocken(){
            $accessKey = "6R9rx5zfafyr5kgeYYskF111GB8Zs2QHlsRfTlyq";
            $secretKey = "XGcurjXepkLZrcjYLeO6C2QConfwacBCUmO9yXLh";
            $bucket    = "doudou";
            $auth      = new Auth($accessKey, $secretKey);
            $upToken   = $auth->uploadToken($bucket, null, 3600); //获取token
            return $upToken;
        }

        public function upload_img(){

            $type      = $_POST['type'] ? $_POST['type'] : false;
            $setting   = C('UPLOAD_QINIU');

            $accessKey = $setting['driverConfig']['accessKey'];
            $secretKey = $setting['driverConfig']['secretKey'];
            $bucket    = $setting['driverConfig']['bucket'];
            $auth      = new Auth($accessKey, $secretKey);
            $config    = new \Qiniu\Config();
            if($type == 'base64'){
                //上传头像
                $image     = $_POST['data'];
                if (strstr($image,",")){
                    $image = explode(',',$image);
                    $image = $image[1];
                }//去除base64中需要的部分

                $upToken   = $auth->uploadToken($bucket, null, 3600); //获取token
                $result    = request_by_curl('http://upload.qiniu.com/putb64/-1',$image,$upToken);//上传七牛云
                $info      = json_decode($result,true);

                if(array_key_exists('error',$info) === true){
                    $data['msg']  = '上传图片失败！';
                    $data['code'] = 0;
                    echo json_encode($data);die;
                }
            }else{
                //上传商品图片
                $Upload  = new \Think\Upload($setting);
                $info    = $Upload -> upload($_FILES);
            }
            if($type == 'base64'){
                //上传头像
                if($info['error']){
                    $data['msg']  = '上传图片失败！';
                    $data['code'] = 0;
                }else{
                    $admin['userid']      = session('userid');
                    $admin['header_img']  = 'http://'.$setting['driverConfig']['domain'].'/'.$info['key'];
                    $header_img           = D('Admin') -> where(['userid' => $admin['userid']]) -> getField('header_img');
                    $key                  = substr($header_img,strrpos($header_img,'/') + 1);

                    $bucketManager = new BucketManager($auth, $config);
                    $err = $bucketManager -> delete($bucket, $key);
                    $err ? $this -> ajaxReturnData(0,'删除源文件失败') : true;

                    $res = D('Admin') -> save($admin);
                    session('header_img',$admin['header_img']);
                    $res !== false ? true : $this -> ajaxReturnData(0,'保存头像失败');
                    $data['msg']  = '上传图片成功！';
                    $data['code'] = 10000;
                    $data['info'] = $admin['header_img'];
                }
            }else{
                //上传商品图片
                if(empty($info)){
                    $data['msg']  = '上传图片失败！';
                    $data['code'] = 0;
                }else{
                    $data['msg']  = '上传图片成功！';
                    $data['code'] = 10000;
                    $data['info'] = $info['upload_img']['url'] ? $info['upload_img']['url'] :
                        ($info['shop_logo']['url'] ? $info['shop_logo']['url'] :
                            ($info['shop_logo_web']['url'] ? $info['shop_logo_web']['url'] :
                                ($info['upload_img2']['url'] ? $info['upload_img2']['url'] : $info['shop_header_img']['url'])));
                }
            }

            echo json_encode($data);die;

        }

        public function upload_video(){
            $setting = C('UPLOAD_VIDEO_QINIU');
            $upload = new Upload($setting);// 实例化上传类
            $info = $upload->upload();
            ob_end_clean();
            if(empty($info)){
                $data['msg']  = '上传视频失败！';
                $data['code'] = 0;
            }else{
                $data['msg']  = '上传视频成功！';
                $data['code'] = 10000;
                $data['info'] = $info['upload_video']['url'];
            }
            echo json_encode($data);die;
        }


        public function notify(){
            echo '转码成功！';
        }


    }
?>