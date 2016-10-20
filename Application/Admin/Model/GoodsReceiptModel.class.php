<?php
namespace Admin\Model;
use Think\Model;

class GoodsReceiptModel extends Model{

	public function index($condition=""){
		$condition = $condition;

		$model = M();
		$result_a = $model->table("logistics")->alias("l")
			->field("l.id, l.NO, l.reason, l.logisticstype, l.time, l.status, u.realname as into_realname")
			->join("user as u on receiverid = u.id")
			->where( "l.NO like '%".$condition."%'")
			->order("l.id asc")
			->select();
			
		$result_b = $model->table("logistics")->alias("l")
			->field("u.realname as out_realname, l.id")
			->join("user as u on shipperid = u.id")
			->where( "l.NO like '%".$condition."%'")
			->order("l.id asc")
			->select();
		
		$count_a = count($result_a);
		$count_b = count($result_b);
		if ($count_a == $count_b) {
			for ($i=0; $i < $count_a; $i++) { 
				$res[$i] = $result_a[$i] + $result_b[$i];
			}
		}
		return $res;
	}



}
?>