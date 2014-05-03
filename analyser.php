<?php
define('BEFORE_COMPILE','before_compile');

if(count($argv) < 2){
	exit('请指定待分析的文件');
}else{
	$stat_data = $argv[1];
}

$data = unserialize(file_get_contents($stat_data));
//var_dump($data);

$dist = 'result';
if(!is_dir($dist)){mkdir($dist);}

foreach($data as $php_file => $file_data){
	echo "analysing $php_file \r\n";
	
	$source_name = dirname($php_file)."/".BEFORE_COMPILE."/".basename($php_file);var_dump($source_name);
	$f_source = fopen($source_name,'r');
	
	$target_name = "./".$dist."/".basename($php_file);var_dump($target_name);
	$f_target = fopen($target_name,'w');
	
	$line_num=1;
	while($line = fgets($f_source)){
		if(isset($file_data[$line_num])){
			$line_count = $file_data[$line_num];
		}else{
			$line_count = 0;
		}
		fwrite($f_target,'/**'.$line_count."*/".$line);
		$line_num ++;
	}
	fclose($f_source);
	fclose($f_target);
}


