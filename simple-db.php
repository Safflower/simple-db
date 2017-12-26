<?php
	namespace Safflower;

	class SimpleDB{

		### Database path
		private $basePath = '';

		### Naming rule regular expression of table
		private $tableNamingRule = '/^[a-zA-Z0-9\!@#\$%\^&\-_\+\=\. ]+$/';

		### Naming rule regular expression of column
		private $columnNamingRule = '/^[a-zA-Z0-9\!@#\$%\^&\-_\+\=\. ]+$/';

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
		public function setBasePath($path){
			if(false === ($realPath = realpath($path))){
				trigger_error('데이터베이스의 경로가 존재하지 않습니다.', E_USER_WARNING);
				return false;
			}

			$this->basePath = $realPath;
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
				trigger_error('데이터베이스의 경로가 설정되지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !preg_match($this->tableNamingRule, $tableName)){
				trigger_error('테이블의 이름이 유효하지 않습니다.', E_USER_WARNING);
				return false;
			}

			if($columnName !== null){
				if(!is_array($columnName)){
					$columnName = [$columnName];
				}

				foreach($columnName as $name){
					if(!is_string($name) || !preg_match($this->columnNamingRule, $name)){
						trigger_error('열의 이름이 유효하지 않습니다.', E_USER_WARNING);
						return false;
					}
				}
			}

			$tablePath = $this->getTablePath($tableName);

			if(isset($tablePath{259})){
				trigger_error('테이블의 경로가 너무 깁니다.', E_USER_WARNING);
				return false;
			}

			if(is_file($tablePath)){
				trigger_error('테이블이 이미 존재합니다.', E_USER_WARNING);
				return false;
			}

			if(!touch($tablePath)){
				trigger_error('테이블 파일의 생성을 실패했습니다.', E_USER_WARNING);
				return false;
			}

			if($columnName !== null){
				if(false === ($fp = fopen($tablePath, 'w'))){
					trigger_error('테이블 파일을 쓰는데 실패했습니다.', E_USER_WARNING);
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
				trigger_error('데이터베이스의 경로가 설정되지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !preg_match($this->tableNamingRule, $tableName)){
				trigger_error('테이블의 이름이 유효하지 않습니다.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('테이블이 존재하지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(!unlink($tablePath)){
				trigger_error('테이블 파일을 삭제하는데 실패했습니다.', E_USER_WARNING);
				return false;
			}

			return true;
		}

		### Truncate the Table
		### e.g. $db->truncateTable('users');
		public function truncateTable($tableName){
			if(!$this->checkBasePath()){
				trigger_error('데이터베이스의 경로가 설정되지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !preg_match($this->tableNamingRule, $tableName)){
				trigger_error('테이블의 이름이 유효하지 않습니다.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('테이블이 존재하지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('테이블 파일을 읽는데 실패했습니다.', E_USER_WARNING);
				return false;
			}
			$column = self::fgets($fp);
			fclose($fp);

			if(false === ($fp = fopen($tablePath, 'w'))){
				trigger_error('테이블 파일을 쓰는데 실패했습니다.', E_USER_WARNING);
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
				trigger_error('데이터베이스의 경로가 설정되지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !preg_match($this->tableNamingRule, $tableName)){
				trigger_error('테이블의 이름이 유효하지 않습니다.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('테이블이 존재하지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('테이블 파일을 읽는데 실패했습니다.', E_USER_WARNING);
				return false;
			}
			$column = self::fgets($fp);
			fclose($fp);

			if(!is_array($rowInfo)){
				trigger_error('행의 값이 유효하지 않습니다.', E_USER_WARNING);
				return false;
			}

			foreach($rowInfo as $key => $value){
				if(!is_string($key) || !preg_match($this->columnNamingRule, $key) || !in_array($key, $column, true)){
					trigger_error('열의 이름이 유효하지 않습니다.', E_USER_WARNING);
					return false;
				}
			}

			$columnBuffer = [];
			$count = 0;
			foreach($column as $name){
				$columnBuffer[$count++] = isset($rowInfo[$name]) ? $rowInfo[$name] : '';
			}

			if(false === ($fp = fopen($tablePath, 'a'))){
				trigger_error('테이블 파일을 쓰는데 실패했습니다.', E_USER_WARNING);
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
		public function selectRow($tableName, $columnName = null, $filterCallback = null, $limitCount = -1){
			if(!$this->checkBasePath()){
				trigger_error('데이터베이스의 경로가 설정되지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !preg_match($this->tableNamingRule, $tableName)){
				trigger_error('테이블의 이름이 유효하지 않습니다.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('테이블이 존재하지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('테이블 파일을 읽는데 실패했습니다.', E_USER_WARNING);
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
				if(!is_array($columnName)){
					$columnName = [$columnName];
				}

				foreach($columnName as $name){
					if(!is_string($name) || !preg_match($this->columnNamingRule, $name) || !in_array($name, $column, true)){
						trigger_error('열의 이름이 유효하지 않습니다.', E_USER_WARNING);
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

				if($filterCallback !== null && is_callable($filterCallback) && $filterCallback($buffer) !== true){
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
		public function deleteRow($tableName, $filterCallback = null, $limitCount = -1){
			if(!$this->checkBasePath()){
				trigger_error('데이터베이스의 경로가 설정되지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !preg_match($this->tableNamingRule, $tableName)){
				trigger_error('테이블의 이름이 유효하지 않습니다.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('테이블이 존재하지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('테이블 파일을 읽는데 실패했습니다.', E_USER_WARNING);
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
				trigger_error('테이블 파일을 쓰는데 실패했습니다.', E_USER_WARNING);
				return false;
			}
			self::fputs($fp, $column);
			foreach($rows as $row){
				$buffer = [];
				$count = -1;
				foreach($column as $name){
					$buffer[$name] = $row[++$count];
				}

				if($filterCallback === null || is_callable($filterCallback) && $filterCallback($buffer) === true){
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
		public function updateRow($tableName, array $rowInfo, $filterCallback = null, $limitCount = -1){
			if(!$this->checkBasePath()){
				trigger_error('데이터베이스의 경로가 설정되지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(!is_string($tableName) || !preg_match($this->tableNamingRule, $tableName)){
				trigger_error('테이블의 이름이 유효하지 않습니다.', E_USER_WARNING);
				return false;
			}

			$tablePath = $this->getTablePath($tableName);

			if(!is_file($tablePath)){
				trigger_error('테이블이 존재하지 않습니다.', E_USER_WARNING);
				return false;
			}

			if(false === ($fp = fopen($tablePath, 'r'))){
				trigger_error('테이블 파일을 읽는데 실패했습니다.', E_USER_WARNING);
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
				trigger_error('행의 값이 유효하지 않습니다.', E_USER_WARNING);
				return false;
			}

			foreach($rowInfo as $key => $value){
				if(!is_string($key) || !preg_match($this->columnNamingRule, $key) || !in_array($key, $column, true)){
					trigger_error('열의 이름이 유효하지 않습니다.', E_USER_WARNING);
					return false;
				}
			}

			if(false === ($fp = fopen($tablePath, 'w'))){
				trigger_error('테이블 파일을 쓰는데 실패했습니다.', E_USER_WARNING);
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

				if($filterCallback === null || is_callable($filterCallback) && $filterCallback($buffer) === true){
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
		### e.g. $db->getTablePath('users');
		private function getTablePath($tableName){
			return $this->basePath.DIRECTORY_SEPARATOR.$tableName;
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
