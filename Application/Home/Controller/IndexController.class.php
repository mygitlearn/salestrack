<?php
namespace Home\Controller;
use Think\Controller;
/*首页
 *
 *@author chengpenghui
 *
 *@time 2015/7/6
 *
 *@param
 * */
class IndexController extends BaseController {
    /*
     *销售首页界面
     *
     *@author chengpenghui
     *
     *
     * */
    public function index(){
        $module = M('advantage');
        $list = $module->where('type = 1 and status = 0 ')->select();
        $listvideo = $module->where('type = 0 and status = 0 ')->limit(1)->select();
        $this->assign('list',$list);
        $this->assign('listvideo',$listvideo);
        $this->display();
    }

    /*
     * 提交所搜集的数据
     *
     * @author chengpenghui
     *
     * @param
     *
     * */
    public function submitdetail(){
        $model = M('collectdate');
        $userid = (int)$_SESSION['uid']; //得到用户的session然后根据session找出用户所注册的代理商
        $content = I('post.information');
        if(empty($content))$this->error("数据内容不能为空！");
        $array = explode("\r\n",$content);
        $s= count($array);
        for ($j=0; $j<$s; $j++) {
            $s1=$array[$j];
            if(strlen($s1)==12){
               $status = $model->where("barcode = $s1")->getField('status');
                if($status == 1){
                    $this->error("你所扫描的商品有已经销售的！");
                }else{
                    $res =$model->where("barcode = $s1")->save(array('status'=>1));//找到与小袋条形码一样的把其状态改为销售
                }
            }elseif(strlen($s1)==14){
                $bigres=$model->where("bigcode = $s1")->save(array('status'=>1));//找到与大袋条形码一致的并把其状态改为售出
            }
        }
        if($res || $bigres || ($res && $bigres)){
            $this->success('销售成功！','index.php?s=/Home/Index/index');
        }else{
            $this->error("销售失败！");
        }
    }


    /*
     * 批量发货
     *
     * @author chengpenghui
     *
     * @param
     *
     * */
    public function bulkshipment(){
        $popmodel = M('user');
        $userid = (int)$_SESSION['uid']; //得到用户的session然后根据session找出用户所注册的代理商
        $list = $popmodel->where("fatherid = $userid")->select();
        $this->assign('list',$list);
        $this->display();
    }

    /*提交发货请求
     *
     * @author chengpenghui
     * @param
     *
     * */
    public function submitbulkshipment(){
        $model = M('logisticsdetails');
        $return =M('logistics'); //用于产生物流详情
        $collectdate= M('collectdate');
        $data['shipperid'] = (int)$_SESSION['uid'];//得到用户的session然后根据session找出用户所注册的代理商
        $data['receiverid'] = I('post.fatherid');
        $data['logisticstype'] = 0;
        $data['status'] = 0;
        $data['NO'] = time();
        $data['time'] = time();
        $logistics = $return->data($data)->add();
        if($logistics){
            if(isset($_FILES['filename'])){
                $upload = new \Think\Upload();
                $upload->maxSize   =     3145728 ;
                $upload->exts      =     array('txt', 'doc', 'docx', 'exl');
                $upload->savePath  =      'Doc/';
                $info   =   $upload->upload();
                if(!$info) {
                    $content = I('post.datecode');
                    if(empty($content))$this->error("数据内容不能为空！");
                }else{
                    foreach($info as $file){
                        $url = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__."/Uploads/".$file['savepath'].$file['savename'];
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
                        $this->error("你所提交的数据，不存在！");

                    }

                }elseif(strlen($s1)==14){
                    $rescollectdate = $collectdate->where("bigcode = $s1")->select();
                    if(!$rescollectdate){
                        $logstdelete = $return->where("id = $logistics")->delete();
                        $this->error("你所提交的数据，不存在！");
                    }
                }else{
                    $logstdelete = $return->where("id = $logistics")->delete();
                    $this->error("你所提交的数据出现错误");
                }
                $sendid = (int)$logistics;
                $time = time();
                $dataList[] = array('sendid'=>$sendid,'time'=>$time,'datecode'=>$s1);
            }
            $logisticsdetails =$model->addAll($dataList);
            $this->success("发货成功！");
        }else{
            $this->error("发货失败");
        }

    }

}