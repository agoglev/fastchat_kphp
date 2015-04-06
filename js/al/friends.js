/*
	Author: Andrey Goglev
	VK: https://vk.com/ru151
*/
var friends = {
	accept: function(id){
		$('#req'+id+' .buttons').html('<span style="color:#666;">Заявка принята</span>');
		$.post('/friends', {act: 'accept', id: id});
		friends.remove_req(id, 1);
	},
	reject: function(id){
		$('#req'+id+' .buttons').html('<span style="color:#666;">Заявка отклонена</span>');
		$.post('/friends', {act: 'reject', id: id});
		friends.remove_req(id);
	},
	remove_req: function(id, accept){
		var _f = friends;
		_f.req_list.cnt--;
		if(accept) _f.friends_list.push(_f.req_list.res['f'+id]);
		delete _f.req_list.res['f'+id];

		var cnt = _f.req_list.cnt;
		$('#req_cnt').html(cnt ? '+'+cnt : '');
	},
	loadFriends: function(){
		var _f = friends;
		_f.loaded_friend = false;
		_f.start_search = false;
		_f.mode = 'all';
		ajax('/friends', {act: 'load_all', uid: friends.uid}, {
			onDone: function(d){
				_f.friends_list = d;
				_f.sorted_list = d;
				_f.loaded_friend = true;
				_f.offset = 0;
				if(_f.start_search) _f.search();
			},
			onFail: function(){
				$('#friends_res').html('<div class="info_center" style="padding: 100px 20px;">Поиск по друзьям невозможен, проверьте соединение с интернетом и перезагрузите страницу</div>');
			}
		});
	},
	delete_box: function(id){
		ajax('/friends', {act: 'delete_box', id: id}, {
			onDone: function(d){
				if(d.err) return topError({text: 'Пользователя не существует', red: 1});
				Box.showBox({id: 'box_delfr', cont: d.res});
			}
		});
	},
	del: function(id){
		var pos = 0, _f = friends;

		for(var i = 0; i < _f.friends_list.length; i++) if(_f.friends_list[i].id == id) pos = i+1;

		var f = _f.friends_list.slice(0, pos-1), l = _f.friends_list.slice(pos+1);
		if(l.length > 0) for(var i = 0; i < l.length; i++) f.push(l[i]);
		
		_f.friends_list = f;
		_f.search();

		$.post('/friends', {act: 'delete', id: id});

		Box.Clos('box_delfr');
	},
	change_mode: function(name, el){
		var _f = friends;

		$('.tabs li.active').removeClass('active');
		$(el).addClass('active');

		_f.mode = name;
		_f.search();
	},
	search: function(){
		var _f = friends, val_orig = $('#friends_search_query').val(), val = String(val_orig).trim().toLowerCase();

		$('#request_tab').hide();
		$('#friends_tab').show();

		if(!_f.loaded_friend){
			_f.start_search = true;
			$('#friends_res').html('<div style="padding:100px 0;text-align:center"><img src="/img/big.gif"></div>');
			return;
		}

		var sorted = [], cnt = 0;
		for(var i = 0; i < _f.friends_list.length; i++){
			var item = _f.friends_list[i];
			if(item.name.toLowerCase().indexOf(val) != -1){
				switch(_f.mode){
					case 'online': if(item.online != 1) continue; break;
				}
				cnt++;
				sorted.push(item);
			}
		}

		_f.sorted_list = sorted;
		_f.offset = 0;

		if(val){
			if(cnt) $('.friends_cnt').html(gram(cnt, ['Найден', 'Найдено', 'Найдено']).replace(cnt, '')+' '+gram(cnt, ['человек', 'человека', 'человек']));
			else $('.friends_cnt').html('Ничего не найдено');
		}else {
			switch(_f.mode){
				case 'all': var str = (_f.uid == fc.id ? 'У вас' : 'У {name}')+' '+(cnt ? gram(cnt, ['друг', 'друга', 'друзей']) : 'нет друзей'); break;
				case 'online': var str = 'Нет друзей онлайн'; break;
			}
			$('.friends_cnt').html(str);
		}

		if(cnt) _f.put_friends();
		else {
			if(val) $('#friends_res').html('<div class="info_center" style="padding: 100px 20px;">По запросу <b>'+val_orig+'</b> ничего не найдено</div>');
			else {
				switch(_f.mode){
					case 'all': str = 'У вас нет друзей'; break;
					case 'online': str = 'Нет ни одного друга онлайн'; break;
				}
				$('#friends_res').html('<div class="info_center" style="padding: 100px 20px;">'+str+'</div>');
			}
			$('#friend_load_btn').hide();
		}
	},
	compile_friend: function(d){
		var res = this.tpl;
		res = res.replace('{name}', d.name).replace('{online}', d.online ? 'online' : '').replace(/\{id\}/g, d.id);
		return res;
	},
	put_friends: function(more){
		var res = '', _f = friends, len = _f.sorted_list.length;
		for(var i = _f.offset; i < len; i++) res += _f.compile_friend(_f.sorted_list[i]);
		_f.offset = Math.min(_f.offset+15, len-1);
		if(more) $('#friends_res').append(res);
		else $('#friends_res').html(res);
		if(len-1 > _f.offset) $('#friend_load_btn').show();
		else $('#friend_load_btn').hide();
	},
	loadMore: function(){
		return friends.put_friends(1);
	},
	openRequest: function(el){
		var _f = friends;

		$('.tabs li.active').removeClass('active');
		$(el).addClass('active');

		if(_f.req_list) _f.putRequest();
		else{
			ajax('/friends', {act: 'request'}, {
				onDone: function(d){
					_f.req_list = d;
					_f.putRequest();
				}
			});
		}
	},
	putRequest: function(){
		var _f = friends, tab = $('#request_tab');

		$('#friends_tab').hide();
		tab.show();

		var cnt = _f.req_list.cnt, res = _f.req_list.res;

		$('#req_cnt').html(cnt > 0 ? '+'+cnt : '');

		if(cnt > 0){
			var cont = '';
			for(var i in res){
				var tpl = _f.req_tpl, d = res[i];
				tpl = tpl.replace('{name}', d.name).replace(/\{id\}/g, d.id).replace('{online}', d.online ? 'online' : '');
				cont += tpl;
			}
			$('.friends_cnt').html('У вас '+gram(cnt, ['заявка', 'заявки', 'заявок'])+' в друзья');
			tab.html(cont);
		}else{
			$('.friends_cnt').html('Нет заявок в друзья');
			tab.html('<div class="info_center" style="padding:100px 0;">Нет ни одной заявки в друзья</div>');
		}
	}
};