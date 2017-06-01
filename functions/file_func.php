<?php

/**
 * функции для работы с файлами
 */

/**
 * рекурсивное удаление папок с содержимым
 * для безопасного удаления папки рекомендуется $dir передавать со слешем в конце
 * @param $dir - полный путь к папке
 * @param bool $i - удалять саму папу или нет
 * @return bool
 * @version v1.1.22
 */
function delete_all($dir,$i = true) {
	//заменяем все обратные слеши на нормальные
	$dir = str_replace('\\','/',$dir);
	//если есть подряд два слеша то удаления не будет так как есть вероятность что пропущена папка
	if (strpos($dir, '//')) return false;
	//если заканчивается на один слеш то удаляем его
	if (substr($dir, -1)=='/') $dir = substr($dir, 0, -1);
	//удаление файла
	if (is_file($dir)) return unlink($dir);
	if (!is_dir($dir)) return false;
	//запускаем рекурсию
	$dh = opendir($dir);
	while (false!==($file = readdir($dh))) {
		if ($file=='.' || $file=='..') continue;
		delete_all($dir.'/'.$file);
	}
	closedir($dh);
	//удаляем саму папку
	if ($i==true) return rmdir($dir);
}


/**
 * копирование папок с файлами
 * @param $src - старый путь
 * @param $dst - новый путь
 */
function rcopy($src, $dst) {
	if (file_exists($dst)) delete_all($dst);
	if (is_dir($src)) {
		mkdir($dst);
		$files = scandir($src);
		foreach ($files as $file)
			if ($file != "." && $file != "..") rcopy("$src/$file", "$dst/$file");
	}
	else if (file_exists($src)) copy($src, $dst);
}

/**
 * функция для копирование файла с генерацией превью
 * @param $temp_file - полный путь к временному файлу
 * @param $root - полный путь к корневой папка c картинками всегда со слешем в конце
 * @param $file - название файла
 * @param $param - параметры картинки
 * @return bool
 * @version v1.1.16
 * v1.1.16 - добавилась
 */
function copy2 ($temp_file,$root,$file,$param=array()) {
	//log_add('file.txt',array($temp_file,$root,$file,$param));
	//если есть подряд два слеша то это ошибка и функция отключается
	if (strpos($root, '//') OR strpos($root, '\\\\')) return false;
	if (is_dir($root)) delete_all($root,false); //удаление старого файла
	if ($temp_file && (is_dir($root) || mkdir ($root,0755,true))) { //создание папок для файла
		include_once(ROOT_DIR . 'functions/image_func.php');
		//загрузка с параметрами
		if (is_array($param)) {
			$param['a-'] = 'resize 100x100'; //для превью в админке
			foreach ($param as $k => $v) {
				if ($v) {
					$prm = explode(' ', $v);
					img_process($prm[0], $temp_file, $prm[1], $root . $k . $file);
					//если есть водяной знак
					if (isset($prm[2])) img_watermark($root . $k . $file, ROOT_DIR . 'templates/images/' . $prm[2], $root . $k . $file, isset($prm[3]) ? $prm[3] : '');
				}
				//простое копирование - сохранение оригинальных размеров
				else copy($temp_file, $root . $k . $file);
			}
		} //простая загрузка
		else {
			img_process('resize', $temp_file, '100x100', $root . 'a-' . $file);    //для превью в админке
			copy($temp_file, $root . $file);
		}
		if (is_file($root.$file)) {
			return true;
		}
	}
	return false;
}
