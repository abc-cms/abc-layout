<?php
if (is_array($q)) {
	//если пустой массив то ничего не выводим
	if ($q) {
		?>
<ul class="form_message bg-danger">
	<?php foreach ($q as $k => $v) echo html_array('form/message', $v) ?>
</ul>
		<?php
	}
}
else {
	?>
<li><?=$q?></li>
	<?php
}
?>
