<?php
namespace app\common\model;

use think\Model;

class HyCrypt{
	// 密钥
	public $key;
	// 每一个密文块的长度
	// public $blockSize;
	// 加密源
	public $mcryptModule;


	// public static $iv;
	public function __construct($key){
		
		// $this->key = $key;

		$this->mcryptModule = mcrypt_module_open('rijndael-256', '', 'cbc', '');
		$this->key = hash('sha256', $key, true);
		$this->key = substr($this->key, 0, mcrypt_enc_get_key_size($this->mcryptModule));

	}

	public function encrypt($data){

		// var_dump("encrypt");
		// var_dump($this->key);
		// var_dump($data);
		$data = $this->pad($data);
		// var_dump($data);
		$ivSize = mcrypt_enc_get_iv_size($this->mcryptModule);
		// var_dump($this->ivSize);
		$iv = mcrypt_create_iv($ivSize,MCRYPT_DEV_URANDOM);
		// self::$iv = $iv;
		// var_dump("iv: ".$iv);
		mcrypt_generic_init($this->mcryptModule,$this->key,$iv);
		$encrypted = mcrypt_generic($this->mcryptModule, $data);

		
		return base64_encode($iv.$encrypted);
	}

	

	public function decrypt($data){
		// var_dump("decrypt");
		// var_dump($this->key);
		// var_dump($data);
		$data = base64_decode($data);

		$ivSize = mcrypt_enc_get_iv_size($this->mcryptModule);
		$iv = substr($data,0,$ivSize);
		$data = substr($data, $ivSize);

		// var_dump("iv: ".$iv);
		mcrypt_generic_init($this->mcryptModule, $this->key, $iv);
		$decrypted = mdecrypt_generic($this->mcryptModule,$data);
		// var_dump($decrypted);
		$decrypted = $this->unpad($decrypted);
		return $decrypted;
	}

	// PCK填充
	private function pad($data){
		$blockSize = mcrypt_enc_get_block_size($this->mcryptModule);
		$pad = $blockSize - (strlen($data)%$blockSize);
		if($pad<=$blockSize){
			$data = $data . str_repeat(chr($pad), $pad);
		}
		return $data;
	}

	private function unpad($data){
		$pad = ord($data[strlen($data)-1]);
		return substr($data,0,-$pad);
	}

	public function __destruct(){
		mcrypt_generic_deinit($this->mcryptModule);
		mcrypt_module_close($this->mcryptModule);
	}
}