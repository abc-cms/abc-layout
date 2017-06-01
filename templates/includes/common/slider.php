<?php
$title = htmlspecialchars($q['name']);
$img = get_img('slider',$q,'img');
if ($i==1) {
	?>
<div id="slider" class="carousel slide carousel-fade pb" data-ride="carousel">
	<div class="carousel-inner" role="listbox">
<?php
}
?>
	<div class="item<?=$i==1 ? ' active"' : ''?>">
		<a <?=@$q['url']?'href="'.$q['url'].'"':''?> title=""><img class="img-responsive" src="<?=$img?>" alt="<?=$title?>"></a>
	</div>
<?php
if ($i==$num_rows) {
	?>
	</div>
	<?php if ($num_rows>1) {?>
		<ol class="carousel-indicators">
			<?php
			for ($n=0; $n<$num_rows; $n++) {
				?>
				<li data-target="#slider" data-slide-to="<?=$n?>"<?=$n==0?' class="active"':''?>></li>
			<?php
			}
			?>
		</ol>
	<?php
	}
	?>
	</div>
<?php
}
?>