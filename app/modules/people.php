<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

ajax_only();

$logged or die('{"err":"nolog"}');

$act = $_POST['act'];

switch($act){
	case 'list':

		$q = textFilter($_POST['val']);
		$doload = intval($_POST['doload']);
		$s_w = $q ? "AND CONCAT(name, ' ', lname) LIKE '%{$q}%'" : '';

		$limit = 20;
		$page = isset($_POST['page']) ? intval($_POST['page'])*$limit : 0;

		$sql_ = mysql_query("SELECT uid, name, lname FROM `users` WHERE uid != '{$uid}' {$s_w} ORDER by uid LIMIT {$page}, {$limit}", 1);

		$res = '';
		if($sql_){

			$friend = new Memcache;
			$friend->addServer('localhost', FR1_PORT);

			foreach($sql_ as $row){
				$check_req = $friend->get("friendreq{$row['uid']}_{$uid}");
				$check_fr = $friend->get("friend{$uid}_{$row['uid']}");
				$req_btn = (!$check_req && !$check_fr) ? '<li onClick="page.send_friend_req('.$row['uid'].', this)">Добавить в друзья</li>' : '';
				$res .= '<div class="people_item">
					<img src="/img/camera_50.gif" class="fl_l">
					<div class="cont">
						<div class="name">'.$row['name'].' '.$row['lname'].'</div>
					</div>
					<div class="actions fl_r">
						<li onClick="im.open('.$row['uid'].');">Отправить сообщение</li>
						'.$req_btn.'
					</div>
					<div class="clear"></div>
				</div>';
			}
		}else{
			if($page == 0) $res = '<div class="info_center">По запросу <b>'.$q.'</b> ничего не найдено</div>';
		}

		if($doload){
			echo $res;
			exit;
		}

		tpl_load('people/head');
		tpl_set(array(
			'{res}' => $res,
			'{load}' => count($sql_) == $limit ? '' : 'no_display'
		));
		tpl_make('cont');

		echo json_encode(array(
			'cont' => $tpl_res['cont'],
			'id' => 'box_people'
		));

	break;
}
exit;