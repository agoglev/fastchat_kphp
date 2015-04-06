/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

var Notify = {
	debug: true,
	is_server: false,
	init: function(){
		var _n = Notify;
		window.addEventListener('storage', _n.onStorage, false);
		$('body').append('<iframe id="fc_storage" src="/storage.php" class="fixed" style="top:-30px;height:10px;width:10px;left:0;"></iframe>');
		_n.storage = document.getElementById('fc_storage');
		_n.storage.onload = _n.storageOnload;
	},
	lsSet: function(k, v){
		Notify.storage.contentWindow.setLS({k: k, v: v});
	},
	onStorage: function(e){
		e = e || window.event;
		var k = e.key, v = e.newValue, _n = Notify;
		if(!v) return;

		switch(k){
			case 'q_check':
				if(!_n.is_server) return;
				_n.lsSet('q_server_ok', 1);
			break;

			case 'q_server_ok':
				if(_n.is_server) return;
				_n.debug & debugLog('server checked');
				clearTimeout(_n.timer);
				_n.timer = false;
				_n.start_check();
			break;
		}
	},
	storageOnload: function(){
		var _n = Notify;
		_n.storage_inited = true;
		if(window.im){
			im.storage = _n.storage;
			im.check_server();
		}
		_n.check_server();
	},
	check_server: function(){
		var _n = Notify;
		_n.lsSet('q_check', 1);
		_n.timer = setTimeout(_n.server_not, 3000);
	},
	server_not: function(){
		var _n = Notify;
		console.log('queue create');
		_n.is_server = true;
		$('body').append('<iframe src="/q_frame.php" class="fixed" style="top:-60px;height:10px;width:10px;left:0;"></iframe>');
	},
	start_check: function(){
		var _n = Notify;
		_n.debug & debugLog('start checking');
		_n.timer = setTimeout(_n.check_server, 0x7530);
	},
	onEvent: function(d){
		d = JSON.parse(d);
		switch(d.type){
			case 'req_count':
				var cnt = d.cnt > 0 ? '+'+d.cnt : '';
				$('#req_cnt_head').html(cnt);
			break;
			case 'msg_count':
				var cnt = d.cnt > 0 ? '+'+d.cnt : '';
				$('#msg_count_all').html(cnt);
			break;
		}
	}
};
window.getFrameData = function(d){
	if(!$.isArray(d)) d = [d];

	for(var i = 0; i < d.length; i++) Notify.onEvent(d[i]);
}