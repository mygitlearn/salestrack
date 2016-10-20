<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Common\GLogger;

class PublicController extends Controller {

    /**
     * 后台用户登录
     */
    public function login(){
        $this->display();
    }

    public function do_login(){
        //获得用户名
        $user_name = trim(I("post.username"));
        //获得密码
        $password = trim(I("post.password"));
        $where["username"] = $user_name;
        $where["password"] = md5($password);
        //GLogger::d('Dbug_test');
        //$this->error($user_name.'用户不存在或密码错误！'.$password);
        //得到用户的信息
        $data = M("User")->where($where)->find();
        if($data && !empty($user_name) && !empty($password)){
            session("id",$data["id"]);
            session("realname",$data["realname"]);
            session("grade",$data['grade']);
            session("fatherid",$data['fatherid']);
            $this->success('登录成功！', U('Advantage/index'));
        }else{
            $this->error('用户不存在或密码错误！');
        }
    }
    /**
     * 退出
     */
    public function logout(){
        session('id',null);
        session('user_name',null);
        $this->redirect('Public/login');
    }

    /*public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }*/
}