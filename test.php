<?php
function _is_main($line){
	$rule = '/.*?function\s+main\(.*/';
	return preg_match($rule,trim($line))===1;
}

function _is_php_tag($line){
	return trim($line) == '<?php';
}

function _is_new_line($line){
	return _is_end_with(trim($line),';');
}

function _is_end_with($str,$find){
	return ($pos = strripos($str, $find)) !== false && $pos == strlen($str) - strlen($find);
}

$f = fopen('/etc/rc.local','r');
while($line = fgets($f,1024)){
	#echo $line;
	_is_main($line);
	_is_end_with($line,">");
	_is_new_line($line);
	_is_php_tag($line);
}
