<?php
$title = htmlspecialchars($q['name']);
if ($i==1) {
	$old=0;
	?>
	<div id="menu">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		<div class="collapse navbar-collapse">
		<ul class="dropdown nav navbar-nav l1">
	<?php
}
if ($old>0 && $old>=$q['level'] ) echo '</li>';
if ($old>$q['level']) for ($n=$q['level']; $n<$old; $n++) echo '</ul></li>';
if ($old<$q['level'] && $old>0) echo '<ul class="dropdown-menu">';
$url = get_url('page',$q);
$class = @$u[1]==$q['url'] ? ' class="active"' : '';

echo '<li'.$class.'><a href="'.$url.'" title="'.$title.'">'.$q['name'].'</a>';

$old = $q['level'];

if ($i==$num_rows) {
	for ($a=1; $a<=$q['level']; $a++) echo '</li></ul>';
?>
	</div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function () {
	$('#menu .dropdown-menu').prev('a').append('<span class="caret"></span>').click(function () {
		$(this).next('.dropdown-menu').toggle();
		return false;
	});
	$('#menu .active').parents('li').addClass('active');
})
</script>
<?php } ?>