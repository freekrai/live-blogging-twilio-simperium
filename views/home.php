<div class="jumbotron">
	<h1>Live Blog</h1>
</div>
<br />
<section id="talkwrap" style="overflow: auto;margin-bottom: 10px;">
	<div id="talk"></div>
</section>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="https://js.simperium.com/v0.1/"></script>
	<script>
		function formatDate(date, fmt) {
		    function pad(value) {
		        return (value.toString().length < 2) ? '0' + value : value;
		    }
		    return fmt.replace(/%([a-zA-Z])/g, function (_, fmtCode) {
		        switch (fmtCode) {
		        case 'Y':
		            return date.getUTCFullYear();
		        case 'M':
		            return pad(date.getUTCMonth() + 1);
		        case 'd':
		            return pad(date.getUTCDate());
		        case 'H':
		            return pad(date.getUTCHours());
		        case 'm':
		            return pad(date.getUTCMinutes());
		        case 's':
		            return pad(date.getUTCSeconds());
		        default:
		            throw new Error('Unsupported format code: ' + fmtCode);
		        }
		    });
		}
//formatDate(new Date(timestamp), '%H:%m:%s');
		var simperium = new Simperium('<?=$appname?>', { token : '<?=$token?>'});
		var bucket = simperium.bucket('<?=$bucket?>');
		bucket.on('notify', function(id, data) {
			var date = formatDate(new Date(data.timeStamp*1000), '%d/%M/%Y @ %H:%m:%s');
			$("#talk").append("<div class='well'><strong>"+date+':</strong><br />'+data.text+'</div>');
			$('#talkwrap').scrollTop($('#talkwrap')[0].scrollHeight);
		});
		bucket.on('local', function(id) {
		    console.log("request for local state for object "+id+" received");
		    return {"some": "json"};
		});
		bucket.start();
	</script>
