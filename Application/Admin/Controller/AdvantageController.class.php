<?php 
namespace Admin\Controller;
use Admin\Controller;
/**
 *上传广告（视频或者图片）
 *@author  chengpenghui
 *
 *
 **/
class AdvantageController extends BaseController {


	public function index() {

		$advantage = M('advantage');
	    $advantagelist = $advantage->where('status !=2')->count();
		$Page = new  \Think\Page($advantagelist,10);
		$show = $Page->show();// 分页显示输出
		$list = $advantage->where('status !=2')->order('time desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select();
		$this->assign('_list',$list);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}

	/*
	 *上传广告
	 *
	 *
	 *
	 * **/
	public function uploadfile(){
		$this->meta_title = '上传文件';
		$this->display();
	}
    /*
     * 提交要上传的广告
     *@author chengpenghui
     *
     *
     * **/
	public function submitupload(){
		$data ['title'] = I('post.title');
		if(empty($data ['title']))$this->error("标题不能为空！");
		$data['userid'] = (int)$_SESSION['id'];
		$data['info'] = I('post.information');
		if(empty($data['info']))$this->error("文件描述不能为空！");
		$data['time'] = time();
		$data['type'] = I('post.type');
		if(isset($_FILES['filename'])){
				$upload = new \Think\Upload();// 实例化上传类
			if($data['type']==0){ //判断要上传的文件类型是不是视频
				$data['status'] =1;
				$upload->maxSize   =     52428800 ;// 设置附件上传大小
				$upload->exts      =     array('flv', 'wmv', 'rm', 'rmvb', 'avi', 'mpeg','mp4');// 设置附件上传类型
				$upload->savePath  =      'Video/'; // 设置附件上传目录
			}else{//要上传的文件类型为图片
				$data['status'] =0;
				$upload->maxSize   =     3145728 ;// 设置附件上传大小
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
				$upload->savePath  =      'Picture/'; // 设置附件上传目录
			}
            // 上传单个文件
			$info   =   $upload->upload();
			if(!$info) { //上传错误提示错误信息
				/*$this->error($upload->getError());*/
			}else{ //上传成功，获取上传文件的信息
				foreach($info as $file){
					$url = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__."/Uploads/".$file['savepath'].$file['savename'];
				}
			}
		}
		$data['url']=$url;
		$advantage = M('advantage');
        $res = $advantage->data($data)->add();
		if(!$res){
			$this->success('上传成功！','index.php?s=/Admin/Advantage/index');
		}else{
			$this->error("上传失败！");
		}
	}
	/*
	 * 启用状态（禁用）
	 *
	 * @author chengpenghui
	 * @param id
	 *
	 * */
     public function activated(){
		 $id=$_GET['id'];
		 $model= M('advantage');
		 $data= $model->find($id);
		 if($data){
			 $rela = $model->where("id=$id")->save(array('status'=>1));
			 if($rela){
				 $this->success('禁用成功');
				 $this->redirect('Advantage/index');
			 }else{
				 $this->error('禁用失败');
			 }
		  }
	 }

	/*
	 *
	 * 禁用状态（启用）
	 *
	 * @author chengpenghui
	 *
	 * @param
	 * */
    public function banned(){
		$id=$_GET['id'];
		$model= M('advantage');
		$data= $model->find($id);
		if($data){
			$rela = $model->where("id=$id")->save(array('status'=>0));
			if($rela){
				$this->success('启用成功');
				$this->redirect('Advantage/index');
			}else{
				$this->error('启用失败');
			}
		}
	}

	/*
	 * 删除广告
	 *
	 * @param
	 *
	 * @author chengpenghui
	 * */
	public function deleted(){
		$id=$_GET['id'];
		$model= M('advantage');
		$data= $model->find($id);
		if($data){
			$rela = $model->where("id=$id")->save(array('status'=>2));
			if($rela){
				$this->success('删除成功');
				$this->redirect('Advantage/index');
			}else{
				$this->error('删除失败');
			}
		}
	}

	/*
	 *批量删除
	 *
	 * @author chengpenghui
	 *
	 * @param
 	 * */
     public function batchdelete($ids = 0){ //初始化ID数组
		 empty($ids) && $this->error('参数错误！');
		 if(is_array($ids)){
			 $map['id'] = array('in', $ids);
		 }elseif (is_numeric($ids)){
			 $map['id'] = $ids;
		 }
		 $res = M('advantage')->where($map)->save(array('status'=>2));
		 if($res !== false){
			 $this->success('删除成功！');
			 $this->redirect('Advantage/index');
		 }else {
			 $this->error('删除失败！');
		 }
	 }

	/*
	 * 对所上传的广告进行编辑(对所上传的广告进行编辑)
	 *
	 * @author chengpenghui
	 *
	 * @param
	 * (根据type的不同对所要编辑的视频或图片的显示格式也不同)
	 *
	 * */
	public function eduitadvantage(){
		$id = $_GET['id'];
		$m = M('advantage');
		$edit= $m->find($id);
		$this->assign('data',$edit);
		$this->display();
	}
	/*
	 * 提交修改的文件
     * @author chengpenghui
	 *
	 * @param
	 *
	 * */
	public function submiteduit(){
		$data['id'] = I('post.id');
		$data ['title'] = I('post.title');
		$data['userid'] = (int)$_SESSION['id'];
		$data['info'] = I('post.information');

		$result = D('Admin/Advantage')->submiteduit($data);
		if($result == true){
			$this->success('修改成功');
			$this->redirect('Advantage/index');
		}elseif($result == false){
			$this->error('修改失败');
		}
	}

	/*
	 *点击查看所要浏览的视频或图片
	 *
	 *@author chengpengh
	 *
	 *@param
	 *
	 * */
     public function lookdetail(){
		 $id = $_GET['id'];
		 $m = M('advantage');
		 $edit= $m->find($id);
		 $last = $this->doc_show($id);
		 $arr['last_title'] = $last[0]['title'];
		 $arr['last_id'] = $last[0]['id'];
		 $arr['after_title'] = $last[1]['title'];
		 $arr['after_id'] = $last[1]['id'];
		 $this->assign('arr',$arr);
		 $this->assign('data',$edit);
		 $this->display();
	 }

	/*
	 * 显示上一个题目，或者下一个题目
	 *
	 * @author chengpenghui
	 * @param
	 *
	 *
	 * */
     public function doc_show($id){
		 $m=M('advantage');
		 $where['id'] = array('lt',$id);
		 $res1 = $m->where($where)->field('title, id')->find(); //下一篇
		 $where['id']  = array('gt',$id);
		 $res2 = $m->where($where)->field('title, id')->find();//上一篇
		 $arr[] = $res1;
		 $arr[] = $res2;
		 return $arr;
	 }

	/*
	 * 搜索按照制定的日期和指定的搜索范围进行搜索
	 *
	 * @author chengpenghui
	 *
	 * @param
	 *
	 * */

	public function rangesearch(){
		if(I("start_time") != null || I("start_time") != 0){
			$timestart = I("start_time")." 00:00:00";
			$timestart = strtotime($timestart);
		}else{
			$timestart = 0;
		}
		if(I("end_time") != null){
			$endtime = I("end_time")." 23:59:59";
			$endtime = strtotime($endtime);
		}else{
			$endtime = 2147483647;
		}
		$filetype = I('type');
		$filestatus = I('status');
		$model = M("advantage");
		if($filetype == 2 && $filestatus==3) {
			$count = $model->where("time >= $timestart  and time <= $endtime and status != 2")->count();
            $Page = new  \Think\Page($count,10,array('start_time'=> $timestart,'end_time'=>$endtime,'status'=>2));
			$show = $Page->show();// 分页显示输出
			$list = $model->where("time >= $timestart  and time <= $endtime and status != 2")
				->order('time desc')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
		  }elseif($filetype == 2 && $filestatus!=3){
			$count = $model->where("time >= $timestart  and time <= $endtime and status = $filestatus")->count();
			$page  = new \Think\Page($count,10);
			$show  = $page->show();// 分页显示输出
			$list = $model -> where("time >= $timestart  and time <= $endtime  and status = $filestatus")
                    ->limit($page->firstRow.','.$page->listRows)
                    ->order('time desc')
                    ->select();
            }elseif($filetype !=2 && $filestatus==3){
			$count = $model->where("type =$filetype and time >= $timestart  and time <= $endtime ")->count();
			$page  = new \Think\Page($count,10);
			$show  = $page->show();// 分页显示输出
                $list = $model -> where("time >= $timestart  and time <= $endtime and type =$filetype")
                    ->limit($page->firstRow.','.$page->listRows)
                    ->order('time desc')
                    ->select();
            }elseif($filetype!=2 && $filestatus!=3){
			$count = $model->where("type =$filetype and status=$filestatus and time >= $timestart  and time <= $endtime ")->count();
			$page  = new \Think\Page($count,10,array('start_time'=> $timestart,'end_time'=>$endtime,'status'=>$filestatus,'type'=>$filetype));
			$show  = $page->show();// 分页显示输出
				$list = $model -> where("status=$filestatus and time >= $timestart  and time <= $endtime and type =$filetype and status=$filestatus")
				->limit($page->firstRow.','.$page->listRows)
				->order('time desc')
				->select();
		}
		/*当选择所又类别所有状态时*/

		$this->assign('_list',$list);
		$this->assign('page',$show);// 赋值分页输出
		$this->display('index');
	}


  /*模糊条件查找（按照用户所输入的模糊的词汇进行查询）
   *
   *@author chengpenghui
   *
   *@param
   *
   * */
	public function fuzzysearch(){
		$model= M('advantage');
		$title  =   I('title');
		$map['status']  =   array('neq',2);
		if(empty($title))$this->error('你所要搜索的信息不存在！');
		if(is_numeric($title)){
			$map['id|title']=   array(intval($title),array('like','%'.$title.'%'),'_multi'=>true);
		}else{
			$map['title']    =   array('like', '%'.(string)$title.'%');
		}

		$Page = new \Think\Page($model->where($map)->count(),10); //每页10条

		$list = $model->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first', '<<'); //分页配置
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign('_list', $list);
		$this->assign('page', $show); // 赋值分页输出
		$this->display('index');
	}

}

 ?>