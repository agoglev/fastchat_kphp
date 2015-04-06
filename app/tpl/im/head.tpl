<div class="im_wrap">
	<div class="im_chats fl_l">
		<div class="im_search">
			<input type="text" placeholder="Поиск.." onkeyup="im.search(this.value)" id="dialogs_search_q"/>
		</div>
		<div id="contacts" class="nano">
			<div class="nano-content" id="contacts_res">
				<div id="all_dialogs">{dialogs}</div>
				<div id="search_result_dialogs"></div>
			</div>
		</div>
	</div>
	<div class="im_cont fl_l">
		<div class="im_no_sel">Выбирите собеседника</div>
		<div id="im_preloader"><img src="/img/big.gif"></div>
		<div id="chat_view_bl">
			<div class="im_user_panel">
				<div class="name fl_l">User Name</div>
				<div id="im_head_online" class="fl_l">Online</div>
				<div class="clear"></div>
			</div>
			<div id="messages_bl">
				<div id="messages_nano" class="nano">
					<div class="nano-content" id="messages_nano_res">
						<div id="message_all_cont">
							<div id="messages_res"></div>
							<div id="im_offline"><span>Name</span> последний раз был {date}</div>
							<div id="im_typing"><img src="/img/typing.gif"/> <span>Name</span> набирает сообщение</div>
						</div>
					</div>
				</div>
			</div>
			<div class="im_send_form">
				<textarea id="im_text" placeholder="Введите Ваше сообщение.."></textarea>
				<div class="send_cont">
					<button class="button fl_l" onClick="im.send();">Отправить</button>
					<div class="im_send_inf fl_l">перенос строки Ctrl+Enter</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>
<audio src="/song/chat.mp3" autoload id="im_song"/></audio>
