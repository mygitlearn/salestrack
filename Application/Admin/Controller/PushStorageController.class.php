<?php
/*对商品的入库进行管理
 * @author chengpenghui
 *
 * @date 2015/6/27
 *
 * @Copyright 三月工作室
 *
 * */

namespace Admin\Controller;
use Admin\Controller;
use Admin\Common\GLogger;

class PushStorageController extends BaseController{
      /*
       * 商品入库
       *
       *
       * */
      public function index(){
          $pushmodel = M('goodstype');
          $list = $pushmodel->where("status=0")->select();
          $this->assign('list',$list);
          $this->display();
      }


    /*当商品数据上传完时提交(Core code do not delete)
     *
     *@author chengpenghui
     *
     *@param
     *
     * */
    public function submitpush(){
        $model = M('collectdate');
        if(isset($_FILES['filename'])){
            $upload = new \Think\Upload();
            $upload->maxSize   =     3145728 ;
            $upload->exts      =     array('txt', 'doc', 'docx', 'exl');
            $upload->savePath  =      'Doc/';
            $info   =   $upload->upload();
            if(!$info) {
              $this->error("上传文件不能为空！");
            }else{
                foreach($info as $file){
                    $url = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__."/Uploads/".$file['savepath'].$file['savename'];
                }
            }
        }
        $content= file_get_contents($url);//获取txt文件的内容并把把整个文件读入一个字符串中。
        $str = preg_replace("/\,/", "\r\n", $content); //用换行符代替逗号
        $array = explode("\r\n", $str);
        $s = (int)(count($array)/2);
         for($i=0;$i<count($array);$i++){
                if(strlen($array[$i]) == 14){ //获取大包数据并用数组$bigcode盛放
                    $bigcode[] = $array[$i];
                }elseif(strlen($array[$i]) == 12){//获取小包数据并用$barcode盛放
                    $barcode[] = $array[$i];
                }
            }
            for($j=0;$j<$s;$j++){
                $userid = (int)$_SESSION['id'];
                $status = 0;
                $data['goodstype_id'] = (int)I('post.goodid');
                $listbig = $bigcode[$j];  //循环遍历出小包的数据
                $listsmall = $barcode[$j]; //循环遍历出打包的数据
                $dataList[] = array('userid'=>$userid,'goodstype_id'=>$data['goodstype_id'],'status'=>$status,'barcode'=>$listsmall,'bigcode'=>$listbig);
            }
            $addto = $model->addAll($dataList);//进行批量插入
            if($addto){
                $this->success('入库成功！');
            }else{
                $this->error('入库失败！');
            }
        }
    /*
     *用表来显示所上传的商品信息并在index表中显示
     *
     * @author chengpenghui
     *
     * @param
     * */
    public function showstoragelist(){

    }




}




?>