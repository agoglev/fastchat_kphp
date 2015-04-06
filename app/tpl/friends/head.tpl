<div class="tabs">
	<li class="active" onClick="friends.change_mode('all', this);">Все друзья</li>
	<li onClick="friends.change_mode('online', this);">Друзья онлайн</li>
	<li onClick="friends.openRequest(this);">Зявки в друзья <span id="req_cnt">{req_cnt}</span></li>
	<div class="clear"></div>
</div>
<div class="friends_cnt">У вас {cnt}</div>
<div class="content">
	<div id="friends_tab">
		<div class="friends_search_form">
			<input type="text" placeholder="Начните вводить имя или фамилию друга.." id="friends_search_query" onkeyup="friends.search();">
			<button class="button" onClick="friends.search();">Искать</button>
		</div>
		<div id="friends_res">{res}</div>
		<div class="load_btn{load_but}" id="friend_load_btn" onClick="friends.loadMore();">Показать больше</div>
	</div>
	<div id="request_tab"></div>
</div>