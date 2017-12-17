<?php
namespace app\admin\controller\System;
use app\common\controller\Base;
use think\View;
use think\Request;
use think\Db;
class System extends Base
{
	public function setAuth(){
		$pk = 1;

		$res = Db::name('frame_capacity')->where(array('status'=>1))
				->field('id,title,url,pid,type,name')
				->select();
		$data = [];

		foreach ($res as $k => $v) {
			$data[$k] = [
				'val' => $v['id'],
				'url' => empty($v['url'])?'':($v['url'].$v['type'].(empty($v['name'])?'':'?'.$v['name'])),
				'title' => $v['title']
			];
		}

		return json($data);


	}

	public function ajax(){
		$type = Request::instance()->get('q');
		$json = [
			'status'=>false,
			'data'=>'',
			'info'=>''
		]
		call_user_func_array(array(&$this,'ajax_'.$type),array(&$json));
		
	}
    /**
     * [ajax_update 权限更新操作]
     * @param  [type] $json [description]
     * @return [type]       [description]
     */
	public function ajax_update($json){
		// $data = Request::instance()->post();
		$data = [
			'role_id' => 1,
			'capacity_id'=> [1,2,3,5,45,64,4]
		]
		$res = Db::name('frame_role_capacity')->where(['role_id'=>$data['role_id']])->save(['capacity_id'=>implode(',',$data['capacity_id'])]);

		if($res){
			$json['status'] = true;
			$json['info'] = '修改成功！';
			return;
		}
	}


	public function ajax_insert(){

	}
}