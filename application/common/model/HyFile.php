<?php
namespace app\common\model;
use think\Request;
use think\Model;

class HyFile extends Model
{	

	public $table = DTP.'frame_file';
	public static function upload($filename,$options=null){
		// dump($filename);
		$file = Request::instance()->file($filename);
		$info = '';	

		if($file){
			if(!is_null($options)) $file->validate(array('size'=>$size,'ext'=>$ext));
		
			$res = $file->rule('md5')->move(ROOT_PATH.'public'.DS.'uploads');

			if($res){
				$data = array(
					'savename'=>$res->getSaveName(),
					'savepath'=>$res->getPath(),
					'ext'=>$res->getExtension(),
					'mime'=>$res->getMime(),
					'size'=>$res->getSize(),
					'md5'=>$res->hash(),
					'create_time'=>time()
				);
				$id = $this->insertGetId($data);
				$json['info'] ="上传成功！";
				$json['status'] = true
				$json['data'] = array('id'=>$id);
				return $json;
			}
		}else{
				$json['info'] ="上传失败！";
				$json['status'] = false
				$json['data'] = '';
				return $json;
		}
	}
}