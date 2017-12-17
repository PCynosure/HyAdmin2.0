<?php
namespace app\common\model;

use think\Model;
use think\Config;
use think\Db;
use app\common\model\HyAuth;
use think\Session;

class HyAccount extends Model
{
    public $name = 'user';

    public function roles()
    {
        return $this->belongsToMany('HyRole', 'frame_access', 'role_id', 'user_id');
    }

	public function login($username, $password, $verifyCode = '', $isRemember = false, $type = false){

        // dump($username);
        if (!$username) {
            $this->error = '帐号不能为空';
            return false;
        }
        if (!$password){
            $this->error = '密码不能为空';
            return false;
        }
        if (config('common.IDENTIFYING_CODE') && !$type) {
            if (!$verifyCode) {
                $this->error = '验证码不能为空';
                return false;
            }
            // $captcha = new HonrayVerify(config('captcha'));
            // if (!$captcha->check($verifyCode)) {
            //     $this->error = '验证码错误';
            //     return false;
            // }
        }
        
        // $username = act_decrypt($username);
        // debug('begin');
        $userInfo = $this->where(['username'=>$username])->find();
        // debug('end');
        // print_r(debug('begin','end',6).'s');

        if (!$userInfo) {
            $this->error = '帐号不存在';
            return false;
        }

        /*
        
        //先将前台提交过来的密码进行解密
        $key = substr($user['password'], 5, 32);
        $password = aes_decrypt_base($password,$key);

        //从后台取出来的密码也进行解密
        $pwdsha1 = $this->pwdDecrypt($userInfo['password']);
        

        if ($password !== $pwdsha1) {
            $this->error = '密码错误';
            return false;
        }
        */

        if($userInfo['password']!=$password){
            $this->error = '密码错误';
            return false;
        }

        if ($userInfo['status'] === 0) {
            $this->error = '帐号已被禁用';
            return false;
        }
        // 获取菜单和权限,权限验证时以distance为判断基准
        // debug('begin');
        $dataList = $this->getMenuAndRule($userInfo['id']);
        // debug('end');
        // print_r(debug('begin','end',6).'s');

        if (!$dataList['menusList']) {
            $this->error = '没有权限';
            return false;
        }

       /* if ($isRemember || $type) {
            $secret['username'] = $username;
            $secret['password'] = $password;
            $data['rememberKey'] = encrypt($secret);
        }*/

        // 保存缓存        
        $data = [];
        // session_start();
        
        $userInfo = $userInfo->toArray(); 

        $info['userInfo'] = $userInfo;
        $info['sessionId'] = session_id();

        //这个authKey就是判定某个用户在线登录状态的唯一标识，在权限认证里面通过这个authKey，在缓存中取出这个用户的具体信息
        $authKey = act_encrypt($userInfo['username'].$userInfo['password'].$info['sessionId']);
        

        $info['authList'] = $dataList['rulesList'];
        $info['authKey'] = $authKey;
        
        // dump($info);
        // 存入缓存
        cache('Auth_'.$authKey, null);
        cache('Auth_'.$authKey, $info, config('common.LOGIN_SESSION_VALID'));
        cache('menusList',null);
        cache('menusList',$dataList['menusList']);

        // dump(config('common.LOGIN_SESSION_VALID'));
        // 返回信息
        
        $data['authKey']        = $authKey;
        $data['sessionId']      = $info['sessionId'];
        $data['userInfo']       = $userInfo;
        $data['authList']       = $dataList['rulesList'];
        $data['menusList']      = $dataList['menusList'];

        Session::set('authKey',$authKey);
        Session::set('userId',$userInfo['id']);
        Session::set("sessionId",$info['sessionId']);
        
        return $data;
    }

    public function getMenuAndRule($u_id){
        
        if ($u_id === 1) {        
            $menusList = Db::name('frame_rule')
                            ->where([
                                'statis'=>1,
                                'type'=>['in',['system','nav','menu']]
                                ])
                            ->order('sort asc')
                            ->select();

            $rulesList = 'all';
        } else {

            /*
            
            debug('begin');  
            $roles = $this->get($u_id)->roles;
            debug('end');
            print_r(debug('begin','end',6).'s'."<br>");*/

            // debug('begin');  
            $roles = Db::name('frame_access')
                        ->alias('fa')
                        ->join('__FRAME_ROLE__ ro','user_id = '.$u_id.' AND role_id = ro.id')
                        ->column('rules');
            

            // debug('end');
            // print_r(debug('begin','end',6).'s'."<br>");
      
            $ruleIds = [];
            
            // foreach ($roles as $k => $v) {
            //    $ruleIds = array_unique(array_merge($ruleIds,explode(',',$v)));
            // }
            
            // $map['id'] = ['in',$ruleIds];
     
            $map['id'] = ['in',$roles[0]];
            $map['status'] = 1;

            
            // debug('begin');
            $rules = Db::name('frame_rule')->where($map)->field('id,pid,name,type,source,distance,icon')->select();
            
            
            $menusList = $rulesList = [];
            

            foreach ($rules as $k => $v) {
                if(in_array($v['type'],['menu','url'])){
                    $rulesList[$v['id']] = $v['distance'];
                }        
            }

            foreach ($rules as $k => $v) {
                if(!in_array($v['type'],['system','nav','menu'])){
                    continue;
                }
                if($v['pid'] == 0){
                    unset($v['distance']);
                    $menusList[$k] = $v;
                }

                foreach ($rules as $k1 => $v1) {
                    if(!in_array($v1['type'],['system','nav','menu'])){
                        continue;
                    }

                    if($v1['pid'] == $v['id']){
                        unset($v1['distance']);
                        $menusList[$k]['child'][] = $v1;
                    }
                }
            }
            
        }

        $ret = [
            'menusList' => $menusList,
            'rulesList' => $rulesList
        ];

        return $ret;
    }


    public function getRole(){

    }

    /*public function switchAuth(){
        $u_id = Session::get('userId');
        if($u_id == 1){

        }else{
            $post = Request::instance()->post();
            $rule = $post['rule'];
            

            $rules = Db::name('frame_access')->where(['user_id'=>$u_id])->column('role_id');
            
            if(!in_array($rule,$rules)){
                return false;
            }

            $dataList = $this->getMenuAndRule();

        }
        
    }*/

    /**
     * 密码加密
     * @param string $pwd
     * @return string
     */
    public function pwdEncrypt($pwd, $isSha1=false){
        // C('PWD_HASH_ADDON')=>'@*H$%Y:1&amp;4'
        if(!$isSha1) $pwd = sha1($pwd.Config::get('crypto.PWD_HASH_ADDON'));
        return aes_encrypt($pwd, Config::get('crypto.CRYPT_KEY_PWD'));
    }

    /**
     * 密码解密
     * @param string $pwd
     * @return string
     */
    public function pwdDecrypt($pwd){
        /*C('CRYPT_KEY_PWD')=>a@#y$V4%9i$&amp;*JG%$#Li*(K:!*3%Q~p0*/
        return aes_decrypt($pwd, Config::get('crypto.CRYPT_KEY_PWD'));
    }
}
