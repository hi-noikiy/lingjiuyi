<?php
namespace Admin\Controller;
require_once './Application/Tools/Qiniu/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
class ShopController extends BaseController
{
    public function index()
    {
        if(IS_AJAX){
            $shop = D('Shop') -> select();
            $data = ['data' => $shop];
            $this -> ajaxReturn($data);
        }else{
            $this -> display();
        }

    }

    public function edit(){
        if(IS_POST){
            $data = I('post.');
            if(isset($data['shop_logo'])){
                $shop_logo     = D('Shop') -> where(['id' => $data['id']]) -> getField('shop_logo');//删除原图片
                $setting       = C('UPLOAD_QINIU');
                $key           = substr($shop_logo,strrpos($shop_logo,'/') + 1);
                $bucket        = $setting['driverConfig']['bucket'];//资源所在空间
                $secretKey     = $setting['driverConfig']['secretKey'];//资源所在空间
                $accessKey     = $setting['driverConfig']['accessKey'];//资源所在空间
                $auth          = new Auth($accessKey,$secretKey);
                $config        = new \Qiniu\Config();
                $bucketManager = new BucketManager($auth, $config);
                $err = $bucketManager -> delete($bucket, $key);
                $err ? $this -> ajaxReturnData(0,'删除源文件失败') : true;
            }

            $res = D('Shop') -> save($data);
            $res !== false ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'修改失败！');

        }else{
            $this -> ajaxReturnData(0,'访问方式错误');
        }
    }

    public function del(){
        $id  = I('post.id','','intval');
        $res = D('Shop') -> delete($id);
        $res ? $this -> ajaxReturnData() : $this -> ajaxReturnData(0,'删除失败！');
    }

}