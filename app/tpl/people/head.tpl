<div class="box" id="box_people">
	<div class="box_pos" style="width: 400px;">
		<div class="box_head">
			<div class="box_title fl_l">Поиск людей</div>
			<div class="box_close fl_r" onClick="Box.Clos('box_people');">Закрыть</div>
			<div class="clear"></div>
		</div>
		<div class="box_cont">
			<div class="people_search_panel">
				<input type="text" placeholder="Начните вводить имя или фамилию.." id="people_query">
				<img src="/img/loading.gif"/>
			</div>

			<div id="people_search_result">{res}</div>
			<div class="load_btn {load}" id="peopleLoadBut" onClick="peopleLoadMore()">Показать больше</div>
		</div>
	</div>
</div>
<style type="text/css">
.people_item{padding: 10px 0;border-top: 1px solid #E9ECEF;}
.people_item:first-child{border-top: 0px;}
.people_item img{border-radius: 25px;}
.people_item .cont{margin-left: 10px;float: left;}
.people_item .name{font-weight: bold;color:#2B587A;font-size: 12px;margin-top: 4px;}
.people_item .cont .button{margin-top: 10px;}

.people_item .actions{list-style: none;color:#2B587A;}
.people_item .actions li{padding: 7px 10px;cursor: pointer;}
.people_item .actions li:hover{background: #E1E7ED;}

.people_search_panel{margin: -20px 0 20 -20px;width: 380px;background: #EEF0F2;padding: 10px;position: relative;}
.people_search_panel input{width: 100%;}
.people_search_panel img{position: absolute;top: 21px;right: 20px;display: none;}

#people_search_result{margin-bottom: 10px;}
</style>
<script type="text/javascript">
//Извращение без таймаута..
var peoplePage = 1;
$('#people_query').keyup(function(){
	var val = $(this).val(), btn = $('#peopleLoadBut');
	if(val) val = val.trim();
	$('.people_search_panel img').show();
	btn.hide().attr('onClick', '');
	$.post('/people', {act: 'list', doload: 1, val: val}, function(d){
		peoplePage = 1;
		$('#people_search_result').html(d);
		$('.people_search_panel img').hide();
		if($('#people_search_result .people_item').length >= 20) btn.html('Показать больше').attr('onClick', 'peopleLoadMore()').show();
	});
});
function peopleLoadMore(){
	var val = $('#people_query').val(), btn = $('#peopleLoadBut');
	if(val) val = val.trim();
	btn.html('<img src="/img/loading.gif"/>').attr('onClick', '');
	$.post('/people', {act: 'list', doload: 1, val: val, page: peoplePage}, function(d){
		if(d){
			$('#people_search_result').append(d);
			peoplePage++;
			btn.html('Показать больше').attr('onClick', 'peopleLoadMore()');
		}else btn.hide();
	});
}
</script>