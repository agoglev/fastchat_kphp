<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

//$start = microtime(true);
header('Content-type: text/html; charset=utf-8');

$logged = false;

include 'app/init.php';

//if(substr($_SERVER['SCRIPT_NAME'], 0, 3) == '/id') $do = '/id';
//else 
$do = $_SERVER['SCRIPT_NAME'];

$st_files = false;
$initJS = '';

switch($do){

	case '/reg':
		include 'app/modules/reg.php';
	break;

	case '/login':
		include 'app/modules/login.php';
	break;

	case '/id':
		include 'app/modules/profile.php';
	break;

	case '/upload':
		include 'app/modules/upload.php';
	break;

	case '/people':
		include 'app/modules/people.php';
	break;

	case '/storage.php':
		tpl_load('storage');
		tpl_make('res');
		echo $tpl_res['res'];
		exit;
	break;

	case '/transport.php':
		include 'app/modules/transport.php';
	break;

	case '/q_frame.php';
		include 'app/modules/q_frame.php';
	break;

	case '/friends':
		include 'app/modules/friends.php';
	break;

	case '/away.php':
		$url = $_GET['url'];
		header('Location: '.$url);
		exit;
	break;

	case '/dev':
		$st_files = array('al/dev.css');
		tpl_load('dev/spec');
		tpl_make('cont');
	break;

	case '/about':
		tpl_load('about');
		tpl_make('cont');
	break;

	case '/test':
		$queue = new Memcache;
		$queue->connect('127.0.0.1', QUE_PORT);
		$queue->add("queue(notify1)", json_encode(array('type' => isset($_GET['msg']) ? 'msg_count' : 'req_count', 'cnt' => $_GET['n'])));
		exit;
	break;

	default:

	if($logged) include 'app/modules/im.php';
	else{
		tpl_load('main_page');
	 	tpl_make('cont');
	}
}

if(isset($_POST['nav'])){
	$res = array('cont' => $tpl_res['cont'], 'st_files' => $st_files ? $st_files : 0, 'init_js' => $initJS);
	echo json_encode($res);
	exit;
}

tpl_load('head');

if($logged){
	tpl_set(array(
		'{my_id}' => $uid,
		'{name}' => $uinfo['name'].' '.$uinfo['lname'],
		'{head_req}' => $uinfo['friends_request'] > 0 ? '+'.$uinfo['friends_request'] : ''
	));
}

if($st_files){
	$st_res = '';
	foreach($st_files as $file){
		if(strpos($file, '.js') !== false) $st_res .= '<script type="text/javascript" src="/js/'.$file.'"></script>';
		else $st_res .= '<link rel="stylesheet" type="text/css" href="/css/'.$file.'"/>';
	}
	tpl_set('{st_files}', $st_res);
}else tpl_set('{st_files}', '');


tpl_set(array(
	'{cont}' => $tpl_res['cont'],
	'{title}' => $site_title ? $site_title : 'FastChat',
	'{init_js}' => $initJS
));
if($logged) tpl_set(array('[logged]' => '', '[/logged]' => ''));
else tpl_block('logged');
tpl_make('main');

echo $tpl_res['main'];

//$time = microtime(true) - $start;
//printf('<br>Скрипт выполнялся %.4F сек.', $time);
