<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

$inited_mysql = false;
$mysql_iid = 0;
function mysql_query($query, $multi = false){
	global $inited_mysql, $mysql_iid;

	if(!$inited_mysql){
		new_db_decl();
		dbQuery("SET NAMES 'utf8'");
		$inited_mysql = true;
	}

	$db = dbQuery($query);

	$mysql_iid = dbInsertedId();

	if($multi){
		$data = array();
		while ($row = dbFetchRow($db)) $data[] = $row;
		return $data;
	}else return dbFetchRow($db);
}