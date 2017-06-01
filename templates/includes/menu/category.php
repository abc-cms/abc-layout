<?php
$title = htmlspecialchars($q['name']);
$url = get_url('shop_category',$q);
if ($i==1) {
	$old=0;?>
<div id="menu_category" class="pb">
	<h4><span><?=i18n('shop|catalog')?></span></h4>
	<ul class="toggle_content tree dhtml">
		<?php
}
//закрываем item
if ($old>0 && $old>=$q['level'] ) echo '</li>';
//закрываем список
if ($old>$q['level']) for ($n=$q['level']; $n<$old; $n++) echo '</ul></li>';
//открываем список
if ($old<$q['level'] && $old>0) echo '<ul>';
//активный пункт меню
$class = $i==2 ? ' class="active"' : '';
?>
	<li <?=$class?>>
		<a href="<?=$url?>" title="<?=$title?>">
			<i class="icon-caret-right"></i>
			<?=$q['name']?>
		</a>
<?php
$old = $q['level'];
//закрываем все незакрытые списки
if ($i==$num_rows) {
	for ($n=1; $n<=$q['level']; $n++) echo '</li></ul>';
?>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function () {
	$('<i class="grower icon-plus-sign"></i>').insertBefore('#menu_category li ul');
	$(document).on('click', '#menu_category .grower', function () {
		if ($(this).hasClass('icon-plus-sign'))
			$(this).removeClass("icon-plus-sign").addClass("icon-minus-sign").next('ul').toggle();
		else $(this).removeClass("icon-minus-sign").addClass("icon-plus-sign").next('ul').toggle();
	});
	//$('#menu_category .active').parents('li').addClass('active');
	$('#menu_category .active').parents('ul').show();
})
</script>
<?php } ?>