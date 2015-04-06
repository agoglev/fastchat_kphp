<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

$im = new Memcache;
$im->connect("localhost", IM_PORT);

$act = isset($_GET['act']) ? $_GET['act'] : (isset($_POST['act']) ? $_POST['act'] : false);


/*
	Actions

	50 - is typing
	51 - seen all history
	100 - update chat info

	Sublists

	195:1 - new messages
*/

function get_new_counts($uid, $ids = false){
	global $im;

	$counts = $ids_res = array();

	$new_messages = $im->get("sublistx{$uid}_128,195:1#-1,1");
	$new_messages = explode(',', $new_messages);
	for($i = 1; $i < count($new_messages); $i += 2){
		$peer = $new_messages[$i+1];
		if(!$counts[$peer]){
			$counts[$peer] = 1;
			if($ids) $ids_res[$peer] = array($new_messages[$i]);
		}else{
			$counts[$peer] += 1;
			if($ids) $ids_res[$peer][] = $new_messages[$i];
		}
	}

	return array('all' => $new_messages[0], 'peers' => $counts, 'unicue' => count($counts), 'ids' => $ids_res);
}

switch($act){

	case 'send':
		ajax_only();

		$peer = intval($_POST['peer']);
		$msg = textFilter($_POST['msg']);

		if(!$msg) echo json_encode(array('err' => 1));

		$bayes = new Memcache;
		$bayes->addServer('localhost', BAY_PORT);

		$random_tag = mt_rand(1111111, 9999999);
		$bayes->set("current_text{$random_tag}", "\x1uid\x20{$uid}\t\x1out\x201\t".$msg);
		$test_spam = $bayes->get("test{$random_tag}");
		$random_tag2 = mt_rand(1111111, 9999999);
		$bayes->set("current_text{$random_tag2}", "\x1uid\x20{$peer}\t".$msg);
		$test_spam2 = $bayes->get("test{$random_tag2}");


		if($test_spam == 'spam' || $test_spam2 == 'spam'){
			echo json_encode(array('spam' => 1));
			exit;
		}

		$rnd_tag = $uid.mt_rand(11111, 99999);
		$im->set("newmsg{$uid}#{$rnd_tag}", "3,{$peer}\n\t{$msg}");
		$iid = $im->get("newmsgid{$uid}#{$rnd_tag}");
		$rnd_tag = $peer.mt_rand(11111, 99999);
		$im->set("newmsg{$peer}#4", "1,{$uid},{$iid}\n\t{$msg}");
		$iid2 = $im->get("newmsgid{$peer}#{$rnd_tag}");

		$new_msg = get_new_counts($peer);
		$im->set("history_action{$peer}#100", json_encode(array('peer' => $uid, 'name' => $uinfo['name'].' '.$uinfo['lname'], 'msg' => $msg, 'dialogs' => $new_msg['unicue'], 'cnt' => $new_msg['peers'][$uid], 'online' => 1)));

		$queue = new Memcache;
		$queue->connect('127.0.0.1', QUE_PORT);
		$queue->add("queue(notify{$peer})", json_encode(array('type' => 'msg_count', 'cnt' => $new_msg['all'])));

		$peer_info = mysql_query("SELECT name, lname, last_update FROM `users` WHERE uid = '{$peer}'");
		$online = $peer_info['last_update'] > $online_time ? 1 : 0;
		$im->set("history_action{$uid}#100", json_encode(array('peer' => $peer, 'name' => $peer_info['name'].' '.$peer_info['lname'], 'msg' => $msg, 'online' => $online)));

		echo json_encode(array('id' => $iid, 'test' => $test_spam, 'rest_prob' => $bayes->get("test_prob{$random_tag}"), 'test2' => $test_spam2, 'test_prop2' => $bayes->get("test_prob{$random_tag2}") ));
		exit;
	break;

	case 'history':
		ajax_only();
		$peer = intval($_POST['peer']);

		$msg_list = $im->get("peermsglist{$uid}_{$peer}#-1,-30");
		$exp = explode(',', $msg_list);

		$peer_inf = $pmc->get('uinfo'.$peer);
		if(!$peer_inf['uid']) $peer_inf = mysql_query("SELECT name, lname, last_update FROM `users` WHERE uid = '{$peer}'");

		if(intval($exp[0]) > 0){
			$res = '';

			$exp = array_reverse($exp);

			$last_uid = 0;

			for($i = 0; $i < count($exp)-1; $i++){
				$msg_id = $exp[$i];
				$msg = $im->get("message{$uid}_{$msg_id}");
				$msg = explode("\t", $msg);
				$msg_data = explode(',', $msg[0]);

				$outbox = ($msg_data[0] & 2) == 2 ? true : false;
				if($outbox) $uname = $uinfo['name'].' '.$uinfo['lname'];
				else $uname = $peer_inf['name'].' '.$peer_inf['lname'];

				if(count($msg) == 3){
					$msg_title = $msg[1];
					$msg_txt = $msg[2];
				}else{
					$msg_title = '';
					$msg_txt = $msg[1];
				}

				$unread = ($msg_data[0] & 1) == 1 ? true : false;
				$unread_class = $unread ? ($outbox ? ' read' : ' read_my') : '';

				$sender = $outbox ? $uid : $peer;

				$msg_type = $last_uid == $sender ? ' short' : '';
				$last_uid = $sender;

				$msg_txt = preg_replace_callback("`(http(?:s)?://\w+[^\s\[\]\<]+)`is", "links", $msg_txt);


				$res .= '<div class="im_msg'.$unread_class.$msg_type.'" id="msg_'.$msg_id.'" data-uid="'.$sender.'">
	<div class="msg_cont">
		<div class="fl_l msg_ava"><img src="/img/camera_50.gif"/></div>
		<div class="cont">
			<div class="name"><a href="/">'.$uname.'</a></div>
			<div class="msg">'.$msg_txt.'</div>
		</div>
		<div class="clear"></div>
	</div>
</div>';
			}

		}else $res = '<div class="info_center">Здесь будет выводиться история переписки</div>';

		echo json_encode(array(
			'uinf' => array('name' => $peer_inf['name'], 'lname' => $peer_inf['lname']),
			'history' => $res,
			'online' => $peer_inf['last_update'] > $online_time ? 1 : 0,
			'last_up' => $peer_inf['last_update']
		));
		exit;
	break;

	case 'typing':
		ajax_only();
		$peer = intval($_POST['peer']);
		$im->set("history_action{$peer}#50", "{$uid},1");
		exit;
	break;

	case 'read':
		ajax_only();

		$peer = intval($_POST['peer']);

		$new = get_new_counts($uid, true);

		$new_peer = $new['peers'][$peer];

		if($new_peer > 0){
			foreach($new['ids'][$peer] as $id){
				$msg = $im->get("message{$uid}_{$id}#4");

				$msg = explode("\t", $msg);
				$msg_data = explode(',', $msg[0]);

				$im->decrement("flags{$uid}_{$id}", 1);
				$im->decrement("flags{$peer}_{$msg_data[3]}", 1);
			}
			$im->set("history_action{$peer}#51", "{$uid},1");

			$queue = new Memcache;
			$queue->connect('127.0.0.1', QUE_PORT);
			$queue->add("queue(notify{$uid})", json_encode(array('type' => 'msg_count', 'cnt' => $new_msg['all'])));

			$dialog_num = $new['unicue']-1;
		}else $dialog_num = $new['unicue'];

		echo json_encode(array('dialog_num' => $dialog_num));

		exit;
	break;

	case 'all_dialogs':

		$msg_list = $im->get("topmsglist{$uid}#-1,-500");
		$exp = explode(',', $msg_list);

		$res = $peers = array();
		$output = array();

		$counts = get_new_counts($uid);
		$site_title = $counts['unicue'] > 0 ? gram($counts['unicue'], 'новое сообщение', 'новых сообщения', 'новых сообщений') : 'FastChat';

		for($i = 1; $i < count($exp); $i += 2){
			$msg_id = $exp[$i];
			$msg = $im->get("message{$uid}_{$msg_id}");
			$msg = explode("\t", $msg);

			$msg_data = explode(',', $msg[0]);

			if(count($msg) == 3) $msg_txt = $msg[2];
			else $msg_txt = $msg[1];

			$new_cnt = $counts['peers'][$msg_data[2]];
			
			$res[] = array(
				'ts' => $msg_data[1],
				'peer' => $msg_data[2],
				'text' => $msg_txt,
				'new' => $new_cnt ? '+'.$new_cnt : ''
			);
			$peers[] = $msg_data[2];
		}

		$peers = implode(',', $peers);
		$sql_peers = mysql_query("SELECT uid, name, lname, last_update FROM `users` WHERE uid IN ({$peers})", 1);
		$profiles = array();
		foreach($sql_peers as $user) $profiles[$user['uid']] = array($user['name'], $user['lname'], $user['last_update']);

		foreach($res as $row){
			$peer_inf = $profiles[$row['peer']];
			$output[] = array(
				'peer' => $row['peer'],
				'name' => $peer_inf[0].' '.$peer_inf[1],
				'new' => $row['new'],
				'text' => $row['text'],
				'online' => $peer_inf[2] > $online_time ? 1 : 0
			);
		}

		echo json_encode($output);
		exit;
	break;

	default:

	$st_files = array('nano.js', 'nano.css', 'im.css', 'im.js', 'jquery.autosize.min.js');

	$msg_list = $im->get("topmsglist{$uid}#-1,-15");
	$exp = explode(',', $msg_list);

	if($exp[0] > 0){

		$res = $peers = array();

		$counts = get_new_counts($uid);
		$site_title = $counts['unicue'] > 0 ? gram($counts['unicue'], 'новое сообщение', 'новых сообщения', 'новых сообщений') : 'FastChat';

		for($i = 1; $i < count($exp); $i += 2){
			$msg_id = $exp[$i];
			$msg = $im->get("message{$uid}_{$msg_id}");
			$msg = explode("\t", $msg);

			$msg_data = explode(',', $msg[0]);

			if(count($msg) == 3) $msg_txt = $msg[2];
			else $msg_txt = $msg[1];

			$new_cnt = $counts['peers'][$msg_data[2]];
			
			$res[] = array(
				'ts' => $msg_data[1],
				'peer' => $msg_data[2],
				'text' => $msg_txt,
				'new' => $new_cnt ? '+'.$new_cnt : ''
			);
			$peers[] = $msg_data[2];
		}

		$peers = implode(',', $peers);
		$sql_peers = mysql_query("SELECT uid, name, lname, last_update FROM `users` WHERE uid IN ({$peers})", 1);
		$profiles = array();
		foreach($sql_peers as $user) $profiles[$user['uid']] = array($user['name'], $user['lname'], $user['last_update']);

		$output = '';
		foreach($res as $row){
			$peer_inf = $profiles[$row['peer']];
			$online = $peer_inf[2] > $online_time ? '<div class="online"></div>' : '';
			$output .= '<div class="chat_block" onCLick="im.open('.$row['peer'].')" id="im_'.$row['peer'].'">
				<img src="/img/camera_50.gif" class="fl_l">
				<div class="cont">
					<div class="name">'.$peer_inf[0].' '.$peer_inf[1].'</div>
					<div class="msg">'.$row['text'].'</div>
					<div class="new_cnt" id="msg_new'.$row['peer'].'">'.$row['new'].'</div>
					<div class="typing" id="typing_'.$row['peer'].'"><img src="/img/typing.gif"/></div>
				</div>'.$online.'
				<div class="clear"></div>
			</div>';
		}
	}else $output = '<div class="info_center">У вас нет ни одного диалога</div>';

	tpl_load('im/head');
	tpl_set(array(
		'{dialogs}' => $output
	));
	tpl_make('cont');

	$initJS = "$(document).ready(function(){
$('#contacts, #messages_nano').nanoScroller();

$(window).resize(function(){
	var wh = Math.max(window.innerHeight-45, 300);
	if(wh > 600) wh -= 30;
	$('.im_chats, .im_cont').css('height', wh+'px');
	$('#contacts, #contacts_res').css('height', (wh-54)+'px');

	var hcont = wh-(parseInt($('.im_send_form').get(0).scrollHeight)+44), mh = $('#message_all_cont').get(0).scrollHeight, margin = hcont > mh ? hcont-mh : 0;
	$('#messages_bl, #messages_nano_res').css('height', hcont+'px');
	$('#messages_bl').css('margin-top', margin+'px');
});
$(window).trigger('resize');
$('#im_text').autosize().bind('keyup', im.keyup).keypress(im.keypress);
im.queue_start();
im.song = document.getElementById('im_song');
im.song.volume = 1;
im.song.load();

im.load_dialogs();";

	$sel = intval($_GET['sel']);
	if($sel > 0) $initJS .= "\nim.open({$sel})";

	$initJS .= "\n});";
}