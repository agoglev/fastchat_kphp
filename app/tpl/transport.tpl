<html>
	<head>
		<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
		<script type="text/javascript">
			var ts = '{ts}', ses = '{session}';
			function start_queue(d){
				ts = d.ts;
				ses = d.ses;
				query();
			}
			function query(){
				$.ajax({
					url: '/im255',
					method: 'POST',
					dataType : 'json',
					data: {act: 'a_check', key: ses, ts: ts, wait: 25, mode: 8},
					success: function (d) {
						if(d.failed) return fail_query(d);
						ts = d.ts;
						if(d.updates.length) window.parent.newQueueEv(d.updates);
						setTimeout(query, 200);
					} 
				}).fail(function(){
					setTimeout(query, 2000);
				});
			}
			function fail_query(d){
				console.log('queue fail', d);
				console.log('reinit..');
				$.post('/transport.php', {act: 'reinit'}, function(d){
					d = JSON.parse(d);
					if(d.no_log) return console.log('no logged, stopped queue');
					ts = d.ts;
					ses = d.ses;
					console.log('recovered');
					query();
				});
			}
			window.onload = query;
		</script>
	</head>
	<body></body>
</html>