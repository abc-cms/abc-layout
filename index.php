<?php
/**
 * Список страниц для верстки
 * документация
 * https://docs.google.com/document/d/1SccM1zcgpJSSlZSMSUDK4LbnjG_oJkxC2qc1d4YMTAE/edit
 */

$config['layout_version'] = '1.0.1 - 1.2.22';

//папка со стилями
$config['style'] = 'templates';

//список модулей
$modules = array(
	'index'=>'Главная',
	'shop_category'=>'Список товаров',
	'shop_product'=>'Страница товара',
);

//файл с архивом верстки
$archive = 'layout.zip';

//если файлов с данными в папке /templates/_data/ много
$json = false;
//если файл только один то указываем название файла /templates/_data/data.json
//$json = 'data';

//переменная с массивами данных из папки /templates/_data/ а так же метаданными старницы
$page = array();

//переменная со словами словаря /templates/_data/dictionary.json
$lang = array();

//переменная с кусками нтмл кода
$html = array();

//массив всех подключаемых css и js файлов
//{localization} - будет заменяться на $lang['localization']
//? будет заменятся на гет параметр времени создания сайта
$config['sources'] = array(
	'bootstrap.css'             => '/templates/plugins/bootstrap/css/bootstrap.min.css',
	'bootstrap.js'              => '/templates/plugins/bootstrap/js/bootstrap.min.js',
	'common.css'				=> '/templates/css/common.css?',
	'common.js'				    => '/templates/scripts/common.js?',
	'editable.js'				=> '/templates/scripts/editable.js',
	'font.css'				    => '/templates/css/font.css',
	'highslide'					=> array(
		'/templates/plugins/highslide/highslide.packed.js',
		'/templates/plugins/highslide/highslide.css',
	),
	'highslide_gallery' 		=> array(
		'/templates/plugins/highslide/highslide-with-gallery.js',
		'/templates/scripts/highslide.js',
		'/templates/plugins/highslide/highslide.css',
	),
	'jquery.js'					=> '/templates/plugins//jquery/jquery-1.11.3.min.js',
	'jquery_cookie.js'			=> '/templates/plugins//jquery/jquery.cookie.js',
	'jquery_ui.js'				=> '/templates/plugins//jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js',
	'jquery_ui.css'			    => '/templates/plugins//jquery/jquery-ui-1.11.4.custom/jquery-ui.min.css',
	'jquery_localization.js'	=> '/templates/plugins//jquery/i18n/jquery.ui.datepicker-{localization}.js',
	'jquery_form.js'			=> '/templates/plugins//jquery/jquery.form.min.js',
	'jquery_uploader.js'		=> '/templates/plugins//jquery/jquery.uploader.js',
	'jquery_validate.js'		=> array(
		'/templates/plugins/jquery/jquery-validation-1.8.1/jquery.validate.min.js',
		'/templates/plugins/jquery/jquery-validation-1.8.1/additional-methods.min.js',
		'/templates/plugins/jquery/jquery-validation-1.8.1/localization/messages_{localization}.js',
	),
	'jquery_multidatespicker.js'=> '/templates/plugins//jquery/jquery-ui.multidatespicker.js',
	'reset.css'					=> '/templates/css/reset.css',
	'tinymce.js'				=> '/templates/plugins//tinymce/tinymce.min.js',//старый тинумайс
	'tinymce.js'				=> '/templates/plugins//tinymce_4.3.11/tinymce.min.js',
);

//функция для урл
function get_url($module='') {
	if ($module) return '/?module='.$module;
	return '';
}

//функция для картинок
function get_img($table,$q,$key='img') {
	global $config;
	//если картинка есть
	if ($q['img']) {
		//полный путь к картинке
		//return $q[$key];
		return '/'.$config['style'].'/_images/'.$table.'/'.$q[$key];
	}
	//если нет выводим заглушку
	else {
		//можно на разный тип картинок выводить разную заглушку
		if ($table=='shop_products') return '/templates/images/no_img.svg';
		return '/templates/images/no_img.svg';
	}
}

$config['editable'] = false;
//charset
$config['charset']			= 'UTF-8';
$config['cache'] = 0;

error_reporting(E_ALL);

header('Content-type: text/html; charset='.$config['charset']);
header('X-UA-Compatible: IE=edge,chrome=1');

define('ROOT_DIR', dirname(__FILE__).'/');

require_once(ROOT_DIR.'functions/html_func.php');	//функции для работы нтмл кодом
require_once(ROOT_DIR.'functions/lang_func.php');
require_once(ROOT_DIR.'functions/common_func.php');
require_once(ROOT_DIR.'functions/auth_func.php');

//если все в одном файле то делаем один многоуровневый массив
if ($json) {
	$path = ROOT_DIR . $config['style'] . '/_data/'.$json.'.json';
	$data = file_get_contents($path);
	$page = json_decode($data, true);
	$lang = $page['lang'];
}
else {
	$root = ROOT_DIR . $config['style'] . '/_data/';
	$files = scandir($root,1);
	foreach ($files as $file) {
		if (substr($file, -4)!='json' OR $file=='..' OR $file=='.') continue;
		$path = ROOT_DIR . $config['style'] . '/_data/'.$file;
		$data = file_get_contents($path);
		$key = substr($file, 0, -5);
		$page[$key] = json_decode($data, true);
	}
	//подключаем словарь
	$dictionary = file_get_contents(ROOT_DIR.$config['style'].'/_data/dictionary.json');
	$lang = json_decode($dictionary,true);
}

$u = array();

//загружаем модуль
$html['module'] = @$_GET['module'];
//echo $html['module'];
if (array_key_exists($html['module'] ,$modules)) {
	//путь к модулю
	$path = ROOT_DIR.$config['style'].'/_modules/'.$html['module'].'.php';
	//основной шаблон
	$template = ROOT_DIR.$config['style'].'/includes/common/template.php';
	if (file_exists($path)) {
		$page['name'] = $modules[$html['module']];
		require_once($path);
		require_once($template);
	}
	else {
		echo 'error';
	}
}
else {
	echo '<ol style="padding:100px 100px 0;">';
	foreach ($modules as $k=>$v) {
		echo '<li style="padding:10px"><a href="?module='.$k.'">'.$v.'</a></li>';
	}
	echo '</ol>';
	if (file_exists($archive)) {
		echo '<div style="padding:10px 110px">';
		echo '<a href="'.$archive.'"><b>СКАЧАТЬ АРХИВ</b></a>';
		echo '</div>';
	}
}