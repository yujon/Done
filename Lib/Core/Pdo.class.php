<?php
//数据库连接操作类Pdo
namespace Done\Lib\Core;

class Pdo extends DB { 
	static $pdo=null;
	
	function __construct(){
		
		if(is_null(self::$pdo)){
			$dsn=DBTYPE.":host=".HOST.";dbname=".DBNAME;
			try{
				$pdo = new \PDO($dsn,USERNAME,PASSWORD,array(\PDO::ATTR_PERSISTENT=>true));
				self::$pdo=$pdo;
			}catch(PDOException $e){
				echo "数据库连接失败".$e->getMessage();
			}
		}			
		self::$pdo->query('SET names utf8');
		self::$pdo->setAttribute(\PDO::ATTR_CASE,\PDO::CASE_LOWER);
		//获取当前表名
		$arr = explode("\\",get_class($this));
		$tModel = $arr[count($arr)-1];
		$this->tName = TABLEPREV.strtolower(preg_replace("/(.+?)Model/i",'\1', $tModel,1));

		$this->getTables();
		$this->getFields();
	}

	//获取表字段
	function getFields(){	
		if(in_array($this->tName, $this->tableList)){
			$cachefile = APP_DATA_PATH.$this->tName.".php";
				
			if(!file_exists($cachefile)){
				try{
					$stmt=self::$pdo->prepare("desc {$this->tName}");
					$stmt->execute();
					$auto="yno";
					$fields=array();
					while($row=$stmt->fetch(\PDO::FETCH_ASSOC)){
						if($row["key"]=="PRI"){
							$fields["pri"]=strtolower($row["field"]);
						}else{
							$fields[]=strtolower($row["field"]);
						}
						if($row["extra"]=="auto_increment")
							$auto="yes";
					}
					//如果表中没有主键，则将第一列当作主键
					if(!array_key_exists("pri", $fields)){
						$fields["pri"]=array_shift($fields);		
					}
					if(!APP_DEBUG && APP_FIELDS)
						file_put_contents($cachefile, "<?php ".json_encode($fields).$auto);
					$this->fieldList=$fields;
					$this->autoIncrement=$auto;	
				}catch(PDOException $e){
					Debug::addmsg("<font color='red'>异常：".$e->getMessage().'</font>');
				}
			}else{
				$json=ltrim(file_get_contents($cachefile),"<?php ");
				$auto=substr($json,-3);
				$json=substr($json, 0, -3);
				$this->fieldList=(array)json_decode($json, true);
				$this->autoIncrement=$auto;	
			}
			Debug::addmsg("表<b>{$this->tName}</b>结构：".implode(",", $this->fieldList),2); //debug
		}
	}

	//获取数据库所有表
	function getTables(){		
		$table_sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES  WHERE TABLE_SCHEMA ='".DBNAME."'";
		$table_res = self::$pdo->query($table_sql);
		if($table_res){
			$data= $table_res->fetchAll(\PDO::FETCH_ASSOC);
			for($i=0;$i<count($data);$i++){
				$this->tableList[] = $data[$i]["table_name"];
			}
		}
	}

	/**
		* 捕获PDO错误信息
		*/
	private function getPDOError()
	{
		if (self::$pdo->errorCode() != '00000')
		{
			$error = self::$pdo->errorInfo();
			if(strstr($error[2], 'Duplicate entry')){
				$err_info  ='总登记号重复';
			}else{
				$err_info = $error[2];
			}
		}
		return $err_info;
	}

	/**
	* 事务开始
	*/
	public function beginTransaction() {
		self::$pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0); 
		self::$pdo->beginTransaction();
	}
	
	/**
 	* 事务提交
 	*/
	public function commit() {
		self::$pdo->commit();
		self::$pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1); 
	}
	
	/**
 		* 事务回滚
 	*/
	public function rollBack() {
		self::$pdo->rollBack();
		self::$pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1); 
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
		$stmt=self::$pdo->prepare($sql);  //准备好一个语句
	        $stmt->execute();   //执行一个准备好的语句
		$size = 0;
		while($row=$stmt->fetch(\PDO::FETCH_ASSOC))
			$size += $row["Data_length"] + $row["Index_length"];
		return toSize($size);
	}
	/*
	 * 数据库的版本
	 * @return	string		返回数据库系统的版本
	 */
	public function dbVersion() {
		return self::$pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
	}

	//执行sql语句
	function query($sql, $method){
		$startTime = microtime(true); 
		$this->setNull();  //初使化SQL

		$marr=explode("::", $method);
		$method=strtolower(array_pop($marr));

		$addcache = false;

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

		$stmt = self::$pdo->query($sql);

		//如果使用mem，并且不是查找语句
		if(defined(APP_MEMCACHE) && !addCache){
			if($stmt->rowCount()>0){
				$method->delCache($this->tName);	 //清除缓存
				Debug::addmsg("清除表<b>{$this->tName}</b>在Memcache中所有缓存!"); //debug
			}
		}

		 switch($method){
			 case "select":  //查所有满足条件的
				 $data=$stmt->fetchAll(\PDO::FETCH_ASSOC);

				 if($addcache){
				 	$mem->addCache($this->tName, $sql, $data);
				 }
				 $return=$data;
				break;
			case "find":    //只要一条记录的
				$data=$stmt->fetch(\PDO::FETCH_ASSOC);

				 if($addcache){
				 	$mem->addCache($this->tName, $sql, $data);
				 }
				 $return=$data;
				break;

			case "total":  //返回总记录数
				$row=$stmt->fetch(\PDO::FETCH_NUM);

				 if($addcache){
				 	$mem->addCache($this->tName, $sql, $row[0]);
				 }
			
				$return=$row[0];
				break;
			case "insert":  //插入数据 返回最后插入的ID
				if($this->autoIncrement=="yes"){
					$return=self::$pdo->lastInsertId();
				}else{
					$return=$stmt;
				}
				break;
			case "delete":
				$return = $stmt;
				break;
			case "update":        //update 
				$return=$stmt->rowCount();
				break;
			default:
				$return=$result;
		}

		$stopTime= microtime(true);
		$ys=round(($stopTime - $startTime) , 4);
		Debug::addmsg('[用时<font color="red">'.$ys.'</font>秒] - '.$sql,2); //debug
		return $return;
	}

	//防止对象被复制
	private function __clone(){
		trigger_error('Clone is not allowed !');
	}
}