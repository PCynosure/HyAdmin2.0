<?php
namespace app\common\adapter;

use think\Model;
use think\Db;
// use app\common\adapter\HonrayAuth;

class HyAuth
{

    private static $_instance;

    private $auth_key;

    public function __construct($auth_key) 
    {
        $this->auth_key = $auth_key; 
    }

    /*public static function getInstance($auth_key)
     {
        if (!(self::$_instance instanceof HonrayAuth)) {
            self::$_instance = new HonrayAuth($auth_key);
        }
        return self::$_instance; 
    }*/

    //权限认证
    /*public function check($names, $uid, $auth_type = 1,$relation='or') 
    {
        self::getInstance($this->auth_key)->_config['AUTH_TYPE'] = $auth_type;
        if ($uid == 1){ 
            return true;
        }
        if (!self::getInstance($this->auth_key)->check($names, $uid, $relation)) {
            return false;
        } else {
            return true;
        }
    }*/

    public function check($ruleName, $uid, $auth_type = 1,$relation='or') 
    {
        self::getInstance($this->auth_key)->_config['AUTH_TYPE'] = $auth_type;
        if ($uid == 1){ 
            return true;
        }

        $cache = cache('Auth_'.$this->auth_key);
        $userAuth  = $cache['authList'];

        if (!in_array($ruleName,$userAuth)) {
            return false;
        } else {
            return true;
        }
    }
   

}