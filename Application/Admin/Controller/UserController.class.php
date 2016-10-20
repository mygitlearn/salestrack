<?php 
namespace Admin\Controller;
use Admin\Controller;
use Admin\Common\GLogger;
/**
 * Created by PhpStorm.
 * User: zhaoning
 * Date: 15/6/24
 * Time: 下午5:54
 */
class UserController extends BaseController {
    /*
     *index界面
     * 初始化用户列表
     */
	public function index() {
        $realname =   I('realname');
        $userModel = M("user");
        GLogger::debug('realname=',$realname."hello");
        //$where['status'] = 0;
        //$where['id'] = array('NEQ',session('id'));
        $grade = session('grade');
        if($grade == 0){
            $where['id'] = array('NEQ',session('id'));
        }elseif($grade == 1){
            $where['fatherid'] = session('id');
        }else{
            $where['id'] = session('id');
        }
        //模糊查询
        if($realname != null){
            $condition["realname"] = array("like", '%'.mb_strtolower($realname).'%');
            $condition["username"] = array("like", '%'.mb_strtolower($realname).'%');
            $condition['_logic'] = 'or';
            $where['_complex'] = $condition;
        }


        $Page = new \Think\Page($userModel->where($where)->count(),10); //每页10条

        $list = $userModel->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

        //$list = $userModel->where($where)->select();
        GLogger::debug('list=',$list);
        $Page->setConfig('first', '<<'); //分页配置
        $Page->setConfig('last', '>>');
        $show = $Page->show();

        $this->assign('_list', $list);
        $this->assign('_page', $show); // 赋值分页输出
        $this->display();
	}
    /*
     *删除用户
     */
    public function delUser(){
        //GLogger::debug('deluserid',I('id'));
        $id =   I('id');
        $where['id'] = $id;
        $userModel = M("user");
        //$data['status'] = 1;
        $is_del = $userModel->where($where)->delete();   //删除用户表
        if($is_del){
            $authorityModel = M('authority');
            $auth['userid'] = $id;
            //GLogger::debug("delAllUser");
            $auth = $authorityModel->where($auth)->delete();   //删除权限表
            if($auth){
                $this->success("删除成功！",U('User/index'));
            }else{
                $this->error("删除失败！");
            }
        }else{
            $this->error("删除用户失败！");
        }
    }

    /*
     *批量删除用户
     */
    public function delAllUser(){
        //GLogger::debug("delAllUser");
        $id = array_unique((array)I('id',0));
        $where['id'] =   array('in',$id);
        $userModel = M("user");
        //$data['status'] = 1;
        $delAlluser = $userModel->where($where)->delete();

        if($delAlluser){
            $authorityModel = M('authority');
            $auth['userid'] = array('in',$id);
            //GLogger::debug("delAllUser");
            $auth = $authorityModel->where($auth)->delete();
            if($auth){
                $this->success("删除成功！",U('User/index'));
            }else{
                $this->error("删除失败！");
            }

        }else{
            $this->error("删除失败！");
        }
    }

    /*
     *添加用户
     */
    public function addUser(){
        $this->display();
    }

    /*
     *添加用户
     */
    public function do_addUser(){
        if(session('grade') >= 2){
            $this->error("没有权限添加用户！");
        }
        //$this->error("添加失败！");
        $data['username'] = I('username');
        $data['realname'] = I('realname');
        $data['tel'] = I('tel');
        $data['area'] = I('area');
        $data['address'] = I('address');
        //$data['fatherid'] = session('id');
        $data['fatherid'] = session('id');
        //$data['grade'] = session('grade');
        $data['grade'] = (int)session('grade')+1;
        $data['password'] = md5("123456");
        $data['status'] = 0;
        if($data['realname'] == null || $data['realname'] ==""){
            $this->error("代理商名称不能为空！");
        }
        if(strlen($data['realname']) > 32){
            $this->error("名称至多为32位!");
        }
        if($data['username'] == null || $data['username'] == ""){
            $this->error("代理商账号不能为空！");
        }
        $is_username = ereg("^[a-zA-Z][a-zA-Z0-9_]{4,19}$",$data['username']);

        if(!$is_username){
            $this->error("账号以字母开头至少为5位字母或数字!");
        }
        if($data['tel'] == null || $data['tel'] == ""){
            $this->error("联系电话不能为空！");
        }
        if($data['area'] == null || $data['area'] == ""){
            $this->error("销售区域不能为空！");
        }
        if(strlen($data['area']) > 32){
            $this->error("销售区域至多为32位!");
            return false;
        }
        if($data['address'] == null || $data['address'] == ""){
            $this->error("联系地址不能为空！");
            return false;
        }
        if(strlen($data['address']) > 32){
            $this->error("联系地址至多为32位!");
            return false;
        }

        $userModel = M("user");
        $where['username'] = $data['username'];
        $username = $userModel->where($where)->count();
        if($username){
            $this->error("代理商账号存在！");
        }
        $father['userid'] = session("id");
        $fatherauthority = M("authority")->where($father)
            ->field('advantage,user,authority,pushstorage,
            popstorage,goodsreceipt,goodsreturn,goodstype')
            ->select();
        $userid = $userModel->add($data);
        GLogger::debug("userid",$userid);
        if($userid){
            $authorityModel = M('authority');
            $auth = $fatherauthority[0];
            $auth['userid'] = $userid;
            $auth['pushstorage'] = 1;
            $auth = $authorityModel->add($auth);
            if($auth){
                $this->success("添加成功！",U('User/index'));
            }else{
                $this->error("添加失败！");
            }
        }else{
            $this->error("添加失败！");
        }
        /*GLogger::debug("begin");
        GLogger::debug(I('username').I('realname').I('tel').I('area').I('address'));
        GLogger::debug("end");*/
    }

    /*
     *修改用户界面
     */
    public function setUser(){
        $userid = I('id');
        $userModel = M("user");
        $where['id'] = $userid;
        $user = $userModel->where($where)->select();
        GLogger::debug($user[0]);
        $this->assign("user",$user[0]);
        $this->display();
    }
    /*
     *修改用户（修改数据库）
     */
    public function do_setUser(){
        $where['id'] = I('id');
        $data['username'] = I('username');
        $data['realname'] = I('realname');
        $data['tel'] = I('tel');
        $data['area'] = I('area');
        $data['address'] = I('address');

        if($data['realname'] == null || $data['realname'] ==""){
            $this->error("代理商名称不能为空！");
        }
        if(strlen($data['realname']) > 32){
            $this->error("名称至多为32位!");
        }
        if($data['username'] == null || $data['username'] == ""){
            $this->error("代理商账号不能为空！");
        }
        $is_username = ereg("^[a-zA-Z][a-zA-Z0-9_]{4,19}$",$data['username']);

        if(!$is_username){
            $this->error("账号以字母开头至少为5位字母或数字!");
        }
        if($data['tel'] == null || $data['tel'] == ""){
            $this->error("联系电话不能为空！");
        }
        if($data['area'] == null || $data['area'] == ""){
            $this->error("销售区域不能为空！");
        }
        if(strlen($data['area']) > 32){
            $this->error("销售区域至多为32位!");
        }
        if($data['address'] == null || $data['address'] == ""){
            $this->error("联系地址不能为空！");
        }
        if(strlen($data['address']) > 32){
            $this->error("联系地址至多为32位!");
            return false;
        }

        $userModel = M("user");
        $username = $userModel->where($where)->select();
        if($data['username'] == $username[0]['username'] && $data['realname'] == $username[0]['realname'] &&
            $data['tel'] == $username[0]['tel'] && $data['area'] == $username[0]['area']
        && $data['address'] == $username[0]['address']){
            $this->success("修改成功！",U('User/index'));
        }
        $userid = $userModel->where($where)->save($data);
        //GLogger::debug("userid",$userid);
        if($userid){
            $this->success("修改成功！",U('User/index'));
        }else{
            $this->error("修改失败！");
        }
    }

    /*
     *重置密码
     */
    public function resetPwd(){
        $id =  I('id');
        //$id = 10;
        //GLogger::debug($id);
        $userModel = M("User");
        $where['id'] = $id;
        $newpwd = "123456";
        $setData['password'] = md5($newpwd);
        $password = $userModel->where($where)->field('password')->select();
        if($password[0]['password'] == $setData['password']){
            $this->success("重置密码为123456");
        }
        $is_resetPwd = $userModel->where($where)->save($setData);
        GLogger::debug("is_resetPwd",$is_resetPwd);
        if($is_resetPwd){
            $this->success("重置密码为123456");
        }else{
            $this->error("重置密码失败！");
        }
    }

    /*
     *修改个人密码
     */
    public function updatePassword(){
        $this->display();
    }

    /*
     *修改个人密码
     */
    public function submitPassword(){
        $id =  session('id');
        $oldPWD = I('old');
        $newPWD = I('password');
        $renewPWD = I('repassword');
        if($newPWD != $renewPWD){
            $this->error('确认密码错误');
        }
        if(strlen($newPWD) < 5){
            $this->error("密码至少为5位!");
        }
        $userModel = M("User");
        $where['id'] = $id;
        $password = $userModel->where($where)->field('password')->select();
        if($password[0]['password'] != md5($oldPWD)){
            $this->error("原密码错误！");
        }
        if($oldPWD == $newPWD){
            $this->success("修改密码成功！");
        }
        $data['password'] = md5($newPWD);
        $password = $userModel->where($where)->save($data);
        if($password){
            $this->success("修改密码成功！",U('User/index'));
        }else{
            $this->error("修改密码失败！");
        }
    }


}

 ?>