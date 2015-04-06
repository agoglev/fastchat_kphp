<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

if((isset($_POST['logout']) || $_SERVER['REQUEST_URI'] == '/login?logout') && $logged){
	setcookie('uid', $uid, time()-3600);
	setcookie('hash', $hash, time()-3600);
	$pmc->delete('session'.$uid.$hash);
	header('Location: /');
	exit;
}

ajax_only();

$email = mb_strtolower(textFilter($_POST['email']));
$pass = textFilter($_POST['pass']);

if($email && $pass){
	$pass5 = md5('fast'.$pass);

	$check = mysql_query("SELECT uid FROM `users` WHERE email = '{$email}' AND password = '{$pass5}'");

	if($check['uid']){

		extend_auth($check['uid'], md5($check['uid'].$pass.time()));
		echo 'ok';

	}else echo 'no';

}else echo 'no';

exit;