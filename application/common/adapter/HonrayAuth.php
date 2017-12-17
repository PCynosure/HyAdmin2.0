<?php
namespace app\common\adapter;
use think\Db;

class HonrayAuth{
    //默认配置
    public $_config = array(
        'AUTH_ON'           => true,                        // 认证开关
        'AUTH_TYPE'         => 1                           // 认证方式，1为实时认证；2为登录认证。
        // 'AUTH_GROUP'        => 'frame_group',                     // 用户组数据表名
        // 'AUTH_GROUP_ACCESS' => 'frame_access',                // 用户-用户组关系表
        // 'AUTH_RULE'         => 'frame_rule',                      // 权限规则表
        // 'AUTH_USER'         => 'frame_user'                       // 用户信息表
    );

    private $auth_key; 

    public function __construct($auth_key) {
        $this->auth_key = $auth_key;
       /* $this->_config['AUTH_GROUP']        = $this->_config['AUTH_GROUP'];
        $this->_config['AUTH_RULE']         = $this->_config['AUTH_RULE'];
        $this->_config['AUTH_USER']         = $this->_config['AUTH_USER'];
        $this->_config['AUTH_GROUP_ACCESS'] = $this->_config['AUTH_GROUP_ACCESS'];
        if (config('AUTH_CONFIG')) {
            //可设置配置项 AUTH_CONFIG, 此配置项为数组。
            $this->_config = array_merge($this->_config, config('AUTH_CONFIG'));
        }*/
    }

    /**
     * [check description]
     * @param  [type] $name     [请求的路径]
     * @param  [type] $uid      [请求的用户]
     * @param  string $relation [description]
     * @return [type]           [description]
     */
    public function check($name, $uid, $relation = 'or') {
        $cache = cache('Auth_'.$this->auth_key);
        $user  = $cache['authList'];
    }
}