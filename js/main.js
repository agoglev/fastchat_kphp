/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/
var uagent = navigator.userAgent.toLowerCase();
var is_safari = (!(/chrome/i.test(uagent)) && /webkit|safari|khtml/i.test(uagent));

var cur = {};

cur.focused = true;
$(window).focus(function(){
	cur.focused = true;
	var msg = $('.im_msg.read_my');
	if(msg.length) im.readTimeout = setTimeout(im.read, 3000);
}).blur(function(){
	cur.focused = false;
});

function inputErr(id){
	var obj = $('#'+id);
	obj.css('background', '#ffefef').focus();
	setTimeout(function(){
		obj.css('background', '#fff');
	}, 700);
}

var Box = {
	Info: function(title, msg, time){
		var h = window.innerHeight/2-50;
		$('.box_info').remove();
		$('body').append('<div class="box_info" onmousedown="$(this).remove()"><div class="pos" style="margin-top:'+h+'px;">\
			<div class="title">'+title+'</div>\
			<div class="msg">'+msg+'</div>\
		</div></div>');
		$('.box_info .pos').fadeIn(200);
		if(!time) time = 3000;
		setTimeout("Box.CloseInf()", time);
	},
	CloseInf: function(){
		$('.box_info').fadeOut(200, function(){
			$(this).remove();
		});
	},
	Show: function(opts){
		$.post(opts.url, opts.query ? opts.query : {}, function(d){
			d = JSON.parse(d);
			if(d.err){
				if(d.err == 'nolog') location.href = '/';
				return;
			}
			Box.showBox(d);
		});
	},
	showBox: function(d){
		$('body').append(d.cont).css('overflow-y', 'hidden');
		var obj = $('#'+d.id+' .box_pos'), h = obj.height(), wh = window.innerHeight;

		if(wh-100 > h) var top = Math.max(50, (wh-h)/2-50);
		else var top = 50;

		obj.css('margin-top', top+'px');

		$('#'+d.id).bind('click', function(e){
			if($(e.target).filter('.box_pos').length == 0 && $(e.target).parents('.box_pos').length == 0) Box.Clos(d.id);
		});
	},
	Clos: function(id){
		if(id) $('#'+id).remove();
		else $('.box').remove();
		$('body').css('overflow-y', 'hidden');
	}
};

var stManager = {
	queue: [],
	wait_cnt: 0,
	waited_cnt: 0,
	waitID: false,
	callback: false,
	files: {},
	names: [],
	_add: function(){
		return this.add.apply(this, arguments);
	},
	add: function(files, callback, onerror){
		if(!$.isArray(files)) files = [files];
		var list = [], names = [], l = files.length, _s = stManager, path_css = '/css/';

		for(var i = 0; i < l; i++){
			var file = files[i], path = (file.indexOf('http://') == -1 && file.indexOf('https://') == -1) ? '/js/' : '';

			if(file.indexOf('.js') != -1){
				if(document.querySelectorAll('script[src="'+path+file+'"]').length) continue;

				var el = document.createElement('script');
				el.type = 'text/javascript';
				el.src = path+file;
			}else if(file.indexOf('.css') != -1){
				if(document.querySelectorAll('link[href="'+path_css+file+'"]').length) continue;

				var el = document.createElement('link');
				el.rel = 'stylesheet';
				el.type = 'text/css';
				el.href = path_css+file;

			}
			$(el).bind('load', function(){
				this.loaded = 1;
				_s.waited_cnt++;
			}).bind('error', function(){
				topError({text: '<b>Error</b>: failed load static file <b>'+file+'</b><br>Clear your browser cache and refresh this page', red: 1}, 5000);
				clearInterval(_s.waitID);
				_s.waitID = false;
				if(_s.last_list){
					try{
						for(var i = 0; i < _s.last_list.length; i++) $(_s.last_list[i]).unbind().remove();
					}catch(e){ }
				}
				if(onerror) onerror();
			});
			list.push(el);
		}
		if(!list.length){
			if(callback) callback();
			return;
		}
		if(callback){
			_s.queue.push({list: list, cb: callback});
			_s.start_wait();
		}else _s.put_files(list);
	},
	put_files: function(list, onload){
		var body = document.getElementsByTagName('head')[0], _s = stManager;
		for(var i = 0; i < list.length; i++){
			if(is_safari && list[i].tagName == 'LINK'){
				var img = document.createElement('img');
				img.onerror = function(){
					_s.waited_cnt++;
    			}
    			img.src = list[i].href;
			}
			body.appendChild(list[i]);
		}
	},
	last_list: [],
	start_wait: function(){
		var _s = stManager;
		if(_s.waitID || !_s.queue.length) return;
		var obj = _s.queue[0];
		_s.wait_cnt = obj.list.length;
		_s.waited_cnt = 0;
		_s.callback = obj.cb;

		_s.queue = _s.queue.slice(1);

		for(var i = 0; i < _s.wait_cnt; i++){
			if(obj.list[i].loaded) _s.waited_cnt++;
		}

		_s.waitID = setInterval(_s.wait, 100);
		_s.last_list = obj.list;
		_s.put_files(obj.list, 1);
	},
	wait: function(){
		var _s = stManager;
		if(_s.wait_cnt == _s.waited_cnt){
			clearInterval(_s.waitID);
			_s.waitID = false;
			_s.callback();
			_s.start_wait();
		}
	}
};

var nav = {
	go: function(lnk){
		history.pushState({link: lnk},null, lnk);

		$('.box').remove();
		$(window).unbind().css('overflow-y', 'auto').scrollTop(0);

		if(window.im) im.destroy();

		$.post(lnk, {nav: 1}, function(d){
			d = JSON.parse(d);
			if(d.st_files){
				stManager.add(d.st_files, function(){
					$('#page').html(d.cont);
					if(d.init_js) eval(d.init_js);
				});
			}else{
				$('#page').html(d.cont);
				if(d.init_js) eval(d.init_js);
			}
		});
	}
}

function peopleShow(){
	Box.Show({
		url: '/people',
		query: {act: 'list'}
	});
}

function debugLog(msg){
	try{
		var args = Array.prototype.slice.call(arguments);
		args.unshift('['+((new Date().getTime())/1000)+'] ');
		console.debug.apply(console, args);
	}catch(e){
		console.debug('['+((new Date().getTime())/1000)+'] '+msg);
	}
}

function gram(number, titles){
	cases = [2, 0, 1, 1, 1, 2];  
	return number+' '+(titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5]]);
}

function ajax(h, query, opts){
	$.post(h, query).done(function(d){
		try{ var json = JSON.parse(d); }catch(e){ var json = false; }
		if(json){
			//system
		}
		if(opts.onDone) opts.onDone(json);
	}).fail(function(xhr){
		switch(xhr.status){
			case 0: topError({text: '<b>Ошибка</b>: Проверьте соединение с интернетом', red: true, time: 5000}); break;
			case 500: topError({text: '<b>Ошибка</b>: Ошибка сервера, попробуйте позже', red: true, time: 5000}); break;
		}
		if(opts.onFail) opts.onFail();
	});
}

function topError(opts){
	if(!opts) opts = {};
	clearTimeout(cur.topErrorTimeout);
	var bg = opts.red ? '#FFB4A3' : '#D6E5F7';
	if(!opts.text) return $('#top_error').hide();
	$('#top_error').html(opts.text).css('background', bg).show();
	if(!opts.time) opts.time = 3000;
	cur.topErrorTimeout = setTimeout(topError, opts.time);
}

var page = {
	send_friend_req: function(id, el){
		$.post('/friends', {act: 'send_request', id: id});
		$(el).attr('onClick', '').html('<span style="color:#777;">Заявка отправленна</span>').css('background', '#fff');
	}
}

function indexOf(arr, value, from) {
  	for (var i = from || 0, l = (arr || []).length; i < l; i++) {
    	if (arr[i] == value) return i;
  	}
  	return -1;
}
function clean(str) {
  	return str ? str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;') : '';
}