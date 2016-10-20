<?php
namespace Home\Controller;
use Think\Controller;

class BaseController extends Controller {


	/**
	 * [初始化方法]
	 * @return [type]
	 * @author FTD
	 */
    final public function _initialize(){
  		if(!session('uid')){
            $this->redirect('Login/login');
        }
    }

   	final public function __clone(){

    	return false;
    }

    final public function __destruct(){

    	return false;
    }
}