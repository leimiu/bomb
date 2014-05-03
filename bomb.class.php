<?php
define('MAX_DIE', 2); //一个地方最多挂掉的次数
define('PROBABILITY',0.8);//促发die的概率
class Bomb{

	var $base_dir;
	var $stat_data;

	private function __construct(){
		$this->base_dir = dirname(__FILE__).'/bomb/';
		if(!is_dir($this->base_dir)){
			mkdir($this->base_dir) or die('创建目录失败！');
		}
		$this->load_data();
		date_default_timezone_set('Asia/Chongqing');
	}

	private static $_instance;
	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	public function __clone(){
		trigger_error('Clone is not allow!',E_USER_ERROR);
	}

	private function load_data(){
		$stat_data_path = $this->base_dir.'/stat.data';
		if(file_exists($stat_data_path)){
			$this->stat_data = unserialize(file_get_contents($stat_data_path));
		}

		//必要时创建一个空的数组
		if(!isset($this->stat_data)){
			$this->stat_data = array();
		}
	}

	private function store_data(){
		$stat_data_path = $this->base_dir.'/stat.data';
		file_put_contents($stat_data_path, serialize($this->stat_data));
	}
	private function record_data($file,$line){
		if(!array_key_exists($file, $this->stat_data)){
			$this->stat_data[$file]=array();
		}

		if(!array_key_exists($line, $this->stat_data[$file])){
			$this->stat_data[$file][$line]=1;
		}else{
			$this->stat_data[$file][$line]=$this->stat_data[$file][$line] + 1;
		}
	}
	private function log($msg){
		$time=date("Y-m-d H:i:s");
		$flog = fopen($this->base_dir."/bomb.log",'a');
		if(!$flog) exit("无法创建日志文件！");
		fwrite($flog, "[".$time."]".$msg."\r\n");
		fclose($flog);
	}

	private function do_die($msg){
		$this->store_data();
		$this->log('Die with message: '.$msg);
		exit($msg);
	}
	private function is_trigger_die($file,$line){
		if(isset($this->stat_data[$file][$line]) && $this->stat_data[$file][$line] >= MAX_DIE){
			return FALSE;
		}else{
			$rand = mt_rand()/mt_getrandmax();
			return $rand <= PROBABILITY;
		}
	}

	public function bomb($file,$line,$function=NULL){
		if($this->is_trigger_die($file,$line)){
			$this->record_data($file,$line);
			$this->do_die('die operation is trigger in function : '.$function."($file#$line)");
		}

	}
}
