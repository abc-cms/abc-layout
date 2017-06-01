<input name="captcha" type="hidden" value="<?=time()?>" />
<?php if (!isset($config['_captcha'])) {
	$config['_captcha'] = true;
	?><script type="text/javascript">document.addEventListener("DOMContentLoaded", function () {$.get('/ajax.php',{'file':'captcha'},function(data){if(data)$('input[name="captcha"]').val(data)})})</script><?php
}