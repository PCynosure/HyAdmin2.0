<?php
namespace app\common\controller;
use think\Controller;
use think\Request;
use think\Config;
use think\Session;

use app\common\model\HyAuth;

// 数据表前缀

defined('DTP') or define('DTP', Config::get('database.prefix'));
// 当前时间戳
defined('TIME') or define('TIME', time());
/**
 * 框架模型类
 */
class Base extends Controller
{
	//默认模型
	protected $model;
	public $request;
	public $view;
	// protected $url;

	// public function __construct(){
	// 	parent::__construct();

	// }

	public function _initialize(){
		
		$this->requestInfo();

        // dump(Request::instance()->module());
		
		//权限检查
		/*$auth = $this->checkAuth();
		if(!$auth['status']){
			return json($auth);
		} */
		
		//获取模块信息
		$this->setHeader();
		
	}

	protected function setHeader(){
		header("Content-Type: text/html;charset=utf-8"); 
		header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authKey, sessionId");
	}

	/**
	 * [requestInfo 通过request获取模块信息]
	 * @return [type] [description]
	 */
	protected function requestInfo() {
		// $this->param = $this->request->param();
		$this->request = Request::instance();

		// dump(MODULE_NAME);//'admin'
		/*用request获取当前模型，控制器，操作的名称*/
		defined('MODULE_NAME') or define('MODULE_NAME', $this->request->module());	

		$controller = $this->request->controller();
		if(strpos($this->request->controller(),".")){
			$controller = substr($controller,strrpos($controller,".")+1);
		}
		defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $controller);
		defined('ACTION_NAME') or define('ACTION_NAME', $this->request->action());
		// /*判断请求方式*/
		// // defined('IS_POST') or define('IS_POST', $this->request->isPost());
		// // defined('IS_GET') or define('IS_GET', $this->request->isGet());
		
		$this->url = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
		// dump($this->url);//'admin/index/login'
		// $this->assign('request', $this->request);
		// $this->assign('param', $this->param);
	}

	/**
	 * [checkAuth 检查权限]
	 * @return [type] [description]
	 */
	protected function checkAuth(){
		parent::_initialize();
        /*获取头部信息*/ 
        // $header = Request::instance()->header();
        
        $authKey = Session::get('authKey');
        $sessionId = Session::get('sessionid');
        $cache = cache('Auth_'.$authKey);
        $ruleList = cache('authList');
        // 校验sessionid和authKey
        if (empty($sessionId)||empty($authKey)||empty($cache)) {
            header('Content-Type:application/json; charset=utf-8');
            return [
	            	'code'=>101, 
	            	'error'=>'登录已失效',
	            	'status'=>false
		           ];
        }
        //证明是最高权限的管理员
        if($ruleList == 'all') return ['status'=>true];

        // 检查账号有效性
        $userInfo = $cache['userInfo'];
        $map['id'] = $userInfo['id'];
        $map['status'] = 1;

        if (!Db::name('user')->where($map)->value('id')) {
            header('Content-Type:application/json; charset=utf-8');
            return [
            	'code'=>103, 
            	'error'=>'账号已被删除或禁用',
            	'status'=>false
            ];   
        }
        // 更新缓存
        cache('Auth_'.$authKey, $cache, config('LOGIN_SESSION_VALID'));
        
        $authAdapter = new HyAuth($authKey);

        $ruleName = $this->url;

        if (!$authAdapter->check($ruleName, $cache['userInfo']['id'])) {
            header('Content-Type:application/json; charset=utf-8');
            return [
            	'code'=>102,
            	'error'=>'无权访问！',
            	'status'=>false
            ];
        }

        return ['status'=>true];
        // $GLOBALS['userInfo'] = $userInfo;
	}


}