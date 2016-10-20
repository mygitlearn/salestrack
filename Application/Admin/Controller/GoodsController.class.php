<?php 
namespace Admin\Controller;
use Think\Controller;
use Think\Model;
use Admin\Common\GLogger;
/**
 * Created by PhpStorm.
 * User: zhaoning
 * Date: 15/6/26
 * Time: 下午5:25
 */
class GoodsController extends BaseController {

	public function index() {
        //$this->display();
		$this->redirect("Goods/searchGoods");
	}
    /**
     *商品查询
     */
    public function searchGoods(){
        $goods =   I('goods');
        $goodsModel = M("collectdate");
        if($goods != null){
            $condition["goodstype.goodsname"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition["collectdate.barcode"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition["collectdate.bigcode"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition['_logic'] = 'or';
            $where['_complex'] = $condition;
        }

        $Page = new \Think\Page($goodsModel->where($where)->count(),10); //每页10条
        //$goodlist = $goodsModel->limit($Page->firstRow.','.$Page->listRows)->select();
        $goodlist = $goodsModel      //关联collectdate，goodstype，user表
            ->field('collectdate.*,
            goodstype.goodsname as goodsname,goodstype.norms as norms,goodstype.price as price,goodstype.verify as verify,
            user.realname as realname')
            ->join('left join goodstype on collectdate.goodstype_id=goodstype.id')
            ->join('left join user on collectdate.userid=user.id')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        //GLogger::debug('list=',$goodlist);
        foreach($goodlist as $k => $v){
            //GLogger::debug("status",$v['status']);
            if($v['status'] == 0){
                $goodlist[$k]['status'] = "否";
                //GLogger::debug("status",$v['status']);
            }else{
                $goodlist[$k]['status'] = "是";
            }
            //GLogger::debug("status",$v['status']);
        }
        //GLogger::debug("goodlist",$goodlist);
        $Page->setConfig('first', '<<'); //分页配置
        $Page->setConfig('last', '>>');
        $show = $Page->show();

        $this->assign('_list', $goodlist);
        $this->assign('_page', $show); // 赋值分页输出
        $this->display();
    }

    /**
     *上传查询文件
     */
    public function uploadTxt(){

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('txt');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     'Doc/'; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $return['status'] = 0;
            $return['info']   = "上传失败";
            $return['info']   = "上传成功";
            $return['url'] = "";
            //$return['url'] = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__."/Admin/Goods/searchlist";
            GLogger::debug("info",$info);
            $this->ajaxReturn($return);
        }else{// 上传成功
            $return['status'] = 1;
            $return['info']   = "上传成功";
            $return['url'] = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__."/Admin/Goods/searchlist";
            $url = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__."/Uploads/".$info['download']['savepath'].$info['download']['savename'];
            session("TXT_URL",$url);
            $this->ajaxReturn($return);
        }
    }

    public function searchlist(){
        $txt_url = session("TXT_URL");
        $txt = file_get_contents($txt_url);
        $arr = explode("\r\n",$txt);
        $condition["collectdate.barcode"] = array("in", $arr);
        $condition["collectdate.bigcode"] = array("in", $arr);
        $condition['_logic'] = 'or';
        $where['_complex'] = $condition;
        $goodsModel = M("collectdate");
        $Page = new \Think\Page($goodsModel->where($where)->count(),10); //每页10条
        //$goodlist = $goodsModel->limit($Page->firstRow.','.$Page->listRows)->select();
        $goodlist = $goodsModel      //关联collectdate，goodstype，user表
            ->field('collectdate.*,
            goodstype.goodsname as goodsname,goodstype.norms as norms,goodstype.price as price,goodstype.verify as verify,
            user.realname as realname')
            ->join('left join goodstype on collectdate.goodstype_id=goodstype.id')
            ->join('left join user on collectdate.userid=user.id')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        //GLogger::debug('list=',$goodlist);
        foreach($goodlist as $k => $v){
            //GLogger::debug("status",$v['status']);
            if($v['status'] == 0){
                $goodlist[$k]['status'] = "否";
                //GLogger::debug("status",$v['status']);
            }else{
                $goodlist[$k]['status'] = "是";
            }
            //GLogger::debug("status",$v['status']);
        }
        //GLogger::debug("goodlist",$goodlist);
        $Page->setConfig('first', '<<'); //分页配置
        $Page->setConfig('last', '>>');
        $show = $Page->show();

        $this->assign('_list', $goodlist);
        $this->assign('_page', $show); // 赋值分页输出
        $this->display('searchGoods');
            //GLogger::debug("arr",$arr);

    }

    /**
     *销售查询
     */
    public function searchSale(){
        //GLogger::debug('------time-----',time());
        //GLogger::debug('start_time-----',I("start_time"));
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
           // GLogger::debug('start_time',$endtime);
        }
        $selecttype = I("type");
        //GLogger::debug('selecttype',$selecttype);
        $id = session('id');   //发货商id 或者发货商父id
        $grade = session('grade');
        $dataModel = M("logistics");
        //GLogger::debug('grade',$grade);
        if($selecttype != 0 && $selecttype != null){
            $where['goodstype_id'] = $selecttype;
        }
        if($grade == 0){      //判断是登陆用户的等级
            $status['logisticstype'] = 0;
            $status['time'] = array(array('egt', $starttime), array('elt', $endtime));
            $Page = new \Think\Page($dataModel->where($status)->count(),10); //每页10条
            $logistics_list = $dataModel->where($status)->limit($Page->firstRow.','.$Page->listRows)->select();    //得到物流的id和编号等
        }elseif($grade == 1){
            $grade_1_where['logisticstype'] = 0;
            $grade_1_where['shipperid'] = $id;
            $grade_1_where['time'] = array(array('egt', $starttime), array('elt', $endtime));
            $Page = new \Think\Page($dataModel->where($grade_1_where)->count(),10); //每页10条
            $logistics_list = $dataModel->where($grade_1_where)->limit($Page->firstRow.','.$Page->listRows)->select();    //得到物流的id和编号等
        }elseif($grade == 2){
            $logistics_list = null;
        }
        $_list = array();

        foreach($logistics_list as $k => $v){      //根据物流id找货物条码，和发货人，收货人

            $sendid['sendid'] = $v['id'];
            $shipperid['id'] = $v['shipperid'];
            $shipper = M('user')->where($shipperid)->select();   //得到发货人
            $receiverid['id'] = $v['receiverid'];
            $receiver = M('user')->where($receiverid)->select();   //得到收货人
            $datecode = M('logisticsdetails')->where($sendid)->field('datecode')->select();  //得到货物的条形码
            //GLogger::debug('sendid',$v['id']);
            $type_id_count = array();
            foreach($datecode as $key => $val){
                //GLogger::debug('datecode',$val['datecode']);
                $condition["bigcode"] = $val['datecode'];
                $condition["barcode"] = $val['datecode'];
                $condition['_logic'] = 'or';
                $where['_complex'] = $condition;
                $idcount = M("collectdate")    //根据条形码找货物的种类
                            ->field('count(collectdate.goodstype_id) AS c,collectdate.goodstype_id,goodstype.goodsname as goodsname,
                            goodstype.norms as norms,goodstype.price as price,goodstype.verify as verify')
                            ->join('left join goodstype on collectdate.goodstype_id=goodstype.id')
                            ->where($where)
                            ->group('collectdate.goodstype_id')
                            ->order('c desc')
                            ->select();   //得到品种的数量
                $type_id_count[] = $idcount[0];
                //GLogger::debug('idcount',$idcount);
            }

            $sale_list =array();
            GLogger::debug("------------------==","-------------------------------");
            GLogger::debug('type_id_count',$type_id_count);
            for($i = 0;$i < count($type_id_count);$i++){    //把相同的品种数量合在一起得到总数量
                if($type_id_count[$i] != null){
                    $key = $type_id_count[$i]['goodstype_id'].'ce';
                    //$sale_list[$key]['salecount'] = $type_id_count[$i]['c'];
                    if(array_key_exists($key, $sale_list)){
                        $sale_list[$key]['NO'] = $v['NO'];
                        $sale_list[$key]['time'] = date('Y-m-d H:i:s',$v['time']);
                        $sale_list[$key]['receiver'] = $receiver[0]['realname'];
                        $sale_list[$key]['tel'] = $receiver[0]['tel'];
                        $sale_list[$key]['address'] = $receiver[0]['address'];
                        $sale_list[$key]['goodsname'] = $type_id_count[$i]['goodsname'];
                        $sale_list[$key]['norms'] = $type_id_count[$i]['norms'];
                        $sale_list[$key]['price'] = $type_id_count[$i]['price'];
                        $sale_list[$key]['verify'] = $type_id_count[$i]['verify'];
                        $sale_list[$key]['salecount'] = (int)$sale_list[$key]['salecount']+(int)$type_id_count[$i]['c'];
                        $sale_list[$key]['shipper'] = $shipper[0]['realname'];
                    }else{
                        $sale_list[$key]['NO'] = $v['NO'];
                        $sale_list[$key]['time'] = date('Y-m-d H:i:s',$v['time']);
                        $sale_list[$key]['receiver'] = $receiver[0]['realname'];
                        $sale_list[$key]['tel'] = $receiver[0]['tel'];
                        $sale_list[$key]['address'] = $receiver[0]['address'];
                        $sale_list[$key]['goodsname'] = $type_id_count[$i]['goodsname'];
                        $sale_list[$key]['norms'] = $type_id_count[$i]['norms'];
                        $sale_list[$key]['price'] = $type_id_count[$i]['price'];
                        $sale_list[$key]['verify'] = $type_id_count[$i]['verify'];
                        $sale_list[$key]['salecount'] = (int)$type_id_count[$i]['c'];
                        $sale_list[$key]['shipper'] = $shipper[0]['realname'];
                    }
                }
            }
            foreach($sale_list as $i => $j){
                $_list[] = $j;
            }
        }
        //GLogger::debug("_list",$_list);
        //session("saledata",$_list);
        $typestatus['status'] = 0;
        $typelist = M('goodstype')->where($typestatus)->select();
        $this->assign('_list', $_list);
        $this->assign('typelist', $typelist);
        //GLogger::debug("saledata",$this->saledata);

        $Page->setConfig('first', '<<'); //分页配置
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('_page', $show); // 赋值分页输出
        $this->display();
    }

    /**
     * 用excel导出数据
     */
    public function export(){
        $id = session('id');   //发货商id 或者发货商父id
        $grade = session('grade');
        $dataModel = M("logistics");
        if($grade == 0){      //判断是登陆用户的等级
            $status['logisticstype'] = 0;
            $logistics_list = $dataModel->where($status)->select();    //得到物流的id和编号等
        }elseif($grade == 1){
            $grade_1_where['logisticstype'] = 0;
            $grade_1_where['shipperid'] = $id;
            $logistics_list = $dataModel->where($grade_1_where)->select();    //得到物流的id和编号等
        }elseif($grade == 2){
            $logistics_list = null;
        }
        $_list = array();

        foreach($logistics_list as $k => $v){      //根据物流id找货物条码，和发货人，收货人

            $sendid['sendid'] = $v['id'];
            $shipperid['id'] = $v['shipperid'];
            $shipper = M('user')->where($shipperid)->select();   //得到发货人
            $receiverid['id'] = $v['receiverid'];
            $receiver = M('user')->where($receiverid)->select();   //得到收货人
            $datecode = M('logisticsdetails')->where($sendid)->field('datecode')->select();  //得到货物的条形码
            //GLogger::debug('sendid',$v['id']);
            $type_id_count = array();
            foreach($datecode as $key => $val){
                //GLogger::debug('datecode',$val['datecode']);
                $condition["bigcode"] = $val['datecode'];
                $condition["barcode"] = $val['datecode'];
                $condition['_logic'] = 'or';
                $where['_complex'] = $condition;
                $idcount = M("collectdate")    //根据条形码找货物的种类
                    ->field('count(collectdate.goodstype_id) AS c,collectdate.goodstype_id,goodstype.goodsname as goodsname,
                            goodstype.norms as norms,goodstype.price as price,goodstype.verify as verify')
                    ->join('left join goodstype on collectdate.goodstype_id=goodstype.id')
                    ->where($where)
                    ->group('collectdate.goodstype_id')
                    ->order('c desc')
                    ->select();   //得到品种的数量
                $type_id_count[] = $idcount[0];
                //GLogger::debug('idcount',$idcount);
            }

            $sale_list =array();
            GLogger::debug("------------------==","-------------------------------");
            GLogger::debug('type_id_count',$type_id_count);
            for($i = 0;$i < count($type_id_count);$i++){    //把相同的品种数量合在一起得到总数量
                if($type_id_count[$i] != null){
                    $key = $type_id_count[$i]['goodstype_id'].'ce';
                    //$sale_list[$key]['salecount'] = $type_id_count[$i]['c'];
                    if(array_key_exists($key, $sale_list)){
                        $sale_list[$key]['NO'] = $v['NO'];
                        $sale_list[$key]['time'] = date('Y-m-d H:i:s',$v['time']);
                        $sale_list[$key]['receiver'] = $receiver[0]['realname'];
                        $sale_list[$key]['tel'] = $receiver[0]['tel'];
                        $sale_list[$key]['address'] = $receiver[0]['address'];
                        $sale_list[$key]['goodsname'] = $type_id_count[$i]['goodsname'];
                        $sale_list[$key]['norms'] = $type_id_count[$i]['norms'];
                        $sale_list[$key]['price'] = $type_id_count[$i]['price'];
                        $sale_list[$key]['verify'] = $type_id_count[$i]['verify'];
                        $sale_list[$key]['salecount'] = (int)$sale_list[$key]['salecount']+(int)$type_id_count[$i]['c'];
                        $sale_list[$key]['shipper'] = $shipper[0]['realname'];
                    }else{
                        $sale_list[$key]['NO'] = $v['NO'];
                        $sale_list[$key]['time'] = date('Y-m-d H:i:s',$v['time']);
                        $sale_list[$key]['receiver'] = $receiver[0]['realname'];
                        $sale_list[$key]['tel'] = $receiver[0]['tel'];
                        $sale_list[$key]['address'] = $receiver[0]['address'];
                        $sale_list[$key]['goodsname'] = $type_id_count[$i]['goodsname'];
                        $sale_list[$key]['norms'] = $type_id_count[$i]['norms'];
                        $sale_list[$key]['price'] = $type_id_count[$i]['price'];
                        $sale_list[$key]['verify'] = $type_id_count[$i]['verify'];
                        $sale_list[$key]['salecount'] = (int)$type_id_count[$i]['c'];
                        $sale_list[$key]['shipper'] = $shipper[0]['realname'];
                    }
                }
            }
            foreach($sale_list as $i => $j){
                $_list[] = $j;
            }
        }
        $data = $_list;

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $filename="工作报表";

        //excel标题，固定的，如果有多个 ，则是这样的：---->   $headArr=array("项目","批次","城市","工作包","任务类型","任务目标");
        $headArr=array("编号","发货时间","客户","电话","地址","名称","规格","单价","品种","数量(袋)","发货商");

        $title = "销售报表";

        $this->getExcel($filename,$headArr,$data,$title);
    }
    /**
     *商品类别
     */
    public function goodsType(){
        $goods =   I('goods');
        GLogger::debug('goods=',$goods."hello");
        $goodsModel = M("goodstype");
        //GLogger::debug('realname=',$realname."hello");
        $where['status'] = 0;
        //模糊查询
        if($goods != null){
            $condition["goodsname"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition["verify"] = array("like", '%'.mb_strtolower($goods).'%');
            $condition['_logic'] = 'or';
            $where['_complex'] = $condition;
        }


        $Page = new \Think\Page($goodsModel->where($where)->count(),10); //每页10条

        $list = $goodsModel->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        //$list = $userModel->where($where)->select();
        GLogger::debug('list=',$list);
        $Page->setConfig('first', '<<'); //分页配置
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('_page', $show); // 赋值分页输出
        $this->assign('_list', $list);

        $this->display();
    }
    /**
     *增加商品类别界面
     */
    public function addType(){
        $this->display();
    }

    /**
     *增加商品类别
     * 辅助函数
     */
    public function do_addType(){
        $data['goodsname'] = I('goodsname');
        $data['norms'] = I('norms');
        $data['price'] = I('price');
        $data['verify'] = I('verify');
        if($data['goodsname'] == null || $data['goodsname'] ==""){
            $this->error("商品名称不能为空！");
        }
        if(strlen($data['goodsname']) > 40){
            $this->error("商品名称至多为40位!");
        }
        if($data['norms'] == null || $data['norms'] ==""){
            $this->error("商品规格不能为空！");
        }
        if(strlen($data['norms']) > 40){
            $this->error("商品规格至多为40位!");
        }
        if($data['price'] == null || $data['price'] ==""){
            $this->error("商品价格不能为空！");
        }
        if(strlen($data['price']) > 40){
            $this->error("商品价格至多为40位!");
        }
        if($data['verify'] == null || $data['verify'] ==""){
            $this->error("商品种类不能为空！");
        }
        if(strlen($data['verify']) > 40){
            $this->error("商品种类至多为40位!");
        }
        $typeModel = M("goodstype");
        $typecount = $typeModel->where($data)->count();
        if($typecount){
            $this->error("商品种类已存在！");
        }
        $typeid = $typeModel->add($data);
        if($typeid){
            $this->success("添加成功！");
        }else{
            $this->error("添加失败！");
        }

    }

    /**
     *删除商品所有类别
     */
    public function delAllType(){
        $id = array_unique((array)I('id',0));
        $where['id'] =   array('in',$id);
        $typeModel = M("goodstype");
        $data['status'] = 1;
        $delAllType = $typeModel->where($where)->save($data);
        if($delAllType){
            $this->success("删除成功！",U('Goods/goodsType'));
        }else{
            $this->error("删除失败！");
        }
    }

    /**
     *删除商品类别
     */
    public function delType(){
        $id =   I('id');
        $where['id'] = $id;
        $typeModel = M("goodstype");
        $data['status'] = 1;
        $is_del = $typeModel->where($where)->save($data);   //删除用户表
        if($is_del){
            $this->success("删除成功！",U('Goods/goodsType'));
        }else{
            $this->error("删除失败！");
        }
    }

    /**
     *修改商品类别
     */
    public function setType(){
        $typeid = I('id');
        $typeModel = M("goodstype");
        $where['id'] = $typeid;
        $type = $typeModel->where($where)->select();
        //GLogger::debug($user[0]);
        $this->assign("type",$type[0]);
        $this->display();
    }
    /**
     *修改商品类别
     * 辅助函数
     */
    public function do_setType(){
        $where['id'] = I('id');
        $data['goodsname'] = I('goodsname');
        $data['norms'] = I('norms');
        $data['price'] = I('price');
        $data['verify'] = I('verify');
        if($data['goodsname'] == null || $data['goodsname'] ==""){
            $this->error("商品名称不能为空！");
        }
        if(strlen($data['goodsname']) > 40){
            $this->error("商品名称至多为40位!");
        }
        if($data['norms'] == null || $data['norms'] ==""){
            $this->error("商品规格不能为空！");
        }
        if(strlen($data['norms']) > 40){
            $this->error("商品规格至多为40位!");
        }
        if($data['price'] == null || $data['price'] ==""){
            $this->error("商品价格不能为空！");
        }
        if(strlen($data['price']) > 40){
            $this->error("商品价格至多为40位!");
        }
        if($data['verify'] == null || $data['verify'] ==""){
            $this->error("商品种类不能为空！");
        }
        if(strlen($data['verify']) > 40){
            $this->error("商品种类至多为40位!");
        }
        $typeModel = M("goodstype");
        $typecount = $typeModel->where($data)->count();   //信息没有修改
        if($typecount){
            $this->success("修改成功！",U('Goods/goodsType'));
        }
        $typeid = $typeModel->where($where)->save($data);  //信息修改后
        if($typeid){
            $this->success("修改成功！",U('Goods/goodsType'));
        }else{
            $this->error("修改失败！");
        }
    }

    /**
     * 生成报表方法
     * 此方法不需要改动
     */
    private function getExcel($fileName,$headArr,$data,$title){
        //对数据进行检验
        if(empty($data) || !is_array($data)){
            die("data must be a array");
        }
        //检查文件名
        if(empty($fileName)){
            exit;
        }
        // H:i:s
        $date = date("Y_m_d_H_i_s",time());
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //第一列设置报表时间范围
        $key = ord("A");
        $colum = chr($key);
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $title);


        //第二列设置报表列头
        $key = ord("A");
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'2', $v);
            $key += 1;
        }

        $column = 3;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        // $objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }

}

 ?>