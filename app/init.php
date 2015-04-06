<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

include 'app/config.php';

$pmc = new Memcache; 
$pmc->addServer('localhost', PMC_PORT); 

include 'app/lib/tpl.php';
include 'app/lib/mysql.php';
include 'app/functions.php';

$server_time = time();
$online_time = $server_time-600;

if($_COOKIE['uid'] > 0){
	$uid = intval($_COOKIE['uid']);
	$hash = $_COOKIE['hash'];

	if($pmc->get('session'.$uid.$hash) == 'logged'){
		$logged = true;

		$uinfo = get_user_data($uid);

		if($online_time > $uinfo['last_update']){
			$uinfo['last_update'] = $server_time;
			$pmc->set('uinfo'.$uid, $uinfo, 3600*24);
			extend_auth($uid, $hash);
			mysql_query("UPDATE `users` SET last_update = '{$server_time}' WHERE uid = '{$uid}'");
		}
	}
}