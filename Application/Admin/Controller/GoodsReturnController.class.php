<?php
namespace Admin\Controller;
use Think\Controller;

/**
 *@author chengpenghui
 *
 *@time 2015/7/2
 *
 *
 * 退货控制层
 * */
class GoodsReturnController extends BaseController{
	
	public function index(){
		$model = M('user');
		$userid = (int)$_SESSION['id']; //得到用户的session然后根据session找出用户所注册的代理商
		$fatherid=(int)$model->where("id=$userid")->getField('fatherid');
		$list = $model->where("id = $fatherid")->select();
		$this->assign('list',$list);
		$this->display();
	}


	/*
	 * 提交所退货
	 * @author chengpenghui
	 * @param
	 *
	 *
	 * */
	public function returngood(){
		$model = M('logisticsdetails');
		$return =M('logistics'); //用于产生物流详情
		$collectdate= M('collectdate');
		$data['shipperid'] = (int)$_SESSION['id'];//得到用户的session然后根据session找出用户所注册的代理商
		$data['receiverid'] = I('post.fatherid');
		$data['reason'] = I('post.reason');
		if(empty($data['reason']))$this->error("原因不能为空！");
		$data['logisticstype'] = 1;
		$data['status'] = 0;
		$data['NO'] = time();
		$data['time'] = time();
		$logistics = $return->data($data)->add();
		if($logistics){
			if(isset($_FILES['filename'])){
				$upload = new \Think\Upload();
				$upload->maxSize   =     3145728 ;
				$upload->exts      =     array('txt');
				$upload->savePath  =      'Doc/';
				$info   =   $upload->upload();
				if(!$info) {
					$content = I('post.datecode');
					if(empty($content)){
						$logstdelete = $return->where("id = $logistics")->delete();
						$this->error("数据内容不能为空！");
					}
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
						$this->error("你所提交的数据，有不存在的！");

					}
					$rescollectdate = $collectdate->where("barcode = $s1")->getField('userid');
					if($rescollectdate != (int)$_SESSION['id']){
						$logstdelete = $return->where("id = $logistics")->delete();
						$this->error("你所提交的数据，有不属于你的商品!");
					}
				}elseif(strlen($s1)==14){
					$rescollectdate = $collectdate->where("bigcode = $s1")->select();
					if(!$rescollectdate){
						$logstdelete = $return->where("id = $logistics")->delete();
						$this->error("你所提交的数据，有不存在的！");
					}
				}
				$sendid = (int)$logistics;
				$time = time();
				$dataList[] = array('sendid'=>$sendid,'time'=>$time,'datecode'=>$s1);
			}
			 $logisticsdetails =$model->addAll($dataList);
			  $this->success('退货成功','index');
		}else{
			$this->error("退货失败");
		}
	}


}
?>