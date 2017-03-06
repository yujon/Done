<?php
/** *******************************************************************
 * 数据库mysqli驱动类，通过该类使用PHP的mysqli扩展连接处理数据库。    *
 * *******************************************************************/
	namespace Done\Lib\Core;
	class Mysqli extends DB{
		static $mysqli = null;

		function __construct(){
			if(is_null(self::$mysqli)){	

				$mysqli=new \mysqli(HOST, USERNAME, PASSWORD, DBNAME);
				if (mysqli_connect_errno()) {
					Debug::addmsg("<font color='red'>连接失败: ".mysqli_connect_error().",请查看config.inc.php文件设置是否有误！</font>");
					return false;
				}else{
					self::$mysqli=$mysqli;
				}
				
			}			
			self::$mysqli->query('SET names utf8');

			//获取当前表名
			$arr = explode("\\",get_class($this));
			$this->tName = TABLEPREV.$arr[count($arr)-1];

			$this->getTables();

			$this->getFields();
			
		}

		/**
		 * 自动获取表结构
		 */
		function getFields(){
			if(in_array($this->tName, $this->tableList)){
				$cachefile = APP_DATA_PATH.$this->tName.".php";
		
				if(file_exists($cachefile)){
					$json=ltrim(file_get_contents($cachefile),"<?php ");
					$this->autoImcrement=substr($json,-3);
					$json=substr($json, 0, -3);
					$this->fieldList=(array)json_decode($json, true);	
				
				}else{
					if(self::$mysqli)
						$result=self::$mysqli->query("desc {$this->tName}");
					else
						return;
				
					$fields=array();
					$auto="yno";
					exit;
					while($row=$result->fetch_assoc()){
						if($row["Key"]=="PRI"){
							$fields["pri"]=strtolower($row["Field"]);
						}else{
							$fields[]=strtolower($row["Field"]);
						}
						if($row["Extra"]=="auto_increment")
							$auto="yes";
					}
					//如果表中没有主键，则将第一列当作主键
					if(!array_key_exists("pri", $fields)){
						$fields["pri"]=array_shift($fields);		
					}
					if(!APP_DEBUG && APP_FIELDS)
						file_put_contents($cachefile, "<?php ".json_encode($fields).$auto);
					$this->fieldList=$fields;
					$this->autoImcrement=$auto;
					
				}
				Debug::addmsg("表<b>{$this->tabName}</b>结构：".implode(",", $this->fieldList),2); //debug
			}
		}

		//获取数据库所有表
		function getTables(){		
			$table_sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES  WHERE TABLE_SCHEMA ='".DBNAME."'";
			$table_res = self::$mysqli->query($table_sql);
			$row= $table_res->fetch_assoc();
			if($table_res){
				while($row= $table_res->fetch_assoc()){
					$this->tableList[] = $row["TABLE_NAME"];
				}
			}
		}

    	/**
		* 事务开始
    	*/
		public function beginTransaction() {
			self::$mysqli->autocommit(false);
			
		}
		
		/**
     		* 事务提交
     		*/
		public function commit() {
 			self::$mysqli->commit();
        	self::$mysqli->autocommit(true);

		}
		
		/**
     		* 事务回滚
     		*/
		public function rollBack() {
  			self::$mysqli->rollback();
        	self::$mysqli->autocommit(true);

		}
		/*
		 * 获取数据库使用大小
		 * @return	string		返回转换后单位的尺寸
		 */
		public function dbSize() {
			$sql = "SHOW TABLE STATUS FROM " . DBNAME;
			if(defined("TABLEPREV")) {
				$sql .= " LIKE '".TABLEPREV."%'";
			}
			$result=self::$mysqli->query($sql);
			$size = 0;
			while($row=$result->fetch_assoc())
				$size += $row["Data_length"] + $row["Index_length"];
			return toSize($size);
		}
		/*
		 * 数据库的版本
		 * @return	string		返回数据库系统的版本
		 */
		function dbVersion() {
			return self::$mysqli->server_info;
		}

		//执行sql语句
		function query($sql, $method){
			$startTime = microtime(true); 
			$this->setNull();  //初使化SQL

			$marr=explode("::", $method);
			$method=strtolower(array_pop($marr));

			$addCache = false;

			if(defined(APP_MEMCACHE)){
				global $mem;
				if($method == "select" || $method == "find" || $method=="total"){
					$data=$mem->getCache($sql);
					if($data){
						return $data;  //直接从memserver中取，不再向下执行
					}else{
						$addcache=true;	
					}
				 }
			}

			$stmt = self::$mysqli->query($sql);

			 //如果SQL有误，则输出并直接返回退出
			 if(!$result){
				 Debug::addmsg("<font color='red'>SQL ERROR: [{self::$mysqli->errno}] {$stmt->error}</font>");
				 Debug::addmsg("请查看：<font color='#005500'>".$sql.'</font>'); //debug
				 return;
			 }	

			//如果使用mem，并且不是查找语句
			if(defined(APP_MEMCACHE) && !addCache){
				if($stmt->affected_rows>0){
					$method->delCache($this->tName);	 //清除缓存
					Debug::addmsg("清除表<b>{$this->tabName}</b>在Memcache中所有缓存!"); //debug
				}
			}

			 switch($method){
				 case "select":  //查所有满足条件的
				 	$stmt->store_result(); 
					$data=$this->getAll($stmt);

					if($addcache){
					 	$mem->addCache($this->tName, $sql, $data);
					}
					$return=$data;
					break;
				case "find":    //只要一条记录的
					$stmt->store_result(); 
					if($stmt->num_rows > 0) {
						$data = $this->getOne($stmt);

						if($addcache){
					 		$mem->addCache($this->tName, $sql, $data);
						}
						$returnv=$data;
					}else{
						$returnv=false;
					}
					break;

				case "total":  //返回总记录数
					$stmt->store_result(); 
					$row=$this->getOne($stmt);

					if($addcache){
					 	$mem->addCache($this->tName, $sql, $row["count"]);
					 }
					$returnv=$row["count"];
					break;
				case "insert":  //插入数据 返回最后插入的ID
					if($this->autoIncrement=="yes")
						$return=self::$mysqli->insert_id;
					else
						$return=$result;
					break;
				case "delete":
					$result = $stmt;
					break;
				case "update":        //update 
					$return=$stmt->affected_rows;
					break;
				default:
					$return=$result;
			}

			$stopTime= microtime(true);
			$ys=round(($stopTime - $startTime) , 4);
			Debug::addmsg('[用时<font color="red">'.$ys.'</font>秒] - '.$memkey,2); //debug
			return $return;

		}

		/**
		 * 获取多所有记录
		 */
		private function getAll($stmt) {
			$result = array();
			$field = $stmt->result_metadata()->fetch_fields();
			$out = array();
			//获取所有结果集中的字段名
			$fields = array();
			foreach ($field as $val) {
				$fields[] = &$out[$val->name];
			}
			//用所有字段名绑定到bind_result方上
			call_user_func_array(array($stmt,'bind_result'), $fields);
		       	while ($stmt->fetch()) {
				$t = array();  //一条记录关联数组
				foreach ($out as $key => $val) {
					$t[$key] = $val;
				}
				$result[] = $t;
			}
			return $result;  //二维数组
		}

		/**
		 * 获取一条记录
		 */
		private function getOne($stmt) {
			$result = array();
			$field = $stmt->result_metadata()->fetch_fields();
			$out = array();
			//获取所有结果集中的字段名
			$fields = array();
			foreach ($field as $val) {
				$fields[] = &$out[$val->name];
			}
			//用所有字段名绑定到bind_result方上
			call_user_func_array(array($stmt,'bind_result'), $fields);
		        $stmt->fetch();
			
			foreach ($out as $key => $val) {
				$result[$key] = $val;
			}
			return $result;  //一维关联数组
	    }

		//防止对象被复制
		private function __clone(){
			trigger_error('Clone is not allowed !');
		}

	}

