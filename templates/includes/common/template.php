<?php
$page['title']			= isset($page['title']) ? filter_var($page['title'], FILTER_SANITIZE_STRING) : filter_var($page['name'],FILTER_SANITIZE_STRING);
$page['description']	= isset($page['description']) ? filter_var($page['description'], FILTER_SANITIZE_STRING) : $page['title'];
$page['keywords']		= isset($page['keywords']) ? filter_var($page['keywords'], FILTER_SANITIZE_STRING) : $page['title'];
?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title><?=$page['title']?></title>
<meta name="description" content="<?=$page['description']?>" />
<meta name="keywords" content="<?=$page['keywords']?>" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
//v1.1.15 - запрет индексации на тестовом
if(strripos($_SERVER['HTTP_HOST'], '.abc-cms.com') OR strripos($_SERVER['HTTP_HOST'], '.tyt.kz')) {
	?><meta name="robots" content="noindex, follow" /><?php
}
?>
	<?=html_sources('return','bootstrap.css font.css common.css')?>
	<?=i18n('common|script_head')?>
	<?=html_sources('head')?>

</head>

<body>
<div id="body">
	<div class="container" id="header">
		<div class="row pb">
			<div class="col-lg-3 col-xs-12 pt">
				<a href="/" title="название сайта"><img src="/<?=$config['style']?>/images/logo.jpg" alt="имя сайта" /></a>
			</div>
			<div class="col-lg-6 col-xs-12 pt">
				текст в шапке
			</div>
		</div>
	</div>

	<div id="wrapper">
		<div class="container">
			<?=html_query('menu/list',$html['menu'])?>

			<div class="row pt">

				<?php if (isset($breadcrumb)) echo html_array('common/breadcrumb',$breadcrumb);?>

				<?php
				if (file_exists(ROOT_DIR.$config['style'].'/includes/modules/'.$html['module'].'.php')) include(ROOT_DIR.$config['style'].'/includes/modules/'.$html['module'].'.php');
				else include(ROOT_DIR.$config['style'].'/includes/modules/default.php');
				?>

			</div>

		</div>
	</div>
</div>
<div id="footer">
	<div class="container">
		<div class="row">
			<div class="col-md-3 col-sm-4 col-xs-12 pb">
				<?=i18n('common|info',true)?>
				<?=html_query('menu/list2',$html['shop_categories'])?>
			</div>
			<div class="col-md-3 col-sm-4 col-xs-12 pb">
				<h4><?=i18n('profile|link',true)?></h4>
				<ul>
					<li><a href=""><?=i18n('profile|user_edit')?></a></li>
					<li><a href=""><?=i18n('basket|orders')?></a></li>
				</ul>
			</div>
			<div class="col-md-3 col-sm-4 col-xs-12 pb">
				<?=i18n('common|social',true)?>
			</div>
			<div class="col-md-3 col-sm-12 col-xs-12 pb">
				<?=i18n('common|txt_footer',array('Y'=>date('Y')))?>
			</div>
		</div>
	</div>
</div>
<?=html_sources('return','jquery.js bootstrap.js')?>
<?=html_sources('footer')?>
<?=html_sources('return','common.js')?>
<?=i18n('common|script_body_end')?>
</body>
</html>