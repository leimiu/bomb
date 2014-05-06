<?php
define('INCLUDE_TARGET','bomb.php');
define('BOMB_INTERFACE','bomb(__FILE__,__LINE__,__FUNCTION__)');
define('BOMB_START_FLAG','BOMB_START');

function _is_main($line){
	$rule = '/.*?function\s+main\(.*/';
	return preg_match($rule,trim($line))===1;
}

function _is_php_tag($line){
	return trim($line) == '<?php';
}

function _is_new_line($line){
	if(preg_match('/class\s+.*/',trim($line))) return false; //排除class定义
	if(preg_match('/switch\s+\(.*/',trim($line))) return false; //排除switch关键字
	if(preg_match('/(private|public|var)\s+\$.*/',trim($line))) return false; //排除类变量定义
	return _is_end_with(trim($line),';') or _is_end_with(trim($line),'{');
}

function _is_end_with($str,$find){
	return ($pos = strripos($str, $find)) !== false && $pos == strlen($str) - strlen($find);
}

function _write_line($fhandler,$line){
	fwrite($fhandler, $line."\r\n");
}

function print_usage(){
	echo '$script dir';
}

function compile_one($src,$dist){
	if(!file_exists($src)){
		return false;
	}

	$fsrc=fopen($src,'r');
	$fdist=fopen($dist,'w');

	if(!$fsrc) die('打开源文件失败：'.$src);
	if(!$fdist) die('打开目标文件失败：'.$dist);


	while($line= fgets($fsrc)){
		$line = rtrim($line);
		
		$main_start_flab=true;//为了处理main和'{'不在同一行的情况
		if(_is_main($line)){

			if(_is_end_with($line,'{')){
				_write_line($fdist, $line.' define("'.BOMB_START_FLAG.'","TRUE");');
			}else{
				_write_line($fdist, $line);
				$main_start_flab = TRUE;
			}

		}else if(_is_php_tag($line)){
			_write_line($fdist, $line.' require_once("'.INCLUDE_TARGET.'");');
		}else if(_is_new_line($line)){
			$new_line="$line ".BOMB_INTERFACE.";";
			_write_line($fdist,$new_line);
		}else{
			
			if($main_start_flab && trim($line) == '{'){
				_write_line($fdist, $line.' define("'.BOMB_START_FLAG.'","TRUE");');
				$main_start_flab = false;
			}else{
				_write_line($fdist, $line);
			}
			
		}

	}


}

function main($dir){
	$home_dir = dirname(__FILE__); 
	chdir($dir) or die('切换目录失败');

	$dist='before_compile';
	if(!is_dir($dist)) mkdir($dist);

	$files = glob("*.php");
	foreach($files as $file){
		echo "compiling $file...\r\n";
		$file_before_compile=$dist.'/'.$file;
		if(file_exists($file_before_compile)){
			exit('貌似已经编译过，请别重新编译。');
		}
		rename($file,$file_before_compile);
		compile_one($file_before_compile,$file);
	}

	//复制必要的文件过去
	copy($home_dir."/bomb.php","bomb.php");
	copy($home_dir."/bomb.class.php","bomb.class.php");
	echo "done\r\n";
}

#var_dump($argv);

if(count($argv)<2){
	print_usage();
}else{
	main(@$argv[1]);
}