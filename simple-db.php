<?php
	namespace Safflower;

	class SimpleDB{

		### Database path
		private $basePath = null;

		### Naming rule regular expression of table
		private static $tableNamingRule = '/^[a-zA-Z0-9\!@#\$%\^&\-_\+\=\. ]+$/';

		### Naming rule regular expression of column
		private static $columnNamingRule = '/^[a-zA-Z0-9\!@#\$%\^&\-_\+\=\. ]+$/';

		### Make Instance Variable
		### e.g. $db = new Safflower\SimpleDB();
		### e.g. $db = new Safflower\SimpleDB(__DIR__.'/@database');
		public function __construct($basePath = null){
			if($basePath !== null){
				$this->setBasePath($basePath);
			}
		}

		### Set Database Path
		### e.g. $db->setBasePath(__DIR__.'/@database');
		public function setBasePath($basePath){
			if(false === ($absPath = realpath($basePath))){
				trigger_error('Database path is invalid.', E_USER_WARNING);
				return false;
			}

			$this->basePath = $absPath;
			return true;
		}

		### Check the Database Path
		### e.g. $isExists = $db->checkBasePath();
		public function checkBasePath(){
			return isset($this->basePath{0}) && is_dir($this->basePath);
		}

		### Create a Table
		### e.g. $db->createTable('users');
		### e.g. $db->createTable('users', 'no');
		### e.g. $db->createTable('users', ['no', 'username', 'password']);
		public function createTable($tableName, $columnName = null){
			if(!$this->checkBasePath()){
				trigger_error('Database path is not set.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !self::isValidTableName($tableName)){
				trigger_error('Table name is invalid.', E_USER_WARNING);
				return false;
			}

			if($columnName !== null){
				if(is_string($columnName)){
					$columnName = [$columnName];
				}else if(!is_array($columnName)){
					trigger_error('Column name is invalid.', E_USER_WARNING);
					return false;
				}

				foreach($columnName as $name){
					if(!is_string($name) || !self::isValidColumnName($name)){
						trigger_error('Column name is invalid.', E_USER_WARNING);
						return false;
					}
				}
			}

			$tablePath = $this->getTablePath($tableName);

			if(isset($tablePath{259})){
				trigger_error('Table name is too long.', E_USER_WARNING);
				return false;
			}

			if(is_file($tablePath)){
				trigger_error('Table already exists.', E_USER_WARNING);
				return false;
			}

			if(!touch($tablePath)){
				trigger_error('Failed to create table file.', E_USER_WARNING);
				return false;
			}

			if($columnName !== null){
				if(false === ($fp = fopen($tablePath, 'w'))){
					trigger_error('Failed to write table file.', E_USER_WARNING);
					return false;
				}
				self::fputs($fp, $columnName);
				fclose($fp);
			}

			return true;
		}

		### Drop the Table
		### e.g. $db->dropTable('users');
		public function dropTable($tableName){
			if(!$this->checkBasePath()){
				trigger_error('Database path is not set.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !self::isValidTableName($tableName)){
				trigger_error('Table name is invalid.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('Table is not exists.', E_USER_WARNING);
				return false;
			}

			if(!unlink($tablePath)){
				trigger_error('Failed to delete table file.', E_USER_WARNING);
				return false;
			}

			return true;
		}

		### Truncate the Table
		### e.g. $db->truncateTable('users');
		public function truncateTable($tableName){
			if(!$this->checkBasePath()){
				trigger_error('Database path is not set.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !self::isValidTableName($tableName)){
				trigger_error('Table name is invalid.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('Table is not exists.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('Failed to read table file.', E_USER_WARNING);
				return false;
			}
			$column = self::fgets($fp);
			fclose($fp);

			if(false === ($fp = fopen($tablePath, 'w'))){
				trigger_error('Failed to write table file.', E_USER_WARNING);
				return false;
			}
			self::fputs($fp, $column);
			fclose($fp);

			return true;
		}

		### Insert a Row
		### e.g. $db->insertRow('users', ['no' => '1', 'username' => 'admin', 'password' => '12345']);
		public function insertRow($tableName, $rowInfo){
			if(!$this->checkBasePath()){
				trigger_error('Database path is not set.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !self::isValidTableName($tableName)){
				trigger_error('Table name is invalid.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('Table is not exists.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('Failed to read table file.', E_USER_WARNING);
				return false;
			}
			$column = self::fgets($fp);
			fclose($fp);

			if(!is_array($rowInfo)){
				trigger_error('Row value is invalid.', E_USER_WARNING);
				return false;
			}

			foreach($rowInfo as $key => $value){
				if(!is_string($key) || !self::isValidColumnName($key) || !in_array($key, $column, true)){
					trigger_error('Column name is invalid.', E_USER_WARNING);
					return false;
				}
			}

			$columnBuffer = [];
			$count = 0;
			foreach($column as $name){
				$columnBuffer[$count++] = isset($rowInfo[$name]) ? $rowInfo[$name] : '';
			}

			if(false === ($fp = fopen($tablePath, 'a'))){
				trigger_error('Failed to write table file.', E_USER_WARNING);
				return false;
			}
			self::fputs($fp, $columnBuffer);
			fclose($fp);

			return true;
		}

		### Select Rows
		### e.g. $db->selectRow('users');
		### e.g. $db->selectRow('users', 'username');
		### e.g. $db->selectRow('users', ['no', 'username', 'password']);
		### e.g. $db->selectRow('users', ['no', 'username', 'password'], 'callback');
		### e.g. $db->selectRow('users', ['no', 'username', 'password'], 'callback', 1);
		public function selectRow($tableName, $columnName = null, $filterCallback = null, $limitCount = -1){
			if(!$this->checkBasePath()){
				trigger_error('Database path is not set.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !self::isValidTableName($tableName)){
				trigger_error('Table name is invalid.', E_USER_WARNING);
				return false;
			}

			if($filterCallback !== null && !is_callable($filterCallback)){
				trigger_error('Filter callback function is not callable.', E_USER_WARNING);
				return false;
			}

			if(!is_int($limitCount)){
				trigger_error('Limit count is invalid.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('Table is not exists.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('Failed to read table file.', E_USER_WARNING);
				return false;
			}
			$column = self::fgets($fp);
			while(false !== ($row = self::fgets($fp))){
				if($row !== null){
					$rows[] = $row;
				}
			}
			fclose($fp);

			if($columnName !== null){
				if(is_string($columnName)){
					$columnName = [$columnName];
				}else if(!is_array($columnName)){
					trigger_error('Column name is invalid.', E_USER_WARNING);
					return false;
				}

				foreach($columnName as $name){
					if(!is_string($name) || !self::isValidColumnName($name) || !in_array($name, $column, true)){
						trigger_error('Column name is invalid.', E_USER_WARNING);
						return false;
					}
				}
			}

			$rowBuffer = [];
			foreach($rows as $row){
				$buffer = [];
				$count = -1;
				foreach($column as $name){
					++$count;
					if($columnName !== null && !in_array($name, $columnName, true)){
						continue;
					}
					$buffer[$name] = $row[$count];
				}

				if($filterCallback !== null && $filterCallback($buffer) !== true){
					continue;
				}

				$rowBuffer[] = $buffer;

				if($limitCount >= 0 && --$limitCount < 1){
					break;
				}
			}
			return $rowBuffer;
		}

		### Delete Rows
		### e.g. $db->deleteRow('users');
		### e.g. $db->deleteRow('users', 'callback');
		### e.g. $db->deleteRow('users', 'callback', 1);
		public function deleteRow($tableName, $filterCallback = null, $limitCount = -1){
			if(!$this->checkBasePath()){
				trigger_error('Database path is not set.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !self::isValidTableName($tableName)){
				trigger_error('Table name is invalid.', E_USER_WARNING);
				return false;
			}

			if($filterCallback !== null && !is_callable($filterCallback)){
				trigger_error('Filter callback function is not callable.', E_USER_WARNING);
				return false;
			}

			if(!is_int($limitCount)){
				trigger_error('Limit count is invalid.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('Table is not exists.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('Failed to read table file.', E_USER_WARNING);
				return false;
			}
			$column = self::fgets($fp);
			while(false !== ($row = self::fgets($fp))){
				if($row !== null){
					$rows[] = $row;
				}
			}
			fclose($fp);

			if(false === ($fp = fopen($tablePath, 'w'))){
				trigger_error('Failed to write table file.', E_USER_WARNING);
				return false;
			}
			self::fputs($fp, $column);
			foreach($rows as $row){
				$buffer = [];
				$count = -1;
				foreach($column as $name){
					$buffer[$name] = $row[++$count];
				}

				if($filterCallback === null || $filterCallback($buffer) === true){
					continue;
				}

				self::fputs($fp, $row);

				if($limitCount >= 0 && --$limitCount < 1){
					break;
				}
			}
			fclose($fp);

			return true;
		}

		### Update Rows
		### e.g. $db->updateRow('users', ['password' => '12345']);
		### e.g. $db->updateRow('users', ['password' => '12345'], 'callback');
		### e.g. $db->updateRow('users', ['password' => '12345'], 'callback', 1);
		public function updateRow($tableName, $rowInfo, $filterCallback = null, $limitCount = -1){
			if(!$this->checkBasePath()){
				trigger_error('Database path is not set.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !self::isValidTableName($tableName)){
				trigger_error('Table name is invalid.', E_USER_WARNING);
				return false;
			}

			if($filterCallback !== null && !is_callable($filterCallback)){
				trigger_error('Filter callback function is not callable.', E_USER_WARNING);
				return false;
			}

			if(!is_int($limitCount)){
				trigger_error('Limit count is invalid.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('Table is not exists.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('Failed to read table file.', E_USER_WARNING);
				return false;
			}
			$column = self::fgets($fp);
			while(false !== ($row = self::fgets($fp))){
				if($row !== null){
					$rows[] = $row;
				}
			}
			fclose($fp);

			if(!is_array($rowInfo)){
				trigger_error('Row value is invalid.', E_USER_WARNING);
				return false;
			}

			foreach($rowInfo as $key => $value){
				if(!is_string($key) || !self::isValidColumnName($key) || !in_array($key, $column, true)){
					trigger_error('Column name is invalid.', E_USER_WARNING);
					return false;
				}
			}

			if(false === ($fp = fopen($tablePath, 'w'))){
				trigger_error('Failed to write table file.', E_USER_WARNING);
				return false;
			}
			self::fputs($fp, $column);
			$count2 = 0;
			foreach($rows as $row){
				$buffer = [];
				$count = -1;
				foreach($column as $name){
					$buffer[$name] = $row[++$count];
				}

				if($filterCallback === null || $filterCallback($buffer) === true){
					if($limitCount < 0 || ++$count2 <= $limitCount){
						foreach($rowInfo as $key => $value){
							$buffer[$key] = $value;
						}
						$row = array_values($buffer);
					}
				}

				self::fputs($fp, $row);
			}
			fclose($fp);

			return true;
		}

		### Get the Path of Table
		### e.g. $this->getTablePath('users');
		private function getTablePath($tableName){
			return $this->basePath.DIRECTORY_SEPARATOR.$tableName;
		}

		### Check the Table Name if Valid
		### e.g. self::isValidTableName('users');
		private static function isValidTableName($tableName){
			return preg_match(self::$tableNamingRule, $tableName);
		}

		### Check the Column Name if Valid
		### e.g. self::isValidColumnName('username');
		private static function isValidColumnName($columnName){
			return preg_match(self::$columnNamingRule, $columnName);
		}

		### Write the Row in Table
		### e.g. self::fputs($fp, $contents);
		private static function fputs($fp, $contents){
			return fputs($fp, json_encode($contents, JSON_UNESCAPED_UNICODE)."\n");
		}

		### Read the Row in Table
		### e.g. self::fgets($fp);
		private static function fgets($fp){
			if(false === ($contents = fgets($fp))){
				return false;
			}
			return json_decode($contents);
		}
	}
