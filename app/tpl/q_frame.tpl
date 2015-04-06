<html>
	<head>
		<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
		<script type="text/javascript">
		var ts = '{ts}', key = '{key}', id = '{uid}';
		function check(){
			$.ajax({
					url: '/im266',
					method: 'POST',
					dataType : 'json',
					data: {act: 'a_check', key: key, ts: ts, wait: 25, id: id},
					success: function (d) {
						if(d.failed == 2){
							setTimeout(reconnect, 2000);
							return;
						}
						ts = d.ts;
						if(d.events.length > 0) window.parent.getFrameData(d.events);
						setTimeout(check, 200);
					} 
				}).fail(function(){
					
				});
		}
		function reconnect(){
			$.post('/q_frame.php', {data_only: 1}, function(d){
				if(d){
					d = JSON.parse(d);
					ts = d.ts;
					key = d.key;
					id = d.id;
					check();
				}
			});
		}
		window.onload = check;
		</script>
	</head>
	<body></body>
</html>