<?php
namespace app\common\model;
use think\Config;
use think\Model;
/**
 * 数据库扩展类
 * 增强连表查询
 */
abstract class HyBase extends Model
{
	function __construct($data = []){
		if (is_object($data)) {
            /* 返回由对象属性组成的关联数组*/
            $this->data = get_object_vars($data);
        } else {
            $this->data = $data;
        }
        // 记录原始数据
        // dump($data);
        $this->origin = $this->data;

        // 当前类名
        /*get_called_class返回类的名称，如果不是在类中调用则返回 FALSE。*/
        // dump(get_called_class());// 'app\user\model\User'
        $this->class = get_called_class();

        if (empty($this->name)) {
            // 当前模型名
            $name       = str_replace('\\', '/', $this->class);
            // dump($name);
            //basename  返回路径中的文件名部分
            $this->name = basename($name);
            // dump($this->name);//User
            if (Config::get('class_suffix')) {
                $suffix     = basename(dirname($name));
                $this->name = substr($this->name, 0, -strlen($suffix));
            }
        }

        if (is_null($this->autoWriteTimestamp)) {
            // 自动写入时间戳
            $this->autoWriteTimestamp = $this->getQuery()->getConfig('auto_timestamp');
        }

        if (is_null($this->dateFormat)) {
            // 设置时间戳格式
            $this->dateFormat = $this->getQuery()->getConfig('datetime_format');
        }

        if (is_null($this->resultSetType)) {
            $this->resultSetType = $this->getQuery()->getConfig('resultset_type');
        }
        // 执行初始化操作
        $this->initialize();
		
		$this->_after_initialize();
	}

	protected function initialize(){	
	}

	/**
	 * [_after_initialize 初始化后置方法]
	 * @return [type] [description]
	 */
	protected function _after_initialize(){}

	
}