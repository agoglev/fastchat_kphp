<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

ajax_only();

$email = textFilter(strtolower($_POST['email']));
$name = textFilter($_POST['name']);
$lname = textFilter($_POST['lname']);
$pass = textFilter($_POST['pass']);
$pass2 = textFilter($_POST['pass2']);

if(preg_match('/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i', $email)){
	if(strlen($name) > 1 && preg_match("/^[\s\x{600}-\x{6FF}a-zA-Zа-яА-Я]+$/iu", $name)){
		if(strlen($lname) > 1 && preg_match("/^[\s\x{600}-\x{6FF}a-zA-Zа-яА-Я]+$/iu", $lname)){
			if($pass == $pass2){
				$check_email = mysql_query("SELECT COUNT(uid) as cnt FROM `users` WHERE email = '{$email}'");
				if(!$check_email['cnt']){
					$pass = md5('fast'.$pass);
					mysql_query("INSERT INTO `users` (email, password, name, lname) VALUES ('{$email}', '{$pass}', '{$name}', '{$lname}')");

					extend_auth($mysql_iid, md5($mysql_iid.$pass2.time()));
					echo 'ok';

				}else echo 'mail';
			}
		}
	}
}
exit;