<?php 
namespace Admin\Controller;
use Think\Controller;

class SystemController extends BaseController {

	public function setting() {
        if(session('grade') != 0 || session('grade') == null){
            $this->error("没有权限访问");
        }
		$this->display();
	}

    public function deldb(){
        $where['id'] = array('neq',0);
        $collectdate = M('collectdate')->where($where)->delete();
        $logistics = M('logistics')->where($where)->delete();
        $logisticsdetails = M('logisticsdetails')->where($where)->delete();
        if($collectdate || $logistics || $logisticsdetails){
            $this->success('清除成功');
        }else{
            $this->error('清除失败');
        }
    }
}



 ?>