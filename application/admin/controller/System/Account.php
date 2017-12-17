<?php
namespace app\admin\controller\System;
use app\common\controller\Base;
use app\common\model\HyAccount;
use think\View;
use think\Request;

class Account extends Base
{		
    // public $request = [];
    protected $resJson = [
            'data'=>'',
            'info'=>'',
            'status'=>false,
            'code'  => 200,
    ];

    public function _initialize(){
        parent::_initialize();
    }

    public function login(){
        
       // debug('begin');
       $data = Request::instance()->post();
       $json = $this->resJson;
       $userModel = new HyAccount();

       $verifyCode = !empty($param['verifyCode'])? $param['verifyCode']: '';
       $isRemember = !empty($param['isRemember'])? $param['isRemember']: '';

       $data = $userModel->login($data['username'], $data['password'], $verifyCode, $isRemember);
       
       if (!$data) {
           $json['info'] = $userModel->getError();
           $json['code'] = 500;
           $json['status'] = false;

           return json($json);
       } 

       $json['info'] = '登录成功！';
       $json['code'] = 200;
       $json['status'] = true;
       $json['data'] = $data;

       // print_r($json);
       return json($json);
    }
}
