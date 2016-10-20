<?php
/*对商品的出库进行管理
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

class PopStorageController extends BaseController{
    /*发货管理
     * 获取用户的ID 根据用户的ID找出fatherID一样测用户
     *
     *
     * */
    public function index(){
        $popmodel = M('user');
        $userid = (int)$_SESSION['id']; //得到用户的session然后根据session找出用户所注册的代理商
        $list = $popmodel->where("fatherid = $userid")->select();
        $this->assign('list',$list);
        $this->display();
    }

    public function sentGoods(){
        $popmodel = M('user');
        $userid = (int)$_SESSION['id']; //得到用户的session然后根据session找出用户所注册的代理商
        $list = $popmodel->where("fatherid = $userid")->select();
        $this->assign('list',$list);

        $this->display('index');
    }

    /*
     * 提交发货请求
     *
     * @author chengpenghui
     *
     * @param
     *
     * */
    public function submitposstroge(){
        $model = M('logisticsdetails');
        $return =M('logistics'); //用于产生物流详情
        $collectdate= M('collectdate');
        $data['shipperid'] = (int)$_SESSION['id']; //得到用户的session然后根据session找出用户所注册的代理商
        $data['receiverid'] = I('post.userid');
        $data['logisticstype'] = 0;//物流状态为发货
        $data['status'] = 0;
        $data['NO'] = time();
        $data['time'] = time();
        $logistics = $return->data($data)->add();
        if($logistics){
            if(isset($_FILES['filename'])) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 3145728;// 设置附件上传大小
                $upload->exts = array('txt');// 设置附件上传类型
                $upload->savePath = 'Doc/'; // 设置附件上传目录// 上传文件
                $info = $upload->upload();
                if (!$info) {// 上传错误提示错误信息
                    $content = I('post.information');
                    if(empty($content)){
                        $logstdelete = $return->where("id = $logistics")->delete();
                        $this->error("数据内容不能为空！");
                    }
                }else {// 上传成功 获取上传文件信息
                    foreach ($info as $file) {
                        $url = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__."/Uploads/".$file['savepath'].$file['savename'];;
                    }
                    $content= file_get_contents($url);//获取txt文件的内容并把把整个文件读入一个字符串中。
                }
            }
            $array = explode("\r\n", $content);
            $s= count($array);
            for ($j=0; $j<$s; $j++) {
                $s1=$array[$j];
                if(strlen($s1)==12){
                    $rescollectdate = $collectdate->where("barcode = $s1")->find();
                    if(!$rescollectdate){
                        $logstdelete = $return->where("id = $logistics")->delete();
                        $this->error("你所提交的数据，有不存在！");
                    }
                    $rescollect = $collectdate->where("barcode = $s1")->getField('userid');
                    if($rescollect != (int)$_SESSION['id']){
                        $logstdelete = $return->where("id = $logistics")->delete();
                        $this->error("你所提交的数据，有不属于你的商品!");
                    }
                }elseif(strlen($s1)==14){
                    $rescollectdate = $collectdate->where("bigcode = $s1")->select();
                    if(!$rescollectdate){
                        $logstdelete = $return->where("id = $logistics")->delete();
                         $this->error("你所提交的数据，有不存在的！");
                    }
                }else{
                    $logstdelete = $return->where("id = $logistics")->delete();
                    $this->error("你所提交的数据，有不存在的！");
                }
                $sendid = (int)$logistics;
                $time = time();
                $dataList[] = array('sendid'=>$sendid,'time'=>$time,'datecode'=>$s1);
            }
            $logisticsdetails =$model->addAll($dataList);
             $this->success('发货成功!','index');
        }else{
             $this->error("发货失败");
        }
    }

    /*
     * 销售管理
     * @author chengpenghui
     * @param
     *
     *
     */
    public function sellGoods(){
        $this->display();
    }

    /*
     * 视频显示
     * @author chengpenghui
     *
     * @param
     *
     * */
    public function videolist(){
        $module = M('advantage');
        $list = $module->where('type = 0 and status = 0 ')->limit(1)->select();
        $this->assign('list',$list);
        $this->display();
    }

    /*
     * 提交所获取的内容
     * @author chengpenghui
     * @param
     *
     *
     */

    public function submitsellgoods(){
        $model = M('collectdate');
         $userid = (int)$_SESSION['id']; //得到用户的session然后根据session找出用户所注册的代理商
         $content = I('post.information');
         if(empty($content)) $this->error("数据内容不能为空！");
         $array = explode("\r\n",$content);
         $s= count($array);
        for ($j=0; $j<$s; $j++) {
                $s1=$array[$j];
                if(strlen($s1)==12){
                    $status = $model->where("barcode = $s1")->getField('status'); //找出商品是否已经销售
                    if($status == 1){
                        $this->error("你所扫描的商品有已经销售的！");
                    }else{
                        $res =$model->where("barcode = $s1")->save(array('status'=>1));//找到与小袋条形码一样的把其状态改为销售
                    }
                }elseif(strlen($s1)==14){
                    $bigres=$model->where("bigcode = $s1")->save(array('status'=>1));//找到与大袋条形码一致的并把其状态改为售出
                }else{
                    $this->error("你提交的数据不存在！");
                }
            }
            if($res || $bigres || ($res && $bigres)){
                $this->success("销售成功！");
                $this->redirect("PopStorage/sellGoods");
            }else{
                $this->error("销售失败！");
            }
    }
    /*
     * 广告展示（展示启用的图片广告）
     *
     * @author chengpenghui
     *
     * @param
     *
     * */
    public function advantagelist(){
        $module = M('advantage');
        $list = $module->where('type = 1 and status = 0 ')->select();
        $this->assign('list',$list);
        $this->display();
    }


}




?>