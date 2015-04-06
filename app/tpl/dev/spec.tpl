<div class="dev_cont">
	<div class="dev_title">Выполнение запросов к API</div>
	<div class="dev_descr">Для выполнения запроса к API необходимо отпавить POST или GET запрос на: <br>http://fastchat.su/api?method=<b>METHOD_NAME</b>&<b>PARAMETERS</b>&access_token=<b>ACCESS_TOKEN</b></div>
	<div class="dev_param">
		<li><span><b>METHOD_NAME</b> – название метода API</span></li>
		<li><span><b>PARAMETERS</b> – параметры соответствующего метода API</span></li>
		<li><span><b>ACCESS_TOKEN</b> – ключ доступа, полученный в результате успешной авторизации</span></li>
	</div>
</div>
<div class="dev_cont">
	<div class="dev_title">Method: auth</div>
	<div class="dev_descr">Автризация, получает ключ доступа к данным пользователя</div>
	<div class="dev_param">
		<li><span><b>app_id</b> – индификатор приложения</span></li>
		<li><span><b>app_secret</b> – секретный ключ приложения</span></li>
		<li><span><b>email</b> – email адрес пользователя</span></li>
		<li><span><b>password</b> – пароль пользователя</span></li>
	</div>
	<div class="dev_title">Результат</div>
	<blockquote>{"uid":USER_ID,"access_token":ACCESS_TOKEN}</blockquote>
	<div class="dev_param">
		<li><span><b>USER_ID</b> – индификатор пользователя</span></li>
		<li><span><b>ACCESS_TOKEN</b> – ключ доступа</span></li>
	</div>
</div>
<div class="dev_cont">
	<div class="dev_title">Method: check_token</div>
	<div class="dev_descr">Проверяет актуальность ключа доступа</div>
	<div class="dev_param">
		<li><span><b>app_id</b> – индификатор приложения</span></li>
		<li><span><b>access_token</b> – секретный ключ приложения</span></li>
	</div>
	<div class="dev_title">Результат</div>
	<blockquote>{"uid":USER_ID}</blockquote>
	<div class="dev_param">
		<li><span><b>USER_ID</b> – индификатор пользователя, равен <b>0</b> если токен не актуален</span></li>
	</div>
</div>
<div class="dev_cont">
	<div class="dev_title">Method: user.get</div>
	<div class="dev_descr">Получает информацию о пользователе</div>
	<div class="dev_param">
		<li><span><b>user_id</b> – индификатор пользователя (необязательно если передан <i>access_token</i>)</span></li>
		<li><span><b>access_token</b> – секретный ключ приложения (необязательно)</span></li>
	</div>
	<div class="dev_title">Результат</div>
	<blockquote>{"first_name":"Andrey","sur_name":"Goglev","online":1}</blockquote>
	<div class="dev_param">
		<li><span><b>first_name</b> – имя пользователя</span></li>
		<li><span><b>sur_name</b> – фамилия пользователя</span></li>
		<li><span><b>online</b> – равно <b>1</b>, если онлайн</span></li>
	</div>
</div>
<br/>