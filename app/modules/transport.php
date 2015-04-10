<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

if(!$logged){
	if(isset($_POST['act']) && $_POST['act'] == 'reinit') die('{"no_log":1}');
	else die('<h1>no logged :( </h1>');
}

$im = new Memcache;
$im->connect("localhost", IM_PORT);


$time = $im->get('force_timestamp'.$uid);

$user_secret = $im->get('secret'.$uid);
if(!$user_secret){
	$secret = md5($uid.time());
	$im->set('secret'.$uid, substr($secret, 0, 8));
	$user_secret = $im->get('secret'.$uid);
}

$im_secret1 = "0123456789ABCDEF";
$im_secret2 = "0123456789ABCDEF";
$im_secret3 = "0123456789ABCDEF";
$im_secret4 = "0123456789ABCDEF";

$subnet = extractSubnetwork($_SERVER['HTTP_X_REAL_IP']);
$nonce = sprintf("%08x", mt_rand(0, 0x7fffffff));
$hlam1 = substr(md5($nonce . $im_secret1 . $subnet), 4, 8);

$utime = sprintf("%08x", time());
$utime_xor = xor_str($utime, $hlam1);
$uid = sprintf("%08x", $uid);
$uid_xor = xor_str($uid, substr(md5($nonce . $subnet . $utime_xor . $im_secret2), 6, 8));
$check = substr(md5($utime . $uid . $nonce . $im_secret4 . $subnet . $user_secret), 12, 16);

$im_session = $nonce . $uid_xor . $check . $utime_xor;

if(isset($_POST['act']) && $_POST['act'] == 'reinit'){
	echo json_encode(array('ts' => $time, 'ses' => $im_session));
	exit;
}

tpl_load('transport');
tpl_set(array(
	'{ts}' => $time,
	'{session}' => $im_session
));
tpl_make('cont');

echo $tpl_res['cont'];
exit;
