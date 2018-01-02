<?php  
namespace Admin\Controller;

class NewsController extends BaseController {
    //定义uploadify方法
    public function uploadify() {
        if($_FILES['Filedata']['size'] > 0) {
            //第一步：实例化Upload上传类
            $upload = new \Think\Upload();
            //第二步：设置相关参数
            $upload->maxSize = 2097152; //上传文件的最大值为2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg'); //上传文件格式
            $upload->rootPath = './Uploads/images/'; //上传文件目录
            $upload->subName = array('date', 'Ymd'); //子目录命名规则:20160705
            //第三步：调用upload方法实现文件上传
            if(!$info = $upload->upload()) {
                //第四步：输出错误信息
                echo $upload->getError();
                exit;
            } else {
                $big = $info['Filedata']['savepath'].$info['Filedata']['savename'];

                //获取原图像
                $file = './Uploads/images/'.$info['Filedata']['savepath'].$info['Filedata']['savename'];
                //第一步：实例化图像处理类
                $img = new \Think\Image();
                //第二步：使用open方法打开图像
                $img->open($file);
                //第三步：使用thumb方法生成缩略图
                $img->thumb(144,90,2);  //使用缩略图补白
                //第四步：使用thumb方法保存图像
                $small = $info['Filedata']['savepath'].'small_'.$info['Filedata']['savename'];
                $img->save('./Uploads/images/'.$small);

                echo $big.'|'.$small;
                exit;
            }
        }
    }

    public function index() {
        if(IS_AJAX){
            $news = D('News');
            $list = array();
            foreach($news->select() as $row) {
                $row['addtime'] = date('Y-m-d H:i:s',$row['addtime']);
                $list[] = $row;
            }
            $data = array(
                'data'=>$list
            );
            $this->ajaxReturn($data);
        }else{
            $this->display();
        }
    }

    public function add() {
        $this->display();
    }

    public function addok() {
        if(IS_POST) {
            $news = D('News');
            if($data = $news->create()) {
                $data['addtime'] = time();
                if($news->add($data)) {
                    $this->success('添加成功');
                } else {
                    $this->error($news->getError());
                }
            }
        }
    }
}
