<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

if($logged){
	$act = isset($_GET['act']) ? $_GET['act'] : (isset($_POST['act']) ? $_POST['act'] : false);

	$st_files = array('al/friends.js', 'al/friends.css');

	$hints = new Memcache;
	$hints->addServer('localhost', HIN_PORT);

	$friend = new Memcache;
	$friend->addServer('localhost', FR1_PORT);

	/*
		res_type:
		1 - friend
	*/

	switch($act){

		case 'send_request':
			ajax_only();
			$friend_id = intval($_POST['id']);


			$check = $friend->get("friendreq{$friend_id}_{$uid}");

			if(!$check){
				$row = mysql_query("SELECT uid, friends_request, name, lname FROM `users` WHERE uid = '{$friend_id}'");

				$check_req = $friend->get("friendreq{$uid}_{$friend_id}");
				if($check_req){

					$hints->set("user_object{$uid},1:{$friend_id}", "{$row['name']} {$row['lname']}");
					$friend->set("friend{$uid}_{$friend_id}", 1);

					$hints->set("user_object{$friend_id},1:{$uid}", "{$uinfo['name']} {$uinfo['lname']}");
					$friend->set("friend{$friend_id}_{$uid}", 1);

					$friend->delete("friendreq{$uid}_{$friend_id}");

					$num = intval($uinfo['friends_request'])-1;
					if($num < 0) $num = 0;

					$uinfo['friends_request'] = $num;
					$pmc->set('uinfo'.$uid, $uinfo, 3600*24);
					
					echo '{"ok":1}';
					exit;
				}

				if($row['uid'] > 0){
					$friend->add("friendreq{$friend_id}_{$uid}", 1);

					$num = intval($row['friends_request'])+1;

					$cache = $pmc->get('uinfo'.$friend_id);
					if($cache['uid'] > 0){
						$cache['friends_request'] = $num;
						$pmc->set('uinfo'.$friend_id, $cache, 3600*24);
					}

					mysql_query("UPDATE `users` SET friends_request = '{$num}' WHERE uid = '{$friend_id}'");

					$queue = new Memcache;
					$queue->connect('127.0.0.1', QUE_PORT);
					$queue->add("queue(notify{$friend_id})", json_encode(array('type' => 'req_count', 'cnt' => $num)));

					echo '{"ok":1}';

				}else echo '{"err":1}';

			}else echo '{"err":1}';

			exit;
		break;

		case 'accept':
			ajax_only();
			$id = intval($_POST['id']);

			$check = $friend->get("friendreq{$uid}_{$id}");
			if($check){
				$get_uinf = mysql_query("SELECT name, lname FROM `users` WHERE uid = '{$id}'");

				$hints->set("user_object{$uid},1:{$id}", "{$get_uinf['name']} {$get_uinf['lname']}");
				$friend->set("friend{$uid}_{$id}", 1);

				$hints->set("user_object{$id},1:{$uid}", "{$uinfo['name']} {$uinfo['lname']}");
				$friend->set("friend{$id}_{$uid}", 1);

				$friend->delete("friendreq{$uid}_{$id}");

				$num = intval($uinfo['friends_request'])-1;
				if($num < 0) $num = 0;

				$uinfo['friends_request'] = $num;
				$pmc->set('uinfo'.$uid, $uinfo, 3600*24);

				mysql_query("UPDATE `users` SET friends_request = '{$num}' WHERE uid = '{$uid}'");

				$queue = new Memcache;
				$queue->connect('127.0.0.1', QUE_PORT);
				$queue->add("queue(notify{$uid})", json_encode(array('type' => 'req_count', 'cnt' => $num)));
			}
			exit;
		break;

		case 'reject':
			ajax_only();
			$id = intval($_POST['id']);
			$check = $friend->get("friendreq{$uid}_{$id}");
			if($check){
				$friend->delete("friendreq{$uid}_{$id}");

				$num = intval($uinfo['friends_request'])-1;
				if($num < 0) $num = 0;

				$uinfo['friends_request'] = $num;
				$pmc->set('uinfo'.$uid, $uinfo, 3600*24);

				mysql_query("UPDATE `users` SET friends_request = '{$num}' WHERE uid = '{$uid}'");

				$queue = new Memcache;
				$queue->connect('127.0.0.1', QUE_PORT);
				$queue->add("queue(notify{$uid})", json_encode(array('type' => 'req_count', 'cnt' => $num)));
			}
			exit;
		break;

		case 'delete':
			ajax_only();
			$id = intval($_POST['id']);

			$check = $friend->get("friend{$uid}_{$id}");

			if($check){
				$hints->delete("user_object{$id},1:{$uid}");
				$hints->delete("user_object{$uid},1:{$id}");
				$friend->delete("friend{$id}_{$uid}");
				$friend->delete("friend{$uid}_{$id}");
			}
			exit;
		break;

		case 'request':
			ajax_only();

			$result = $friend->get("requests{$uid}#100");
			$exp = explode(',', $result);

			$cnt = $exp[0];
			if($cnt > 0){
				$ids = array();
				for($i = 1; $i < count($exp); $i += 3) $ids[] = $exp[$i];
				$exp = false;

				$ids = implode(',', $ids);
				$sql_ = mysql_query("SELECT uid, name, lname, last_update FROM `users` WHERE uid IN ({$ids})", 1);
				$ids = false;

				$res = array();
				foreach($sql_ as $row){
					$online = $row['last_update'] > $online_time ? 1 : 0;
					$res['f'.$row['uid']] = array('name' => $row['name'].' '.$row['lname'], 'online' => $online, 'id' => $row['uid']);
				}

			}else $res = array();

			echo json_encode(array(
				'cnt' => $cnt,
				'res' => $res
			));
			exit;
		break;

		case 'load_all':
			ajax_only();

			$id = intval($_POST['uid']);

			$result = $hints->get("user_hints{$id},1()");
			$exp = explode(',', $result);
			$cnt = $exp[0];
			if($cnt){
				$ids = array();
				for($i = 1; $i < count($exp); $i += 2) $ids[] = $exp[$i+1];
				$exp = false;

				$ids = implode(',', $ids);
				$sql_ = mysql_query("SELECT uid, name, lname, last_update FROM `users` WHERE uid IN ({$ids})", 1);
				$ids = false;

				$res = array();
				foreach ($sql_ as $row) $res[] = array('name' => $row['name'].' '.$row['lname'], 'online' => ($row['last_update'] > $online_time ? 1 : 0), 'id' => $row['uid']);
				echo json_encode($res);
			}else echo '[]';
			exit;
		break;

		case 'delete_box':
			ajax_only();

			$id = intval($_POST['id']);
			$row = mysql_query("SELECT name, lname FROM `users` WHERE uid = '{$id}'");
			if($row['name']){
				tpl_load('friends/del_box');
				tpl_set(array(
					'{name}' => $row['name'].' '.$row['lname'],
					'{id}' => $id
				));
				tpl_make('res');
				echo json_encode(array('res' => $tpl_res['res']));
			}else echo json_encode(array('err' => 1));
			exit;
		break;

		default:

		$get_id = $uid;

		$result = $hints->get("user_hints{$uid},1#15()");

		$exp = explode(',', $result);

		tpl_load('friends/friend');
		$friends_tpl = str_replace(array("\n", "\t", "\r"), '', $tpl_cont);

		$cnt = $exp[0];
		if($exp[0] > 0){
			$ids = array();
			for($i = 1; $i < count($exp); $i += 2) $ids[] = $exp[$i+1];
			$exp = false;

			$ids = implode(',', $ids);
			$sql_ = mysql_query("SELECT uid, name, lname, last_update FROM `users` WHERE uid IN ({$ids})", 1);
			$ids = false;

			foreach($sql_ as $row){
				tpl_set(array(
					'{name}' => $row['name'].' '.$row['lname'],
					'{online}' => $row['last_update'] > $online_time ? 'online' : '',
					'{id}' => $row['uid']
				));
				tpl_make('res');
			}
		}else $tpl_res['res'] = '<div class="info_center" style="padding: 100px 0;">У вас нет друзей</div>';

		tpl_load('friends/head');
		tpl_set(array(
			'{res}' => $tpl_res['res'],
			'{cnt}' => $cnt > 0 ? gram($cnt, 'друг', 'друга', 'друзей') : 'нет друзей',
			'{load_but}' => $cnt > 15 ? '' : ' no_display',
			'{req_cnt}' => $uinfo['friends_request'] > 0 ? '+'.$uinfo['friends_request'] : ''
		));
		tpl_make('cont');

		$initJS = 'friends.uid = '.$get_id.';
friends.tpl = \''.$friends_tpl.'\';
friends.loadFriends();
friends.req_tpl = \'<div class="one_request" id="req{id}"><img src="/img/camera_100.gif" class="fl_l"><div class="cont fl_l"><div class="name"><a href="/">{name}</a></div><div class="online">{online}</div><div class="buttons"><button class="button fl_l" onClick="friends.accept({id});">Принять</button><button class="button inline fl_l" onClick="friends.reject({id});">Откланить</button><div class="clear"></div></div></div><div class="clear"></div></div>\'';
	}
}else{
	tpl_load('nolog_err');
	tpl_make('cont');
}