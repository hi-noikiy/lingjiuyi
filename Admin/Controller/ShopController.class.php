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

    public function add(){
        if(IS_POST){
            //店铺名称是否存在
            $shop_cate = I('post.shop_cate','','string') ? I('post.shop_cate','','string') : false;//店铺分类
            if(!empty($_FILES['shop_logo']) && $_FILES['shop_logo']['error'] == 0){
                //上传图片
                $file['shop_logo'] = $_FILES['shop_logo'];
                $setting = C('UPLOAD_QINIU');
                $Upload  = new \Think\Upload($setting);
                $info    = $Upload -> Upload($file);
                $info ? true : $this -> error('上传图片失败！');//判断是否上传成功
                $data['shop_logo'] = $info['shop_logo']['url'];
            }
            if(!empty($_FILES['shop_logo_web']) && $_FILES['shop_logo_web']['error'] == 0){
                //上传图片
                $file['shop_logo_web'] = $_FILES['shop_logo_web'];
                $setting = C('UPLOAD_QINIU');
                $Upload  = new \Think\Upload($setting);
                $info    = $Upload -> Upload($file);
                $info ? true : $this -> error('上传图片失败！');//判断是否上传成功
                $data['shop_logo_web'] = $info['shop_logo_web']['url'];
            }
            I('post.shop_name','','string') ?  $data['shop_name'] = I('post.shop_name','','string') : $this -> error('店铺名称不能为空');
            I('post.shop_desc','','string') ?  $data['shop_desc'] = I('post.shop_desc','','string') : true;
            $data['is_top'] = I('post.is_top','','intval');
            //$res = D('Shop') -> add($data);
            $res = 8;

            if(!empty($shop_cate)){
                $res ? true : $this -> error('添加店铺失败！');
                $shop_cate = explode(',',$shop_cate);
                foreach($shop_cate as $key => $value){
                    $addShopCate[$key]['shop_id'] = $res;
                    $addShopCate[$key]['shop_cate_name'] = $value;
                }
                $res = D('Shop_cate') -> addAll($addShopCate);
                $res ? $this -> success('添加店铺成功！') : $this -> error('添加店铺分类失败！');
            }else{
                $res ? $this -> success('添加店铺成功！') : $this -> error('添加店铺失败！');
            }

        }else{
            $this -> display();
        }
    }

    public function edit(){
        if(IS_POST){
            $data = I('post.');
            if(isset($data['shop_logo'])){
                $shop_logo     = D('Shop') -> where(['id' => $data['id']]) -> getField('shop_logo');//删除原图片
            }elseif(isset($data['shop_logo_web'])){
                $shop_logo_web     = D('Shop') -> where(['id' => $data['id']]) -> getField('shop_logo_web');//删除原图片
            }
            if(!empty($shop_logo) || !empty($shop_logo_web)){
                $logo = empty($shop_logo) ? $shop_logo_web : $shop_logo;
                $setting       = C('UPLOAD_QINIU');
                $key           = substr($logo,strrpos($logo,'/') + 1);
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