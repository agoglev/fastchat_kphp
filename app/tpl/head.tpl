<html>
	<head>
		<title>{title}</title>
		<link rel="stylesheet" type="text/css" href="/css/main.css"/>
		<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
		<script type="text/javascript">
		var fc = {
			id: '{my_id}',
			name: '{name}'
		}
		</script>
		<script src="/js/main.js"></script><script src="/js/al/notify.js"></script>{st_files}
	</head>
	<body class="no_sel">
		<div class="head">
			<div class="awr">
				<a href="/" onClick="nav.go(this.href); return false;"><div class="logo fl_l">FastChat<div id="msg_count_all"></div></div></a>
				[logged]<div class="menu fl_l">
					<a href="/friends" onClick="nav.go(this.href); return false;"><li>Друзья <span id="req_cnt_head">{head_req}</span></li></a>
				</div>[/logged]
				<div class="menu fl_r">
					[logged]<li onClick="peopleShow()">Люди</li>
					<a href="/login?logout"><li>Выйти</li></a>[/logged]
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div id="top_error" class="fixed"></div>
		<div style="width:100%;height:45px;"></div>
		<div id="page" class="awr">{cont}</div>
		<script type="text/javascript">
			Notify.init();
			{init_js}
		</script>
	</body>
</html>