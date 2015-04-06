<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/
	
$id = substr($_SERVER['SCRIPT_NAME'], 3);

if($id == $uid) $row = $uinfo;
else{
	$row = $pmc->get('uinfo'.$id);
	if(!$row['uid']){
		$row = mysql_query("SELECT uid, email, name, lname, photo FROM `users` WHERE uid = '{$id}'");
		if($row['uid']) $pmc->set('uinfo'.$id, $row, 3600*24);
	}
}

if(!$row['uid']){
	tpl_load('profile/not_found');
}else{

	if($row['photo']){
		$photo = new Memcache; 
		$photo->connect('127.0.0.1', 11233);

		//$photos = $photo->get("photo{$id},{$row['photo']}(id,locationps)");

		//print_r($photos);
		//exit;
	}else $ava = '/img/camera_400.gif';

	tpl_load('profile/main');
	tpl_set(array(
		'{id}' => $id,
		'{name}' => $row['name'],
		'{lname}' => $row['lname']
	));
}
tpl_make('cont');