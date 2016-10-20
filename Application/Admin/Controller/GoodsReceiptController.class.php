<?php

namespace Admin\Controller;
use Think\Controller;
use Think\Model;
use Admin\Common\GLogger;

class GoodsReceiptController extends BaseController{
    /**
     * 收货列表
     */
    public function index(){
        $userid = session("id");
        if(I("start_time") != null || I("start_time") != 0){
            $starttime = I("start_time")." 00:00:00";
            $starttime = strtotime($starttime);
            //GLogger::debug('start_time',$starttime);
            //GLogger::debug('start_time',date('Y-m-d H:i:s',$starttime));
        }else{
            $starttime = 0;
            //GLogger::debug('start_time',$starttime);
        }
        if(I("end_time") != null){
            $endtime = I("end_time")." 23:59:59";
            $endtime = strtotime($endtime);
            //GLogger::debug('end_time',$endtime);
            //GLogger::debug('end_time',date('Y-m-d H:i:s',$endtime));
        }else{
            $endtime = 2147483647;
            //GLogger::debug('start_time',$endtime);
        }
        $receipt = I('receipt');
        if($receipt != null ){
            $condition["logistics.NO"] = array("like", '%'.mb_strtolower($receipt).'%');
            $condition["user.realname"] = array("like", '%'.mb_strtolower($receipt).'%');
            $condition['_logic'] = 'or';
            $receiver['_complex'] = $condition;
        }
        //GLogger::debug("userid",$userid);
        $receiver['logistics.receiverid'] = $userid;
        $receiver['logistics.time'] = array(array('egt', $starttime), array('elt', $endtime));
        $Page = new \Think\Page(M("logistics")->where($receiver)->count(),20); //每页10条
        $logisticslist = M("logistics")
                       ->field('logistics.*,user.id as userid,user.realname as shippername,user.tel as shippertel')
                       ->join('left join user on user.id=logistics.shipperid')
                       ->where($receiver)
                       ->limit($Page->firstRow.','.$Page->listRows)
                       ->order('status,time desc')
                       ->select();
        //GLogger::debug("logisticslist",$logisticslist);
        foreach($logisticslist as $k => $v){
            $logisticslist[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
            if($v['reason'] == "" || $v['reason'] == '""' || $v['reason'] == null){
                $logisticslist[$k]['reason'] = "无";
            }
        }
        $Page->setConfig('first', '<<'); //分页配置
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('_page', $show); // 赋值分页输出
        $this->assign('_list', $logisticslist); // 赋值分页输出
        $this->display();
    }
    /**
     * 接收货物
     * 改变货物所属商的id
     */
    public function setstatus(){
        $logistics['id'] = I('id');
        $status['status'] = 1;
        $logisticslist = M('logistics')->where($logistics)->save($status);
        if($logisticslist){
            $send['sendid'] = I('id');
            $logisticsdetailslist = M('logisticsdetails')->where($send)->select();
            if($logisticsdetailslist){
                //GLogger::debug("logisticsdetailslist",$logisticsdetailslist);
                $code = array();
                foreach($logisticsdetailslist as $k => $v){
                    $code[] = $v['datecode'];
                }
                //GLogger::debug('code',$code);
                $condition["collectdate.barcode"] = array("in", $code);
                $condition["collectdate.bigcode"] = array("in", $code);
                $condition['_logic'] = 'or';
                $where['_complex'] = $condition;
                $date['userid'] = session('id');
                $datelist = M('collectdate')->where($where)->save($date);
                if($datelist){
                    $this->success("接收成功");
                }else{
                    $this->error("接收失败");
                }
            }else{
                $this->error("接收失败");
            }
        }else{
            $this->error("接收失败");
        }
    }

    public function lookgoods(){
        $send['sendid'] = I('sendid');
        session('sendid',$send['sendid']);
        if(I('sendid') == null){
            $send['sendid'] = session('sendid');
        }
        $sendlist = M('logisticsdetails')->where($send)->field('datecode')->select();
        $datacode = array();
        foreach($sendlist as $k => $v){
            $datacode[] = $v['datecode'];
        }
        $condition["collectdate.barcode"] = array("in",$datacode);
        $condition["collectdate.bigcode"] = array("in",$datacode);
        $condition['_logic'] = 'or';
        $where['_complex'] = $condition;
        $searchgoods = array();
        $searchgoods[] = $where;
        $goods = I('goods');
        if($goods != null ){
            $condition["collectdate.barcode"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition["collectdate.bigcode"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition["goodstype.verify"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition["goodstype.goodsname"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition["goodstype.norms"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition['_logic'] = 'or';
            $goodswhere['_complex'] = $condition;
            $searchgoods[] = $goodswhere;
        }
        $Page = new \Think\Page(M("collectdate")->where($searchgoods)->count(),20); //每页10条
        $goodslist = M("collectdate")
            ->field('collectdate.*,goodstype.goodsname as goodsname,goodstype.norms as norms,
            goodstype.verify as verify,goodstype.price as price')
            ->join('left join goodstype on goodstype.id=collectdate.goodstype_id')
            ->where($searchgoods)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('goodstype.goodsname')
            ->select();
        $Page->setConfig('first', '<<'); //分页配置
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('_page', $show); // 赋值分页输出
        $this->assign('_list', $goodslist); // 赋值分页输出
        $this->display();
    }
}
?>