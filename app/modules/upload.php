<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

ajax_only();

if(!$logged) die();

$act = $_GET['act'];

switch($act){
	case 'ava':

		$file = $_FILES['up_file'];

		if(!in_array($file['type'], array('image/gif', 'image/png', 'image/jpeg', 'image/jpg'))){
			echo json_encode(array('err' => 'format'));
			exit;
		}
		if($file['size'] > (15*1024*1024)){
			echo json_encode(array('err' => 'size'));
			exit;
		}

		$storage = new Memcache; 
		$storage->connect('127.0.0.1', 11033); 

		switch($file['type']){
			case 'image/gif': $ext = 'gif'; break;
			case 'image/png': $ext = 'png'; break;
			case 'image/jpg':
			case 'image/jpeg': $ext = 'jpg';
		}
		$volume_id = 1000;

		$tmp = $file['tmp_name'];
		$ret = $storage->get("file{$volume_id},{$tmp}");

		if ($ret !== false) { 
    		$a = explode (",", $ret); 
  			$secret = $a[0]; 
  			$local_id = $a[1];

  			$photo = new Memcache; 
			$photo->connect('127.0.0.1', 11233);

			$pid = $photo->get("new_photo{$uid},-1"); 

			mysql_query("UPDATE `users` SET photo = '{$pid}' WHERE uid = '{$uid}'");
			$uinfo['photo'] = $pid;
			$pmc->set('uinfo'.$uid, $uinfo, 3600*24);

			$arr = array();
			$arr['m'] = $photo->get("add_photo_location_engine_m{$uid},{$pid},{$volume_id},{$local_id},1,{$secret}");
			$arr['p'] = $photo->get("add_photo_location_engine_p{$uid},{$pid},{$volume_id},{$local_id},1,{$secret}");
			$arr['x'] = $photo->get("add_photo_location_engine_x{$uid},{$pid},{$volume_id},{$local_id},1,{$secret}");
			$arr['y'] = $photo->get("add_photo_location_engine_y{$uid},{$pid},{$volume_id},{$local_id},1,{$secret}");
			$arr['s'] = $photo->get("add_photo_location_engine_s{$uid},{$pid},{$volume_id},{$local_id},1,{$secret}");

			echo json_encode(array(
				'ok' => 1,
				'link' => "/v{$volume_id}/{$local_id}/{$secret}.{$ext}",
				'status' => $arr,
				'data' => array(
					'uid' => $uid,
					'pid' => $pid,
					'volume_id' => $volume_id,
					'local_id' => $local_id,
					'ext' => $ext,
					'secret' => $secret
				)
			));
		}else echo json_encode(array('err' => 'noupload'));
	break;
}
exit;