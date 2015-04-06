<div class="profile_left fl_l">
	<div class="profile_ava_bl">
		<img src="/img/camera_400.gif" id="profile_ava">
		<div class="ava_settings">
			<li onClick="document.getElementById('upload_ava').click();">Загрузить новую фотографию</li>
		</div>
		<input type="file" id="upload_ava" style="display:none;" onchange="uploadAva(this.files[0])" accept="image/*"/>
	</div>
</div>
<div class="profile_right fl_l">
	<div class="profile_name">{name} {lname}</div>
</div>
<div class="clear"></div>
<style type="text/css">
.profile_left{width: 200px;}
.profile_right{width: 570px;padding: 15px;}

.profile_name{font-size: 16px;font-weight: bold;color:#2B587A;}

.profile_ava_bl{position: relative;overflow: hidden;}
#profile_ava{width: 100%;}
.ava_settings{list-style: none;position: absolute;bottom: -33px;background: rgba(0,0,0,0.7);width: 100%;left:0;transition: bottom 200ms ease;}
.ava_settings li{color: rgba(255,255,255,0.7);padding: 10px 20px;transition: all 200ms ease;}
.ava_settings li:hover{color:#fff;background: rgba(0,0,0,0.3);cursor: pointer;}
.profile_ava_bl:hover .ava_settings{bottom: 0px;}

</style>
<script type="text/javascript">
function uploadAva(file){
	var type = file.name.split('.');
	type = type[type.length-1];

	if(!type.match(/gif|png|jpg|jpeg/)) return Box.Info('Не верный формат', 'Выбирите вотографию формата GIF, PNG или JPG', 5000);

	if(file.size > (15*1024*1024)) return Box.Info('Большой размер', 'Выбранная вами фотография очень большая, выбирите фотографию меньших размеров', 5000);

	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(e){
		if(e.target.readyState == 4){
			if(e.target.status == 200){
				var res = this.responseText;
				if(res){
					res = JSON.parse(res);
					console.log(res);
					if(res.err){
						switch(res.err){
							case 'format': Box.Info('Не верный формат', 'Выбирите вотографию формата GIF, PNG или JPG', 5000); break;
							case 'size': Box.Info('Большой размер', 'Выбранная вами фотография очень большая, выбирите фотографию меньших размеров', 5000); break;
							case 'noupload': Box.Info('Что-то не так', 'Нам не удалось загрузить вашу фотографию, попробуйте позже..', 5000); break;
						}
					}
					if(res.ok) $('#profile_ava').attr('src', res.link);
				}else Box.Info('Ошибка', 'Неизвестная ошибка', 5000);
			}
		}
	};
	xhr.open('POST', '/upload?act=ava');
	var form = new FormData();
	form.append('up_file', file);
	xhr.send(form);
}
</script>