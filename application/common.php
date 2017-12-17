<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Config;
use think\Session;



// aes加密
function aes_encrypt($data,$key){
	$encryptor = new app\common\model\HyCrypt($key);
	return $encryptor->encrypt($data);
}	

function aes_decrypt($data,$key){
	$decryptor = new app\common\model\HyCrypt($key);
	return $decryptor->decrypt($data);
}


/**
 * 基础AES解密
 * @param string $text
 * @param string $key
 * @return string
 */
function aes_decrypt_base($text,$key){
	/*因为前台传过来的用户名密文后面是加了七位的随机数的*/
	$rand=substr($text, -7);
	//返回从后面数的7个字符
	$text=substr($text, 0, -7);
	//返回从0到 长度-7 的字符 如 abcdefghijk => abcd
	//删除字符串右边的值 '\0'
	//mcrypt_decrypt使用给定参数解密密文
	//用于进行crypto加密的密文是一个对象，所以要用base64进行转码
	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($text), MCRYPT_MODE_CBC, substr($key, 5, 9).$rand), "\0");
}

/**
 * 通信信息加密
 * @param string $id
 * @return string
 */
function act_encrypt($data){
	if(!$data) return '';

	// dump(session_id());7m36oelbge60vv0ln07ivoa5l2
	return 'encrypt_act-'.aes_encrypt(session_id().$data,Config::get('crypto.CRYPT_KEY_ACT'));
}
/**
 * 通信信息解密
 * @param string $id
 * @return string
 */
function act_decrypt($data=''){
	if(!$data) return '';
	/*session_id()返回当前通信的id*/
	return str_replace(session_id(), '', aes_decrypt(substr($data,strlen('encrypt_act-')),Config::get('crypto.CRYPT_KEY_ACT')));
}


/**
 * [tokenBuilder 令牌生成器]
 * @param  boolean $value [令牌初始值]
 * @return [type]         [description]
 */
function tokenBuilder($value = false){
	if(Session::has('hyToken'))
		Session::set('hyToken',null);

	$token = strtr(md5(rand(10000000, 99999999)), 'ajfasw', 'homyit');
	
	if($value) Session::set($token,$value);
	else Session::set('hyToken',$token);

	return $token;
}

/**
 * [tokenValidator 令牌验证器]
 * @param  boolean $key [description]
 * @return [type]       [description]
 */
function tokenValidator($key=false){
	if(!$key) {
		$token=Session::set('hyToken');
		Session::set('hyToken',null);
		return $token ?: false;
	}
	return Session::has($key) ? Session::get($key) : false;
}