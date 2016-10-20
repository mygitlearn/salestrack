<?php 
namespace Admin\Controller;
use Admin\Controller;
use Admin\Common\GLogger;
/**
 * Created by PhpStorm.
 * User: zhaoning
 * Date: 15/7/1
 * Time: 下午2:39
 */
class AuthorityController extends BaseController {
    /*
     *index界面
     * 初始化权限列表
     */
	public function index() {
        $realname =   I('realname');

        $id = session("id");
        $title['userid'] = $id;
        $theadtitle = M("authority")->where($title)->select();
        $theadlist = array();     //得到用户权限列表的thead
        $theadname = array();
        //var_dump($theadtitle[0]);
        if((int)$theadtitle[0]['advantage'] == 0){
            $theadlist[] = "广告";
        }else{
            $theadname[] = "advantage";
            $tr_advantage = "td_hidden";
            $this->assign('tr_advantage', $tr_advantage);
        }
        if((int)$theadtitle[0]['user'] == 0){
            $theadlist[] = "用户";
        }else{
            $theadname[] = "user";
            $tr_user = "td_hidden";
            $this->assign('tr_user', $tr_user);
        }
        if((int)$theadtitle[0]['authority'] == 0){
            $theadlist[] = "权限";
        }else{
            $theadname[] = "authority";
            $tr_authority = "td_hidden";
            $this->assign('tr_authority', $tr_authority);
        }
        if((int)$theadtitle[0]['pushstorage'] == 0){
            $theadlist[] = "入库";
        }else{
            $theadname[] = "pushstorage";
            $tr_pushstorage = "td_hidden";
            $this->assign('tr_pushstorage', $tr_pushstorage);
        }
        if((int)$theadtitle[0]['popstorage'] == 0){
            $theadlist[] = "出库";
        }else{
            $theadname[] = "popstorage";
            $tr_popstorage = "td_hidden";
            $this->assign('tr_popstorage', $tr_popstorage);
        }
        if((int)$theadtitle[0]['goodstype'] == 0){
            $theadlist[] = "商品";
        }else{
            $theadname[] = "goodstype";
            $tr_goodstype = "td_hidden";
            $this->assign('tr_goodstype', $tr_goodstype);
        }
        if((int)$theadtitle[0]['goodsreceipt'] == 0){
            $theadlist[] = "收货";
        }else{
            $theadname[] = "goodsreceipt";
            $tr_goodsreceipt = "td_hidden";
            $this->assign('tr_goodsreceipt', $tr_goodsreceipt);
        }
        if((int)$theadtitle[0]['goodsreturn'] == 0){
            $theadlist[] = "退货";
        }else{
            $theadname[] = "goodsreturn";
            $tr_goodsreturn = "td_hidden";
            $this->assign('tr_goodsreturn', $tr_goodsreturn);
        }

        $userModel = M("user");
        //GLogger::debug('realname=',$realname."hello");
        $where['user.status'] = 0;
        $where['user.fatherid'] = $id;
        //模糊查询
        if($realname != null){
            $condition["user.realname"] = array("like", '%'.mb_strtolower($realname).'%');
            $condition["user.username"] = array("like", '%'.mb_strtolower($realname).'%');
            $condition['_logic'] = 'or';
            $where['_complex'] = $condition;
        }
        //$Page = new \Think\Page($userModel->where($where)->count(),10); //每页10条

        $userlist = $userModel
                    ->field("user.id,user.username,user.realname,
                    authority.advantage as advantage,authority.user as user,
                    authority.authority as authority,authority.pushstorage as pushstorage,
                    authority.popstorage as popstorage,authority.goodsreceipt as goodsreceipt,
                    authority.goodsreturn as goodsreturn,authority.goodstype as goodstype")
                    ->join('left join authority on user.id=authority.userid')
                    ->where($where)
                    ->select();
        /*foreach($userlist as $i => $value){
            foreach($theadname as $k => $v){
                unset($userlist[$i][$v]);
            }
        }*/
        //var_dump($userlist);
        $_list = array();
        //$_list = $userlist;
        foreach($userlist as $k => $v){
            foreach($v as $key => $val){
                if($val == "0" ){
                    $v[$key] = "显示";
                }else if($val == "1" && (int)$v['id'] != 1){
                    $v[$key] = "不显示";
                }
            }
            $_list[] = $v;
        }
        //$list = $userModel->where($where)->select();
        //GLogger::debug('list=',$list);
        /*$Page->setConfig('first', '<<'); //分页配置
        $Page->setConfig('last', '>>');
        $show = $Page->show();*/
        $this->assign('theadlist', $theadlist);
        $this->assign('_list', $_list);
        //$this->assign('_page', $show); // 赋值分页输出
        $this->display();
	}

    /*
     *设置权限
     */
    public function setting(){
        $userid = I('userid');
        $advantage = I("advantage");
        if($advantage != null){
            if($advantage == "显示"){
                $advantage = 1;     //不显示
            }else if($advantage == "不显示"){
                $advantage = 0;
            }
            $data['advantage'] = $advantage;
        }
        $user = I("user");
        if($user != null){
            if($user == "显示"){
                $user = 1;     //不显示
            }else if($user == "不显示"){
                $user = 0;
            }
            $data['user'] = $user;
        }
        $authority = I("authority");
        if($authority != null){
            if($authority == "显示"){
                $authority = 1;     //不显示
            }else if($authority == "不显示"){
                $authority = 0;
            }
            $data['authority'] = $authority;
        }
        $pushstorage = I("pushstorage");
        if($pushstorage != null){
            if($pushstorage == "显示"){
                $pushstorage = 1;     //不显示
            }else if($pushstorage == "不显示"){
                $pushstorage = 0;
            }
            $data['pushstorage'] = $pushstorage;
        }
        $popstorage = I("popstorage");
        if($popstorage != null){
            if($popstorage == "显示"){
                $popstorage = 1;     //不显示
            }else if($popstorage == "不显示"){
                $popstorage = 0;
            }
            $data['popstorage'] = $popstorage;
        }
        $goodsreceipt = I("goodsreceipt");
        if($goodsreceipt != null){
            if($goodsreceipt == "显示"){
                $goodsreceipt = 1;     //不显示
            }else if($goodsreceipt == "不显示"){
                $goodsreceipt = 0;
            }
            $data['goodsreceipt'] = $goodsreceipt;
        }
        $goodsreturn = I("goodsreturn");
        if($goodsreturn != null){
            if($goodsreturn == "显示"){
                $goodsreturn = 1;     //不显示
            }else if($goodsreturn == "不显示"){
                $goodsreturn = 0;
            }
            $data['goodsreturn'] = $goodsreturn;
        }
        $goodstype = I("goodstype");
        if($goodstype != null){
            if($goodstype == "显示"){
                $goodstype = 1;     //不显示
            }else if($goodstype == "不显示"){
                $goodstype = 0;
            }
            $data['goodstype'] = $goodstype;
        }
        //GLogger::debug("advantage",$advantage);
        //GLogger::debug("userid",$userid);

        $user['fatherid'] = $userid;
        $children = M("user")->where($user)->select();
        $children_id = array();
        foreach($children as $k => $v){
            $children_id[] = $v['id'];
        }
        $children_id[] = $userid;
        $where['userid'] = array('in',$children_id);
        $authorityModel = M("authority");
        $auth = $authorityModel->where($where)->save($data);
        if($auth){
            $this->success("修改成功！");
        }else{
            $this->error("修改失败！");
        }
    }

}

 ?>