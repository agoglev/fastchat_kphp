<div class="box" id="box_delfr">
	<div class="box_pos" style="width: 400px;">
		<div class="box_head">
			<div class="box_title fl_l">Удаление друга</div>
			<div class="box_close fl_r" onClick="Box.Clos('box_delfr');">Закрыть</div>
			<div class="clear"></div>
		</div>
		<div class="box_cont" style="padding:0">
			<div style="padding:20px;line-height:160%;font-size: 12px;">{name} будет удален из списка ваших друзей, отменить действие будет невозможно</div>
			<div class="box_footer">
				<div class="fl_r">
					<button class="button fl_l" onClick="friends.del({id});">Продожить</button>
					<button class="button fl_l inline" onClick="Box.Clos('box_delfr');">Отмена</button>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>