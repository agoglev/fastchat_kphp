<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

$tpl_res = array();
$tpl_sets = array();
$tpl_cont = '';
$tpl_blocks = array();

function tpl_load($name){
	global $tpl_cont;

	$path = 'app/tpl/'.$name.'.tpl';

	is_file($path) or die('Невозможно загрузить шаблон <b>'.$name.'.tpl</b>');

	$tpl_cont = file_get_contents($path);
}

function tpl_set($field, $value = false){
	if(is_array($field)){
		foreach($field as $k => $v) tpl_set($k, $v);
		return;
	}
	global $tpl_sets;
	$tpl_sets[$field] = $value;
}

function tpl_block($name, $value = ''){

	if(is_array($name)){
		foreach($name as $k => $v) tpl_block($k, $v);
		return;
	}

	global $tpl_blocks;
	$tpl_blocks["'\\[".$name."\\](.*?)\\[/".$name."\\]'si"] = $value;
}

function tpl_make($name){
	global $tpl_cont, $tpl_res, $tpl_sets, $tpl_blocks;

	$res = $tpl_cont;

	if(count($tpl_blocks)){
		$keys = array_keys($tpl_blocks);
		$value = array_values($tpl_blocks);
		$res = preg_replace($keys, $value, $res);
		$tpl_blocks = array();
	}

	if(count($tpl_sets) > 0){
		$keys = array_keys($tpl_sets);
		$values = array_values($tpl_sets);
		$res = str_replace($keys, $values, $res);
		$tpl_sets = array();
	}

	$tpl_res[$name] .= $res;
}