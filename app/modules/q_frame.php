<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

if(!$uid) die('no_log');

$queue = new Memcache;
$queue->connect('127.0.0.1', QUE_PORT);

$ip = ip2long($_SERVER['HTTP_X_REAL_IP']);
$queue->get("upd_secret{$uid}");

$data = $queue->get("timestamp_key{$uid},{$ip},25(notify{$uid})");
$data = json_decode($data, true);

if(isset($_POST['data_only'])){
	echo json_encode(array(
		'key' => $data['key'],
		'ts' => $data['ts'],
		'id' => $uid
	));
	exit;
}

tpl_load('q_frame');
tpl_set(array(
	'{ts}' => $data['ts'],
	'{key}' => $data['key'],
	'{uid}' => $uid
));
tpl_make('cont');
echo $tpl_res['cont'];
exit;