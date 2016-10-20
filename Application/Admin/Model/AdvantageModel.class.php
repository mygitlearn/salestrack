<?php
namespace Admin\Model;
use Think\Model;


class AdvantageModel extends Model{

    public function submiteduit($data){
        $id['id'] = $data['id'];

        $arr['title'] = $data['title'];
        $arr['info'] = $data['info'];
        $arr['time']=time();

        $model = M();
        $obj = $model->table('advantage')->where($id)->data($arr)->save();
        if ($obj){
            return true;
        }else{
            return false;
        }

    }

}