<?php
require_once('bomb.class.php');

function bomb($file,$line,$function=NULL){
	$bomb = Bomb::getInstance();
	$bomb->bomb($file,$line,$function);
}