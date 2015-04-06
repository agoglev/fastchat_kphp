<?
/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/
	
define('PMC_PORT', 11209);//pmemcached - кэш
define('IM_PORT', 11244);//text-engine - сообщения (txt)
define('FR1_PORT', 11210);//friends-engine - друзья
define('PH_PORT', 11233);//photo-engine - фотографии
define('ST_PORT', 11033);//storage-engine  HTTP-port: 8081 - хранение файлов
define('HIN_PORT', 11245);//hints-engine  - рейтинг объектов
define('BAY_PORT', 11246);//bayes-engine - антиспам
define('QUE_PORT', 11247);//queue-engine HTTP-port: 3311 - оповещания