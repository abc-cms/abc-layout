<?php

/*
 * функции для работы с БД
 */

/**
 * соединение с БД
 * @param string $server
 * @param string $username
 * @param string $password
 * @param string $database
 * @return bool - подключено или нет
 */
function mysql_connect_db($server='',$username='',$password='',$database='') {
	global $config;
	if (@$config['mysql_connect']==false) {
		//если подключение без параметров то используем данные из $config
		if ($server=='') {
			$server		= $config['mysql_server'];
			$username	= $config['mysql_username'];
			$password	= $config['mysql_password'];
			$database	= $config['mysql_database'];
		}
		if ($connect = @mysql_connect($server,$username,$password)) {
			if (mysql_select_db($database,$connect)) {
				mysql_query("SET NAMES '" . $config['mysql_charset'] . "'");
				mysql_query("SET CHARACTER SET '" . $config['mysql_charset'] . "'");
				//сброс настроек, чтобы нормально проходили инсерты и апдейты
				//например когда не передают значение поля у которого нет значения по умолчанию
				mysql_query("SET @@GLOBAL.sql_mode= ''");
				mysql_query("SET @@SESSION.sql_mode= ''");
				$config['mysql_connect'] = true;
				return true;
			}
			$config['mysql_error'] = 'cannot connect to database';
			trigger_error($config['mysql_error'], E_USER_DEPRECATED);
			return false;
		}
		$config['mysql_error'] = 'cannot connect to mysql server';
		trigger_error($config['mysql_error'], E_USER_DEPRECATED);
		return false;
	}
	else return true;
}

/**
 * отключение соединения с БД
 * @version v1.2.6
 * v1.2.6 - добавлена
 */
function mysql_close_db() {
	global $config;
	if (@$config['mysql_connect']) {
		mysql_close();
		$config['mysql_connect'] = false;
	}
}

/**
 * своя mysql_real_escape_string
 * @param string $str - строка для экранирования
 * @return string - экранированное значение
 */
function mysql_res ($str) {
	if (mysql_connect_db()) return mysql_real_escape_string($str);
	return false;
}


/**
 * выборка с БД
 * @param string $query - SQL запрос
 * @param string $type - тип данных ответа [string,num_rows,row,rows,rows_id,array]
 * string - строка, одна ячейка из запроса SELECT name FROM ..
 * num_rows - количество записей
 * row - одна строка, массив - SELECT id,name,text .. LIMIT 1 => array('id'=>'12','name'=>'Название','text'=>'текст')
 * rows - массив из row
 * rows_id массив из row где ключем будет id
 * array - массив $k->$v - SELECT id,name .. FROM LIMIT 1 => array(1=>'значение',2=>'значение')
 * @param int $cache - время жизни кеша в секундах
 * @return array|int|string - данные с базы
 * @version v1.1.13
 * v1.1.13 - временные метки для лога скл запросов
 * v1.1.28 - правильный тип данных в mysql_select
 */
function mysql_select($query,$type='rows',$cache=false) {
	global $config;
	$file	= ROOT_DIR.'cache/'.md5($query).'.php';
	//используем кеш
	if (@$config['cache'] && $cache && file_exists($file) && (time()-$cache)<filemtime($file)) {
		$config['queries'][] = array(md5($query).'.php',$query);
		$result = file_get_contents ($file);
		return json_decode($result,true);
	}
	//запросы к БД
	else {
		//$config['queries'][] = $query;
		if (mysql_connect_db()) {
			$time = microtime(true);
			$result = mysql_query($query); //echo $query;
			//сделано, чтобы функция возвращала правильный тип данных даже если нет результатов
			if (in_array($type,array('string','num_rows'))) $data = '';
			else $data = array();
			if ($error = mysql_error()) {
				trigger_error($error.' '.$query, E_USER_DEPRECATED);
				return $data;
			}
			//строка
			if ($type=='string')		$data = @mysql_result($result,0);
			//количество записей
			elseif ($type=='num_rows')	$data = mysql_num_rows($result);
			//один ряд массивом
			elseif ($type=='row')		$data = mysql_fetch_assoc($result);
			//несколько рядов двоуровневым массивом
			elseif ($type=='rows')		while ($q = mysql_fetch_assoc($result)) $data[] = $q;
			//несколько рядов двоуровневым массивом с ключем ИД
			elseif ($type=='rows_id')	while ($q = mysql_fetch_assoc($result)) $data[$q['id']] = $q;
			// несколько рядов с выдачей поля Field (используется для просмотра доступных полей таблицы)
			elseif ($type=='rows_field')	while ($q = mysql_fetch_assoc($result)) $data[$q['Field']] = $q;
			//массив {id}->{name}
			elseif ($type=='array')		while ($q = mysql_fetch_assoc($result)) $data[$q['id']] = $q['name'];
			//кеширование
			if (@$config['cache'] && $cache) {
				if (is_dir(ROOT_DIR.'cache') || mkdir(ROOT_DIR.'cache',0755,true)) {
					$f = fopen($file,'w');
					fwrite($f,json_encode($data));
					fclose($f);
				}
			}
			$time = microtime(true)-$time;
			$config['queries'][] = array($time,$query);
			return $data;
			//возвращаем false если пустой массив (пустой результат запросов array,rows_id,rows)
			//return (is_array($data) AND count($data)==0) ? false : $data;
		}
	}
}

/**
 * запросы к БД кроме селект
 * @param string $type - тип запроса [inser,update,delete]
 * @param string $tbl_name - название таблицы или весь запрос если $type=='query'
 * @param array $post - массив данных
 * @param string $where -
 * @return boolean|int|string - да/нет | ID инсера | запрос удаления
 * @version v1.1.13
 * v1.1.10 обработка NULL
 * v1.1.13 - временные метки для лога скл запросов
 * v1.1.26 - исправление ошибки при удалении (нет массива $post)
 */
function mysql_fn($type, $tbl_name, $post=array() ,$where = '', $ignore = false) {
	global $config;
	if (mysql_connect_db()) {
		$query = '';
		//если четвертый параметр - массив
		if (!is_string($where)) {
			$exceptions = $where;
			$where = '';
		}
		else $exceptions = false;

		//тело запроса INSERT множества записей
		if ($type == 'insert values') {
			$into = implode('`,`', array_keys(current($post)));
			foreach ($post as $q) {
				$values = array();
				foreach ($q as $v) $values[] = "'" . mysql_res($v) . "'";
				$sql[] = implode(',', $values);
			}
			$sql = implode('),(', $sql);
		}
		//тело запроса INSERT одиночной записи или UPDATE
		else {
			//v1.1.26 - исправление ошибки при удалении (нет массива $post)
			if (is_array($post)) {
				foreach ($post as $k => $v) {
					//если есть исключения
					if ($exceptions == false OR !in_array($k, $exceptions)) {
						//@since v1.1.10 обработка NULL
						if ($v === NULL) $sql[] = "`" . $k . "` = NULL";
						else $sql[] = "`" . $k . "` = '" . mysql_res($v) . "'";
					}
				}
				$sql = isset($sql) ? implode(', ', $sql) : '';
			}
		}


		$ignore = $ignore ? "IGNORE" : "";
		switch ($type) {
			//запрос на вставку новой строки
			case 'insert':
				$query = "
					INSERT " . $ignore . " INTO `" . $tbl_name . "`
					SET " . $sql . ";
				";
				break;
			//запрос на вставку новой строки с обновлением при совпадении unique ключа
			case 'insert update':
				$query = "
					INSERT " . $ignore . " INTO `" . $tbl_name . "`
					SET " . $sql . "
					ON DUPLICATE KEY UPDATE " . $sql . ";
				";
				break;
			//запрос на вставку множества строк
			case 'insert values':
				$query = "
					INSERT " . $ignore . " INTO `" . $tbl_name . "` (`" . $into . "`)
					VALUES (" . $sql . ")
				";
				break;
			//запрос на обновление одной или нескольких строк
			case 'update':
				if ($id = intval(@$post['id'])) $where .= " AND id = '" . $id . "' ";
				$query = "
					UPDATE `" . $tbl_name . "`
					SET " . $sql . "
					WHERE 1	" . $where;
				//нельзя делать обновление без условий
				if ($where=='') {
					trigger_error('error_update ' . $query, E_USER_DEPRECATED);
					$query = '';
				}
				break;
			//запрос на удаление одной или нескольких строк
			case 'delete':
				if (is_array($post)) $id = intval(@$post['id']);
				else $id = intval($post);
				if ($id) $where .= " AND id = '" . $id . "' ";
				$query = "
					DELETE
					FROM `" . $tbl_name . "`
					WHERE 1	" . $where;
				//нельзя удалять без условий
				if ($where=='') {
					trigger_error('error_delete ' . $query, E_USER_DEPRECATED);
					$query = '';
				}
				break;
			//любой запрос
			case 'query':
				$query = $tbl_name;
				break;
			//по умолчанию возвращаем тело запроса
			default:
				return $sql;
		}
		//если запрос есть
		if ($query) {
			//выполняем запрос

			//log_add('sql.txt',$query);
			$time = microtime(true);
			mysql_query($query); //echo $query;
			$time = microtime(true)-$time;
			$config['queries'][] = array($time,$query);

			if (($error = mysql_error()) == false) {
				switch ($type) {
					case 'insert':
					case 'insert update':
						return (mysql_affected_rows() > 0) ? mysql_insert_id() : false;
					case 'update':
					case 'delete':
					case 'insert values':
						return (($rows = mysql_affected_rows()) > 0) ? $rows : false;
					case 'query':
						if ($post=='affected_rows') return mysql_affected_rows();
				}
				return false;
			} else {
				trigger_error($error . ' ' . $query, E_USER_DEPRECATED);
			}
		}
	}
}

//выборка с БД - вариант Саши
function mysql_array($query, $type = 'rows',$cache = 0) {
	global $config;
	$file = ROOT_DIR.'cache/'.md5($query).'.php';
	if ($config['cache'] && $cache && file_exists($file) && (time() - $cache) < filemtime($file)) {
		$ret = file_get_contents($file);
		return json_decode($ret,true);

	} else {
		if (!defined('config_db')) require_once(ROOT_DIR.'config_db.php');
		$result = mysql_query($query);
		if ($error = mysql_error()) {
			trigger_error($error.' '.$query, E_USER_DEPRECATED);
			return false;
		}
		if ($result) {
			switch ($type) {
				//одно значение
				case 'value':
				case 'result':		if (mysql_num_rows($result)) $r = mysql_result($result,0); break;
				//массив одиночных значений
				case 'values':
				case 'results':		while ($q = mysql_fetch_row($result)) $r[] = $q[0]; break;
				//количество строк
				case 'num_rows':	$r = mysql_num_rows($result); break;
				//одна строка
				case 'row':			$r = mysql_fetch_assoc($result); break;
				//все строки
				case 'rows':		while ($q = mysql_fetch_assoc($result)) $r[] = $q; break;
				//все строки в массиве с ключами-id
				case 'id':			while ($q = mysql_fetch_assoc($result)) $r[$q['id']] = $q; break;
				//все значения поля name в массиве с ключами-id
				case 'names':		while ($q = mysql_fetch_assoc($result)) $r[$q['id']] = $q['name']; break;
				//пары ключ-значение
				case 'key_val':		while ($q = mysql_fetch_row($result)) $r[$q[0]] = $q[1]; break;
				//все строки в массиве с ключами из первого поля
				case 'first':		while ($q = mysql_fetch_assoc($result)) $r[current($q)] = $q; break;
				//все строки в массиве с ключами из первого поля
				case 'first_second':while ($q = mysql_fetch_assoc($result)) $r[current($q)][next($q)] = $q; break;
				//массивы из значений второго поля, сгруппированных по первому полю
				case 'arrays':		while ($q = mysql_fetch_row($result)) $r[$q[0]][] = $q[1]; break;
				//массивы из значений второго поля, сгруппированных по первому полю с ключами-значениями
				case 'arrays_num':	while ($q = mysql_fetch_row($result)) $r[$q[0]][$q[1]] = $q[1]; break;
				//массивы строк, сгруппированных по какому-либо полю
				default:	 		while ($q = mysql_fetch_assoc($result)) $r[$q[$type]][] = $q;
			}

		} else $r = false;
		if ($config['cache'] && $cache) {
			if (is_dir(ROOT_DIR.'cache') || mkdir(ROOT_DIR.'cache',0755,true)) {
				$f = fopen($file,'w');
				fwrite($f,json_encode(@$r));
				fclose($f);
			}
		}
		return @$r;
	}
}