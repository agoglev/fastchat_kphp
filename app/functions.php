<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

function textFilter($source, $opts = array('substr' => 25000)){
	if(!$source) return '';

	$source = stripslashes($source);

	$find = array('/data:/i', '/about:/i', '/vbscript:/i', '/onclick/i', '/onload/i', '/onunload/i', '/onabort/i', '/onerror/i', '/onblur/i', '/onchange/i', '/onfocus/i', '/onreset/i', '/onsubmit/i', '/ondblclick/i', '/onkeydown/i', '/onkeypress/i', '/onkeyup/i', '/onmousedown/i', '/onmouseup/i', '/onmouseover/i', '/onmouseout/i', '/onselect/i', '/javascript/i');		
	$replace = array("d&#097;ta:", "&#097;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#097;vascript");
	$source = preg_replace("#<iframe#i", "&lt;iframe", $source);
	$source = preg_replace("#<script#i", "&lt;script", $source);

	$source = myBr(htmlspecialchars(substr(trim($source), 0, $opts['substr'])));
	$source = str_replace("{", "&#123;", $source);
	$source = str_replace("`", "&#96;", $source);
	$source = preg_replace($find, $replace, $source);

	return mysql_escape_string($source);
}

function myBr($source){
	$find[] = "'\r'";
	$replace[] = "<br />";
	$find[] = "'\n'";
	$replace[] = "<br />";
	$source = preg_replace($find, $replace, $source);
	return $source;
}

function extend_auth($uid, $hash){
	global $pmc;
	$extend = 3600*24;
	setcookie('uid', $uid, time()+$extend, '/', 'fastchat.su', NULL, TRUE);
	setcookie('hash', $hash, time()+$extend, '/', 'fastchat.su', NULL, TRUE);
	$pmc->set('session'.$uid.$hash, 'logged', $extend);
}

function ajax_only(){
	if($_SERVER['REQUEST_METHOD'] != 'POST') {
		header('Location: /error');
		exit;
	}
}

function get_user_data($uid){
	global $pmc;
	$uinfo = $pmc->get('uinfo'.$uid);
	if(!$uinfo['uid']){
		$uinfo = mysql_query("SELECT uid, email, name, lname, photo, friends_request, last_update FROM `users` WHERE uid = '{$uid}'");
		$pmc->set('uinfo'.$uid, $uinfo, 3600*24);
	}
	return $uinfo;
}

function extractSubnetwork($ip){
	return substr($ip, 0, strrpos($ip, '.')).'.';
}
function char_to_hex($c) {
	$c = ord($c);
	if ($c <= 57)  return ($c - 48);
	else return ($c - 97 + 10);
}

function hex_to_char($c) {
	if ($c < 10) $c = chr($c + 48);
	else $c = chr($c - 10 + 97);
	return $c;
}

function xor_str($str1, $str2, $digits = 8) {
	for ($i = 0,$j = 0; $i < $digits; $i++, $j++) $str1[$i] = hex_to_char(char_to_hex($str1[$i]) ^ char_to_hex($str2[$j]));
	return $str1;
}

function declOfNum($number, $titles){
    $cases = array(2, 0, 1, 1, 1, 2);
    return $titles[ ($number % 100 > 4 && $number % 100 < 20)? 2 : $cases[min($number % 10, 5)] ];
}

function gram($num, $a, $b, $c, $t = false){
	if($t) return declOfNum($num, array(sprintf($a,  $num), sprintf($b,  $num), sprintf($c, $num)));
	else return declOfNum($num, array(sprintf("%d {$a}",  $num), sprintf("%d {$b}",  $num), sprintf("%d {$c}", $num)));
}

function links($arg){
	$lnk = $arg[0];
	$lnk_name = $lnk;
	if(strlen($lnk_name) > 53) $lnk_name = substr($lnk, 0, 53).'..';
	return '<a href="'.$lnk.'" class="link" target="_blank">'.$lnk_name.'</a>';
}