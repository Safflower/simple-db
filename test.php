<?php
	error_reporting(E_ALL);

	require __DIR__.'/simple-db.php';

	$db = new Safflower\SimpleDB();
	$db->setBasePath(__DIR__.'/@database');
	
	$db->dropTable('users');

	$db->createTable('users', ['no', 'username', 'password']);

	$db->insertRow('users', ['no' => 1, 'username' => 'admin', 'password' => '1234']);
	$db->insertRow('users', ['no' => 2, 'username' => 'guest', 'password' => 'guest']);
	$db->insertRow('users', ['no' => 3, 'username' => 'root']);
	$db->insertRow('users', ['no' => 4, 'username' => '123', 'password' => '123']);
	$db->insertRow('users', ['no' => 5, 'username' => '홍길동', 'password' => false]);

	$rows = $db->selectRow('users', 'no', null);
	$no = $rows[count($rows) - 1]['no'];
	$db->insertRow('users', ['no' => ++$no, 'username' => 'john doe', 'password' => 'asd123']);
	$db->insertRow('users', ['no' => ++$no, 'username' => 'jane doe', 'password' => 'zxc456']);

	$db->deleteRow('users', function($row){
		if($row['username'] === 'root'){
			return true;
		}else{
			return false;
		}
	});

	$rows = $db->updateRow('users', ['password' => 'secret'], function($row){return true;
		if($row['no'] < 2){
			return true;
		}else{
			return false;
		}
	},4);

	$rows = $db->selectRow('users', null, function($row){
		if($row['no'] < 10){
			return true;
		}else{
			return false;
		}
	});

	var_dump($rows);

	$db->truncateTable('users');