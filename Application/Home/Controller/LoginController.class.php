<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
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
        //var_dump($data);
        if($data && !empty($user_name) && !empty($password)){
            session("uid",$data["id"]);
            session("urealname",$data["realname"]);
            session("ugrade",$data['grade']);
            session("ufatherid",$data['fatherid']);
            $this->success('登录成功！', U('Index/index'));
        }else{

            $this->error('用户不存在或密码错误！');
        }
    }
}