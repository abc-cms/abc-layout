<h1><?=$q['name']?></h1>
<?php if ($q['text']) {?>
<?=@$q['text']?>
<?php } ?>
<?php if (isset($q['category_list'])) {?>
<?=$q['category_list']?>
<?php } else {?>
<?=$q['filter']?>
<?=$q['product_list']?>
<?php } ?>

