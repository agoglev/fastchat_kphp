<div class="main_page_tabs">
	<li style="border-radius: 2px 0 0 2px;" class="active" data-type="login">Вход</li>
	<li style="border-radius: 0 2px 2px 0;" data-type="reg">Регистрация</li>
	<div class="clear"></div>
</div>
<div class="main_tab_cont" id="login_tab">
	<div><input type="text" placeholder="Ваш E-mail" id="email"/>
	<input type="password" placeholder="Ваш пароль" id="pass"></div>
	<button class="button" id="login_btn">Войти</button>
</div>
<div class="main_tab_cont no_display" id="reg_tab">
	<div><input type="text" placeholder="Ваше имя" id="reg_name"/>
	<input type="text" placeholder="Ваша фамилия" id="reg_lname"/>
	<input type="text" placeholder="Ваш E-mail" id="reg_email"/>
	<input type="password" placeholder="Ваш пароль" id="reg_p1">
	<input type="password" placeholder="Пароль ещё раз" id="reg_p2"></div>
	<button class="button" id="reg_btn">Отправить</button>
</div>

<style type="text/css">
.main_page_tabs{width: 240px;list-style: none;margin: 150px auto 15px;background: #5682a3;color: #fff;font-weight: bold;border-radius: 2px;}
.main_page_tabs li{padding: 10px 0;text-align: center;width: 50%;float:left;cursor: pointer;transition: background 200ms ease;}
.main_page_tabs li:hover{background: rgba(255,255,255,0.1)}
.main_page_tabs li.active{background: #48708E;}

.main_tab_cont{width: 240px;margin: 0 auto;}
.main_tab_cont input{padding: 13px 10px;width: 100%;border:1px solid #C3CBD4;font-size: 12px;border-bottom-width: 0px;}
.main_tab_cont input:first-child{border-radius: 2px 2px 0 0;}
.main_tab_cont input:last-child{border-radius: 0 0 2px 2px;border-bottom-width: 1px;}

.main_tab_cont .button{margin-top: 10px;width: 100%;padding: 14px 0;}
.main_tab_cont .button:active{padding: 15px 0 13px;}
</style>
<script type="text/javascript">
$(function(){
	$('.main_page_tabs li').click(function(){
		var obj = $(this), type = obj.attr('data-type');
		$('.main_page_tabs li.active').removeClass('active');
		obj.addClass('active');
		$('.main_tab_cont').hide();
		$('#'+type+'_tab').show();
	});
	$('#login_btn').click(function(){
		var email = $('#email').val(), pass = $('#pass').val();
		if(email) email = email.trim();
		if(pass) pass = pass.trim();
		if(!email) return inputErr('email');
		if(!pass) return inputErr('pass');
		$.post('/login', {email: email, pass: pass}, function(d){
			if(d == 'no') Box.Info('Ошибка', 'Логин либо пароль введены не верно!');
			else if(d == 'ok') location.reload();
		});
	});
	$('#reg_btn').click(function(){
		var email = String($('#reg_email').val()).trim(), 
			p1 = String($('#reg_p1').val()).trim(), 
			p2 = String($('#reg_p2').val()).trim(),
			name = String($('#reg_name').val()).trim(),
			lname = String($('#reg_lname').val()).trim();

		if(!name || name.length < 2) return inputErr('reg_name');
		if(!lname || lname.length < 2) return inputErr('reg_lname');
		if(!email || !validAddres(email)) return inputErr('reg_email');
		if(!p1) return inputErr('reg_p1');
		if(p1 != p2) return inputErr('reg_p2');

		$.post('/reg', {email: email, name: name, lname: lname, pass: p1, pass2: p2}, function(d){
			if(d == 'mail') Box.Info('Ошибка', 'Выбранный вами E-mail адрес уже используется другим пользователем!');
			else if(d == 'ok') location.reload();
			else Box.Info('Ошибка', 'Неизвестная ошибка');
		});
	});
});
function validAddres(mail) {
	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
 	return pattern.test(mail);
}
function isValidName(xname){
	var pattern = new RegExp(/^[\sa-zA-Zа-яА-Я\u0600-\u06ff]+$/);
		return pattern.test(xname);
}
</script>