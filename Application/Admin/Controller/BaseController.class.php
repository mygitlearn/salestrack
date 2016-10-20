<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Common\GLogger;

class BaseController extends Controller {


	/**
	 * [初始化方法,查询导航和子导航]
	 * @return [type]
	 * @author FTD
	 */
    final public function _initialize(){
  		if(!session('id')){
            $this->redirect('Public/login');
        }
        $user_id = session('id');
        $base_model = D('Base');
        $top_menu = $base_model->get_top_menu();
        //GLogger::debug("top_menu",$top_menu);
        $top_menu = $this->updateMenu($user_id,$top_menu);
        //GLogger::debug("new_top_menu",$top_menu);
        $parent_id = '';
        $top_menu_count = count($top_menu);
        foreach ($top_menu as $k => $v) {
        	if($v['node_url'] ==  MODULE_NAME.'/'.CONTROLLER_NAME)
        	{
        		$parent_id = $v['id'];
        		break;
        	}
        }

        $top_menu = $this->change_url($top_menu);
        $child_menu = $base_model->get_child_menu($parent_id);
        $child_menu = $this->make_child_menu($child_menu);
        $this->assign('urlcolor',__MODULE__.'/'.CONTROLLER_NAME); //
        $this->assign('lefturlcolor',__ACTION__);
        $this->assign('top_menu',$top_menu);
        $this->assign('child_menu',$child_menu);
    }

    /**
     * [构造一个二维导航数组]
     * @param  [type] $child_menu [子导航]
     * @return [array]            
     */
    public function make_child_menu($child_menu){
        $parent_arr = array();   		//存放二级导航
		$children_arr = array();  		//存放三级导航
		$child_menu_count = count($child_menu);  //该一级导航下的所有子导航
		for($i = 0,$j = 0,$k = 0;$i < $child_menu_count;$i++){ 
			if($child_menu[$i]['depth'] == 2){
				$parent_arr[$j++] = $child_menu[$i];
			}else if($child_menu[$i]['depth'] == 3){
				$children_arr[$k++] = $child_menu[$i];
			}
		}
		$parent_count = count($parent_arr);  		//2级导航总数
		$children_count = count($children_arr); 	//3级导航总数
		for($m = 0,$p = 0;$m < $parent_count;$m++){
			for($n = 0;$n < $children_count ;$n++){
				if($parent_arr[$m]['id'] == substr($children_arr[$n]['id'],0,6)){
					$arr[$parent_arr[$m]['node_name']][$p++] = $children_arr[$n];
				}
			}
		}
		return $arr; 	 //该数组的一维键等于二级导航
    }

    /**
     * [改变主导航url,加上index]
     * @param  [type] $top_menu [description]
     * @return [type]           [description]
     */
   	public function change_url($top_menu){
   		$top_menu_count = count($top_menu);
   		for($i = 0 ;$i < $top_menu_count;$i++){
   			$top_menu[$i]['node_url'] = $top_menu[$i]['node_url'].'/index';
   		}

   		return $top_menu;
   	}
   	
   	final public function __clone(){

    	return false;
    }

    final public function __destruct(){

    	return false;
    }

    private function updateMenu($user_id,$oldTopMenu){
        $where['userid'] = $user_id;
        $authorityModel = M('authority'); //获得权限表模型
        $authority = $authorityModel->where($where)->select();

        //$a1=array(0=>"Dog",1=>"Cat",2=>"Horse",3=>"Bird");
        //$array = array_splice($a1,0,1);
        //unset($a1[1]);
        //var_dump($a1);
        //GLogger::debug("array",$array."123");
        foreach ($authority as $k => $v) {
            if($v['advantage'] == 1){
                unset($oldTopMenu[0]);
            }
            if($v['user'] == 1){
                unset($oldTopMenu[1]);
            }
            if($v['authority'] == 1){
                unset($oldTopMenu[2]);
            }
            if($v['pushstorage'] == 1){
                unset($oldTopMenu[3]);
            }
            if($v['popstorage'] == 1){
                unset($oldTopMenu[4]);
            }
            if($v['goodstype'] == 1){
                unset($oldTopMenu[5]);
            }
            if($v['goodsreceipt'] == 1){
                unset($oldTopMenu[6]);
            }
            if($v['goodsreturn'] == 1){
                unset($oldTopMenu[7]);
            }
        }
        $newTopMenu = array_values($oldTopMenu);
        return $newTopMenu;
        //GLogger::debug("authority",$authority);
    }
}