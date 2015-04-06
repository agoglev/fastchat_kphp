/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/

var im = {
	uid: 0,
	uinf: {},
	is_server: false,
	timer: false,
	debug: (fc.id == 1),
	offline: false,
	inited: false,
	open: function(id){
		$('.im_no_sel, #im_offline, #im_head_online').hide();

		var preloader = $('#im_preloader');
		preloader.show();

		$('.chat_block.active').removeClass('active');
		$('#im_'+id).addClass('active');

		this.uid = id;

		Box.Clos();

		$('#im_typing, #typing_'+id).css('opacity', 0);
		$('#msg_new'+id+', #im_typing').show();

		im.cancel_sarch();

		$.post('/', {peer: id, act: 'history'}, function(d){
			preloader.hide();

			d = JSON.parse(d);
			im.uinf = d.uinf;
			$('#im_typing span').html(im.uinf.name);
			$('.im_user_panel .name').html(im.uinf.name+' '+im.uinf.lname);
			$('#messages_res').html(d.history);
			$('#chat_view_bl').show();

			$('#messages_res .im_msg.read_my:first').before('<div class="new_msg_info"><div class="str">Новые сообщения</div></div>');

			$(window).trigger('resize');
			$('#messages_nano').nanoScroller({scrollBottom: 0});

			var new_msg = $('.new_msg_info');
			if(new_msg.length != 0) {
				$('#messages_nano').nanoScroller({scrollTo: new_msg});
				im.read();
			}

			if(d.online) {
				$('#im_head_online').show();
				im.offline = false;
			}else{
				$('#im_typing').hide();
				if(d.last_up){
					var str = im.get_last_time(d.last_up);
					$('#im_offline').html(im.uinf.name+' последний раз был '+str).show();
				}else $('#im_offline').html(im.uinf.name+' сейчас не в сети').show();
				im.offline = true;
				$('#im_'+id+' .online').remove();
			}
			setTimeout(function(){
				$(window).trigger('resize');
			}, 200);
		});
	},
	send: function(){
		var txt = String($('#im_text').val()).trim();

		if(!txt) return inputErr('im_text');

		$('#im_text').val('').focus();

		$('#messages_res .info_center').remove();

		var ts = new Date().getTime();
		im.put_msg({id: ts, peer: im.uid, date: ts, title: '', msg: txt, flags: 3});

		$('#messages_nano').nanoScroller({scrollBottom: 0});

		$.post('/', {act: 'send', peer: im.uid, msg: txt}, function(d){
			d = JSON.parse(d);
			if(d.spam){
				$('#msg_'+ts+' .cont').html('<div class=""><b>Сообщение не отправленно</b><br>Ваше сообщение расценено как спам</div>');
				return;
			}
			$('#msg_'+ts).attr('id', 'msg_'+d.id);
		});
	},
	put_msg: function(d){
		var msg = d.msg.replace(/\n/g, '<br>'), uinf = im.uinf, outbox = (d.flags & 2) == 2;

		if(msg){
			msg = msg.replace(/(^|[^A-Za-z0-9А-Яа-яёЁ\-\_])(https?:\/\/)?((?:[A-Za-z\$0-9А-Яа-яёЁ](?:[A-Za-z\$0-9\-\_А-Яа-яёЁ]*[A-Za-z\$0-9А-Яа-яёЁ])?\.){1,5}[A-Za-z\$рфукРФУК\-\d]{2,22}(?::\d{2,5})?)((?:\/(?:(?:\&amp;|\&#33;|,[_%]|[A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.~=;:]+|\[[A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.,~=;:]*\]|\([A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.,~=;:]*\))*(?:,[_%]|[A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.~=;:]*[A-Za-z0-9А-Яа-яёЁ\_#%?+\/\$~=]|\[[A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.,~=;:]*\]|\([A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.,~=;:]*\)))?)?)/ig, function () {
				var matches = Array.prototype.slice.apply(arguments), prefix = matches[1] || '', protocol = matches[2] || 'http://', domain = matches[3] || '', url = domain + (matches[4] || ''), full = (matches[2] || '') + matches[3] + matches[4];
				if (domain.indexOf('.') == -1 || domain.indexOf('..') != -1) return matches[0];
				var topDomain = domain.split('.').pop();
				if (topDomain.length > 6 || indexOf('info,name,aero,arpa,coop,museum,mobi,travel,xxx,asia,biz,com,net,org,gov,mil,edu,int,tel,ac,ad,ae,af,ag,ai,al,am,an,ao,aq,ar,as,at,au,aw,ax,az,ba,bb,bd,be,bf,bg,bh,bi,bj,bm,bn,bo,br,bs,bt,bv,bw,by,bz,ca,cc,cd,cf,cg,ch,ci,ck,cl,cm,cn,co,cr,cu,cv,cx,cy,cz,de,dj,dk,dm,do,dz,ec,ee,eg,eh,er,es,et,eu,fi,fj,fk,fm,fo,fr,ga,gd,ge,gf,gg,gh,gi,gl,gm,gn,gp,gq,gr,gs,gt,gu,gw,gy,hk,hm,hn,hr,ht,hu,id,ie,il,im,in,io,iq,ir,is,it,je,jm,jo,jp,ke,kg,kh,ki,km,kn,kp,kr,kw,ky,kz,la,lb,lc,li,lk,lr,ls,lt,lu,lv,ly,ma,mc,md,me,mg,mh,mk,ml,mm,mn,mo,mp,mq,mr,ms,mt,mu,mv,mw,mx,my,mz,na,nc,ne,nf,ng,ni,nl,no,np,nr,nu,nz,om,pa,pe,pf,pg,ph,pk,pl,pm,pn,pr,ps,pt,pw,py,qa,re,ro,ru,rs,rw,sa,sb,sc,sd,se,sg,sh,si,sj,sk,sl,sm,sn,so,sr,ss,st,su,sv,sx,sy,sz,tc,td,tf,tg,th,tj,tk,tl,tm,tn,to,tp,tr,tt,tv,tw,tz,ua,ug,uk,um,us,uy,uz,va,vc,ve,vg,vi,vn,vu,wf,ws,ye,yt,yu,za,zm,zw,рф,укр,cat,pro,local'.split(','), topDomain) == -1) return matches[0];
				if (matches[0].indexOf('@') != -1) return matches[0];
				try { full = decodeURIComponent(full); } catch (e){}
				if (full.length > 55) full = full.substr(0, 53) + '..';
				full = clean(full).replace(/&amp;/g, '&');
				return prefix + '<a href="/away.php?url=' + encodeURIComponent(protocol + url) + '" target="_blank" class="link">' + full + '</a>';
			});
		}

		var name = outbox ? fc.name : uinf.name+' '+uinf.lname, read = outbox ? ' read' : ' read_my', sender = outbox ? fc.id : im.uid;

		var type_msg = $('.im_msg:last').attr('data-uid') == sender ? ' short' : '';

		$('#messages_res').append('<div class="im_msg'+read+type_msg+'" id="msg_'+d.id+'" data-uid="'+sender+'"><div class="msg_cont">\
			<div class="fl_l msg_ava"><img src="/img/camera_50.gif"/></div>\
			<div class="cont">\
				<div class="name"><a href="/">'+name+'</a></div>\
				<div class="msg">'+msg+'</div>\
			</div>\
			<div class="clear"></div>\
		</div></div>');
		$(window).trigger('resize');
	},
	keyup: function(){
		$(window).trigger('resize');
		im.typing();
	},
	keypress: function(e){
		e = e || window.event;
		if (e.keyCode == 10 && (e.ctrlKey || e.metaKey && /mac/i.test(navigator.userAgent.toLowerCase()))) {
			$('#im_text').val($('#im_text').val()+'\n');
			return;
		}
		if(e.keyCode == 13) {
			e.preventDefault();
			im.send();
		}
	},
	last_typing: 0,
	typing: function(){
		var ts = new Date().getTime();
		if(ts-5000 > im.last_typing){
			im.last_typing = ts;
			$.post('/im', {act: 'typing', peer: im.uid}, function(){
				im.last_typing = new Date().getTime();
			});
		}
	},
	read: function(){
		$('#messages_res .im_msg.read_my').removeClass('read_my');
		$('#msg_new'+im.uid).html('');
		$.post('/im', {act: 'read', peer: im.uid}, function(d){
			d = JSON.parse(d);
			document.title = d.dialog_num > 0 ? gram(d.dialog_num, ['новое сообщение', 'новых сообщения', 'новых сообщений']) : 'FastChat';
		});
	},
	dialogs: false,
	loaded_dialogs: false,
	load_dialogs: function(){
		$.post('/', {act: 'all_dialogs'}, function(d){
			im.loaded_dialogs = true;
			if(d){
				d = JSON.parse(d);
				im.dialogs = d;
			}else im.dialogs = [];
		});
	},
	search: function(str){
		if(!im.loaded_dialogs){
			alert('Идет загрузка диалогов, попробуйте позже..');
			return;
		}
		var res = '', cnt = 0;

		str = String(str).trim();
		if(str) str = str.toLowerCase();
		else{
			$('#search_result_dialogs').html('');
			$('#all_dialogs').show();
			$(window).trigger('resize');
			return;
		}

		for(var i = 0; i < im.dialogs.length; i++){
			if(!str || im.dialogs[i].name.toLowerCase().indexOf(str) != -1){
				var d = im.dialogs[i];
				d.ac_cl = d.peer == im.uid ? ' active' : '';
				d.new = 0;
				res += im.compile_dialog(d);
				cnt++;
			}
		}

		if(!cnt) res = '<div class="info_center">Ни чего не найденно</div>';
		else{
			res = '<div class="search_res" onClick="im.cancel_sarch();">\
				<div class="fl_l">Результаты поиска ('+cnt+')</div>\
				<div class="fl_r">отмена</div>\
				<div class="clear"></div>\
			</div>'+res;
		}
		
		$('#all_dialogs').hide();
		$('#search_result_dialogs').html(res);
		$(window).trigger('resize');
	},
	cancel_sarch: function(){
		$('#dialogs_search_q').val('');
		$('#search_result_dialogs').html('');
		$('#all_dialogs').show();
		$(window).trigger('resize');
	},
	compile_dialog: function(d){
		var online = d.online ? '<div class="online"></div>' : '';
		return '<div class="chat_block'+d.ac_cl+'" onclick="im.open('+d.peer+')" id="im_'+d.peer+'">\
			<img src="/img/camera_50.gif" class="fl_l">\
			<div class="cont">\
				<div class="name">'+d.name+'</div>\
				<div class="msg">'+d.text+'</div>\
				<div class="new_cnt" id="msg_new'+d.peer+'">'+(d.new ? '+'+d.new : '')+'</div>\
				<div class="typing" id="typing_'+d.peer+'"><img src="/img/typing.gif"></div>\
			</div>'+online+'\
			<div class="clear"></div>\
		</div>';
	},
	get_last_time: function(t){
		t *= 1000;
		var ct = (new Date().getTime())-t, s = Math.round(ct/1000), m = Math.round(ct/60000), h = Math.round(ct/3600000), d = Math.round(ct/86400000), w = Math.round(ct/604800000), mh = Math.round(ct/2419200000), y = Math.round(ct/29030400000);
		if(s <= 1) return 'только что';
		else if(s <= 60) return gram(s, ['секунду', 'секунды', 'секунд'])+' назад';
		else if(m <= 60) return (m == 1) ? 'минуту назад' : gram(m, ['минуту', 'минуты', 'минут'])+' назад';
		else if(h <= 24) return (h == 1) ? 'час назад' : gram(h, ['час', 'часа', 'часов'])+' назад';
		else if(d <= 6) return (d == 1) ? 'день назад' : gram(d, ['день', 'дня', 'дней'])+' назад';
		else if(w <= 7) return (w == 1) ? 'неделю назад' : gram(w, ['неделю', 'недели', 'недель'])+' назад';
		else if(mh <= 12) return (mh == 1) ? 'месяц назад' : gram(mh, ['месяц', 'месяца', 'месяцев'])+' назад';
		else if(y <= 12) return (y == 1) ? 'год назад' : gram(y, ['год', 'года', 'лет'])+' назад';
	},
	//Storage
	destroy: function(){
		//$(window).unbind('storage', im.lsOnStorage);
		clearTimeout(im.timer);	
	},
	queue_start: function(){
		//$(window).bind('storage', im.lsOnStorage);
		if(Notify.storage_inited){
			im.storage = Notify.storage;
			im.check_server();
		}
	},
	lsSet: function(k, v){
		im.storage.contentWindow.setLS({k: k, v: v});
	},
	lsOnStorage: function(e){
		e = e || window.event;
		var k = e.key, v = e.newValue;
		if(!v) return;

		im.debug && debugLog('LS', k, v);

		switch(k){
			case 'check':
				if(!im.is_server) return;
				im.lsSet('server_ok', 1);
			break;

			case 'server_ok':
				if(im.is_server) return;
				im.debug & debugLog('server checked');
				if(im.timer){
					clearTimeout(im.timer);
					im.timer = false;
				}
				im.start_check();
			break;

			case 'data':
				if(im.is_server) return;
				var ev = JSON.parse(v);
				for(var i = 0; i < ev.length; i++) im.proccessEvent(ev[i]);
			break;
		}
	},
	check_server: function(){
		return im.initTransport();
		im.lsSet('check', 1);
		im.timer = setTimeout(im.server_not, 0x1F4);
	},
	server_not: function(){
		im.debug & debugLog('create connection');
		im.is_server = true;
		im.timer = false;
		im.lsSet('server_ok', 1);
		im.initTransport();
	},
	start_check: function(){
		im.debug & debugLog('start checking');
		im.timer = setTimeout(im.check_server, 0x7530);
	},
	//Transport
	initTransport: function(){
		$('#fc_queue').remove();
		$('body').append('<iframe id="fc_queue" src="/transport.php" class="fixed" style="top:-30px;height:10px;width:10px;left:0;"></iframe>');
	},
	readTimeout: false,
	proccessEvent: function(ev){
		var action = ev[0], local_id = ev[1], flags = ev[2];

		switch(action){
			case 4:
				var peer = ev[3], date = ev[4], title = ev[5], msg = ev[6], added = false;
				if(peer == im.uid && $('#msg_'+local_id).length == 0){
					im.put_msg({id: local_id, peer: peer, date: date, title: title, msg: msg, flags: flags});
					$('#messages_nano').nanoScroller({scrollBottom: 0});
					$('#im_typing').css('opacity', 0);
					added = true;
				}
				var outbox = (flags & 2) != 2;
				if(im.is_server && (cur.focused && outbox || added && outbox)){
					im.song.currentTime = 0;
					im.song.play();
				}
				if(cur.focused && added){
					clearTimeout(im.readTimeout);
					im.readTimeout = setTimeout(im.read, 1000);
				}
				$('#typing_'+peer).css('opacity', 0);
				$('#msg_new'+peer).show();
			break;
			case 50:
				if(im.uid == local_id){
					if(im.offline){
						$('#im_typing, #im_head_online').show();
						$('#im_offline').hide();
					}
					$('#im_typing, #typing_'+local_id).css('opacity', 1);
					setTimeout("$('#im_typing, #typing_"+local_id+"').css('opacity', 0);", 5000);
				}else{
					$('#msg_new'+local_id).hide();
					$('#typing_'+local_id).css('opacity', 1);
					setTimeout(function(){
						$('#typing_'+local_id).css('opacity', 0);
						$('#msg_new'+local_id).show();
					}, 5000);
				}
				if(im.offline){
					im.offline = false;
					if($('#im_'+local_id+' .online').length == 0) $('#im_'+local_id+' .cont').after('<div class="online"></div>');
				}
			break;
			case 100:
				$('#all_dialogs .info_center').remove();
				$('#im_'+local_id.peer).remove();
				var ac_cl = im.uid == local_id.peer ? ' active' : '';
				$('#all_dialogs').prepend(im.compile_dialog({peer: local_id.peer, new: local_id.cnt, name: local_id.name, text: local_id.msg, ac_cl: ac_cl, online: local_id.online}));
				if(local_id.cnt != 'undefined' && local_id.cnt != undefined) document.title = gram(local_id.cnt, ['новое сообщение', 'новых сообщения', 'новых сообщений']);
			break;
			case 51:
				if(im.uid == local_id) $('.im_msg.read').removeClass('read');
			break;
		}
	},

};
window.newQueueEv = function(ev){
	im.debug & console.log('new event', ev);
	for(var i = 0; i < ev.length; i++) im.proccessEvent(ev[i]);
}