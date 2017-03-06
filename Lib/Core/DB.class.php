<?php
//所有模型类的基类，封装了一些常用的数据库操作函数
	namespace Done\Lib\Core;

	abstract class DB{
		
		protected $tName;  //表名
		protected $autoIncrement; //主键自增
		protected $fieldList;  //字段列表
		protected $tableList;  //表列表
		protected $sql=array("field"=>"","where"=>"","innerjoin"=>"","leftjoin"=>"","rightjoin"=>"","fulljoin"=>"","on"=>"","order"=>"", "limit"=>"", "group"=>"","having"=>"");
		
		//重置属性
		function setNull(){
			$sql = array("field"=>"","where"=>"","innerjoin"=>"","leftjoin"=>"","rightjoin"=>"","fulljoin"=>"","on"=>"","order"=>"", "limit"=>"", "group"=>"", "having"=>"");
			$this->sql = $sql;
		}


		//filter = 1 去除 " ' 和 HTML 实体， 0则不变
		private function check($array, $filter){
			$arr=array();		
			foreach($array as $key=>$value){
				$key=strtolower($key);
				if(in_array($key, $this->fieldList) && $value !== ''){
					if(is_array($filter) && !empty($filter)){
						if(in_array($key, $filter)){
							$arr[$key]=$value;
						}else{
							$arr[$key]=stripslashes(htmlspecialchars($value));
						}
					}else if(!$filter){
						$arr[$key]=$value;
					}else{
						$arr[$key]=stripslashes(htmlspecialchars($value));
					}
				}
			}
			return $arr;
		}

		protected function escape_string_array($array){
			if(empty($array))
				return array();
		 	$value=array();
			 foreach($array as $val){
				 $value[]=str_replace(array('"', "'"), '', $val);
			 }
		 	 return $value;
		 }

		/**
		 *连贯操作调用field() where() order() limit() group() having()等方法，组合SQL语句
		 */
		function __call($methodName, $args){
			$methodName=strtolower($methodName);
			if(array_key_exists($methodName, $this->sql)){
				if(empty($args[0]) || (is_string($args[0]) && trim($args[0])==='')){
					$this->sql[$methodName]="";
				}else{
					$this->sql[$methodName]=$args;
				}

				if($methodName=="limit"){
					if($args[0]=="0")
						$this->sql[$methodName]=$args;
				}	
			}else{
				Debug::addmsg("<font color='red'>调用类".get_class($this)."中的方法{$methodName}()不存在!</font>");
			}
			return $this;
		}

		//设置查询的字段
		//field(array(a,b,c)) 
		//field("a b c")//field("a,b,c")
		//field(a,b,c)
		private function comField(){
			if($this->sql['field']!==""){
				if(is_array($this->sql['field'][0])){ //field(array(a,b,c)) 
					$arr = $this->sql['field'][0];
				}
				elseif(count($this->sql['field'])==1 && is_string($this->sql['field'][0])){  //field("a b c")//field("a,b,c")
					$arr = preg_replace("/(\s)+/", ",", $this->sql['field'][0]);
					$arr = explode(",", $arr);
				}else{ //field(a,b,c)
					$arr = $this->sql['field'];
				}
				//剔除不存在的字段
				for($i=0;$i<count($arr);$i++){
					if(!in_array($this->sql['field'][$i], $this->fieldList)){
						unset($arr[$i]);
					}
				}
				$this->sql['field'] = " ".implode(",", $arr)." ";
			}else{
				$this->sql['field'] = " * ";
			}
		}

		
		//设置内连接查询
		//innerjoin(array(a,b,c)) 
		//innerjoin("a b c")//innerjoin("a,b,c")
		//innerjoin(a,b,c)
		private function comInnerJoin(){
			if($this->sql['innerjoin']!==""){
				if(is_array($this->sql['innerjoin'][0])){ //innerjoin(array(a,b,c)) 
					$arr = $this->sql['innerjoin'][0];
				}elseif(count($this->sql['innerjoin'])==1 && is_string($this->sql['innerjoin'][0])){  //innerjoin("a b c")//innerjoin("a,b,c")
					$arr = preg_replace("/(\s)+/", ",", $this->sql['innerjoin'][0]);
					$arr = explode(",", $arr);
				}else{ //innerjoin(a,b,c)
					$arr = $this->sql['innerjoin'];
				}
				//剔除不存在的表
				for($i=0;$i<count($arr);$i++){
					if(!in_array($arr[$i], $this->tableList)){
						unset($arr[$i]);
					}
				}
				$this->sql['innerjoin'] = " inner join ".implode(" inner join ", $arr);
			}
		}

		//设置左外连接查询
		//leftjoin(array(a,b,c)) 
		//leftjoin("a b c")//leftjoin("a,b,c")
		//leftjoin(a,b,c)
		private function comLeftJoin(){
			if($this->sql['leftjoin']!==""){
				if(is_array($this->sql['leftjoin'][0])){ //leftjoin(array(a,b,c)) 
					$arr = $this->sql['leftjoin'][0];
				}elseif(count($this->sql['leftjoin'])==1 && is_string($this->sql['leftjoin'][0])){  //leftjoin("a b c")//leftjoin("a,b,c")
					$arr = preg_replace("/(\s)+/", ",", $this->sql['leftjoin'][0]);
					$arr = explode(",", $arr);
				}else{ //leftjoin(a,b,c)
					$arr = $this->sql['leftjoin'];
				}
				//剔除不存在的表
				for($i=0;$i<count($arr);$i++){
					if(!in_array($arr[$i], $this->tableList)){
						unset($arr[$i]);
					}
				}
				$this->sql['leftjoin'] = " left outer join ".implode(" left outer join ", $arr);
			}
		}

		//设置全外连接查询
		//rightjoin(array(a,b,c)) 
		//rightjoin("a b c")//rightjoin("a,b,c")
		//rightjoin(a,b,c)
		private function comRightJoin(){
			if($this->sql['rightjoin']!==""){
				if(is_array($this->sql['rightjoin'][0])){ //rightjoin(array(a,b,c)) 
					$arr = $this->sql['rightjoin'][0];
				}elseif(count($this->sql['rightjoin'])==1 && is_string($this->sql['rightjoin'][0])){  //rightjoin("a b c")//rightjoin("a,b,c")
					$arr = preg_replace("/(\s)+/", ",", $this->sql['rightjoin'][0]);
					$arr = explode(",", $arr);
				}else{ //rightjoin(a,b,c)
					$arr = $this->sql['rightjoin'];
				}
				//剔除不存在的表
				for($i=0;$i<count($arr);$i++){
					if(!in_array($arr[$i], $this->tableList)){
						unset($arr[$i]);
					}
				}
				$this->sql['rightjoin'] = " right outer join ".implode(" right outer join ", $arr);
			}
		}

		//设置全外连接查询
		//fulljoin(array(a,b,c)) 
		//fulljoin("a b c")//fulljoin("a,b,c")
		//fulljoin(a,b,c)
		private function comFullJoin(){
			if($this->sql['fulljoin']!==""){
				if(is_array($this->sql['on']) && is_array($this->sql['fulljoin'][0])){ //fulljoin(array(a,b,c)) 
					$arr = $this->sql['fulljoin'][0];
				}elseif(count($this->sql['fulljoin'])==1 && is_string($this->sql['fulljoin'][0])){  //fulljoin("a b c")//fulljoin("a,b,c")
					$arr = preg_replace("/(\s)+/", ",", $this->sql['fulljoin'][0]);
					$arr = explode(",", $arr);
				}else{ //fulljoin(a,b,c)
					$arr = $this->sql['fulljoin'];
				}
				//剔除不存在的表
				for($i=0;$i<count($arr);$i++){
					if(!in_array($arr[$i], $this->tableList)){
						unset($arr[$i]);
					}
				}
				$this->sql['rightjoin'] = " full outer join ".implode(" full outer join ", $arr);
			}
		}

		//设置连接条件
		//on(array("a"=>c,"b"=>d)) 
		//on(array("'a=b','c'='d'"))//on(array("'a=b' 'c'='d'"))
		private function comOn(){
			$on=" ON ";
			$flag = false;
			if(is_array($this->sql['on']) && is_array($this->sql['on'][0])){
				$and = "";
				$keys = array_keys($this->sql['on'][0]);
				$vals = array_values($this->sql['on'][0]);
				for($i=0;$i<count($keys);$i++){
					if(!$flag){ //第一个条件不需要加and
						$and = $and.$keys[$i]."=".$vals[$i];							
						$flag = true;
					}else{
						$and = $and." AND ".$keys[$i]."=".$vals[$i];				
					}
				}
				$on = $on.$and;
			}elseif(is_array($this->sql['on']) && is_string($this->sql['on'][0]) && $this->sql['on'][0] !== ""){
				$on = $on.preg_replace("/[\s|,]+/"," AND ", $this->sql['on'][0]);
			}else{
				$on = "";
			}
			$this->sql['on'] = $on;
		}
		
		//设置操作的条件
		//where(array("a"=>0,"b"=>0)) 
		//where(array("a"=>0,"b"=>0),array("c"=>0,"d"=>0)) 
		//同一数组表and，不同数组为or
		private function comWhere(){
			$where = " WHERE ";
			$flag = false;
			if(count($this->sql['where'])>1){  //如果多个参数,每个必须为数组 
				for($i=0;$i<count($this->sql['where']);$i++){
					if(is_array($this->sql['where'][$i])){ 
						$and = "(";
						$keys = array_keys($this->sql['where'][$i]);
						$vals = array_values($this->sql['where'][$i]);
						for($j=0;$j<count($keys);$j++){
							if($keys[$j] == "password"){
								$vals[$j] = md5($vals[$j]);
							}
							if($flag = false){ //第一个条件不需要加and
								if(is_string($vals[$j])){
									$and = $and.$keys[$j]."='".$vals[$j]."'";
								}else{
									$and = $and.$keys[$j]."=".$vals[$j];
								}								
								$flag = true;
							}else{
								if(is_string($vals[$j])){
									$and = $and." AND ".$keys[$j]."='".$vals[$j]."'";
								}else{
									$and = $and." AND ".$keys[$j]."=".$vals[$j];
								}
								
							}
						}
						$and = $and.")";
					}
					if($i < $arg_num - 1){
						$where = $where.$and." OR ";
					}else{
						$where = $where.$and;
					}
				}			
			}elseif(is_array($this->sql['where']) && is_array($this->sql['where'][0])){
					$and = '';
					$keys = array_keys($this->sql['where'][0]);
					$vals = array_values($this->sql['where'][0]);
					for($i=0;$i<count($keys);$i++){
						if($keys[$i] == "password"){
							$vals[$i] = md5($vals[$i]);
						}

						// if($vals[$i]===''){
						// 	unset($vals[$i]);
						// 	unset($keys[$i]);
						// }

						if($flag == false){ //第一个条件不需要加and
							if(is_string($vals[$i])){
								$and = $and.$keys[$i]."='".$vals[$i]."'";
							}else{
								$and = $and.$keys[$i]."=".$vals[$i];
							}								
							$flag = true;
						}else{
							if(is_string($vals[$i])){
								$and = $and." AND ".$keys[$i]."='".$vals[$i]."'";
							}else{
								$and = $and." AND ".$keys[$i]."=".$vals[$i];
							}							
					   }
					}
					$where = $where.$and;
			}else{
					$where = "";
			}
			$this->sql['where'] = $where;
		}
		
		//设置Having条件
		//having("SUM(a)>1") 
		private function comHaving(){
			if( is_array($this->sql['having']) && is_string($this->sql['having'][0])  && $this->sql['on'][0] !== ""){
				$this->sql['having'] = " HAVING ".$this->sql['having'][0];
			}else{
				$this->sql['having']= "";
			}
		}

		//设置查询的条数
		//limit(array(a,b)) //limit(array(a))
		//limit("a b") //limit("a,b")
		//limit(a,b)
		private function comLimit(){
			if($this->sql['limit']!=="" && count($this->sql['limit'])<=2){
				if(is_array($this->sql['limit'][0])){ //limit(array(a,b)) //limit(array(a))
					$str = implode(",",$this->sql['limit'][0]);
				}
				elseif(count($this->sql['limit'])==1 && is_string($this->sql['limit'][0])){  //limit("a b") //limit("a,b")
					$str = preg_replace("/(\s)+/", ",", $this->sql['field'][0]);
				}else{ //limit(a,b)
					$str = implode(",",$this->sql['limit']);
				}
				
				$this->sql['limit'] = " LIMIT ".$str." ";
			}else{
				$this->sql['limit'] = "";
			}
		}
		
		//设置查询结果排练顺序
		//order(array(a,b,c)) 
		//order("a b c") //order("a,b,c")
		//order(a,b,c)
		private function comOrder(){
			if($this->sql['order']!==""){
				if(is_array($this->sql['order'][0])){ //order(array(a,b,c)) 
					$arr = $this->sql['order'][0];
				}
				elseif(count($this->sql['order'])==1 && is_string($this->sql['order'][0])){  //order("a b c") //order("a,b,c")
					$arr = str_replace(' ', ',', $this->sql['order'][0]);
					$arr = explode(",", $arr);
				}else{ //order(a,b,c)
					$arr = $this->sql['order'];
				}
				//剔除不存在的字段
				for($i=0;$i<count($arr);$i++){
					if(!in_array($this->sql['order'][$i], $this->fieldList)){
						unset($arr[$i]);
					}
				}
				$this->sql['order'] = " ORDER BY ".implode(",", $arr)." ";
			}
		}
		
		//设置Group条件
		//group(array(a,b,c)) 
		//group("a b c") //group("a,b,c")
		//group(a,b,c)
		private function comGroup(){
			if($this->sql['group']!==""){
				if(is_array($this->sql['group'][0])){ //group(array(a,b,c)) 
					$arr = $this->sql['group'][0];
				}
				elseif(count($this->sql['group'])==1 && is_string($this->sql['group'][0])){  //group("a b c") //group("a,b,c")
					$arr = str_replace(' ', ',', $this->sql['order'][0]);
					$arr = explode(",", $arr);
				}else{ //group(a,b,c)
					$arr = $this->sql['group'];
				}
				//剔除不存在的字段
				for($i=0;$i<count($arr);$i++){
					if(!in_array($this->sql['group'][$i], $this->fieldList)){
						unset($arr[$i]);
					}
				}
				$this->sql['group'] = " GROUP BY ".implode(",", $arr)." ";
			}
		}
		
		//组合sql条件
		private function comSQL(){
			$this->comField();
			$this->comInnerJoin();
			$this->comLeftJoin();
			$this->comRightJoin();
			$this->comOn();
			$this->comWhere();
			$this->comLimit();
			$this->comOrder();
			$this->comHaving();
			$this->comGroup();
		}

		
		//按条件查询记录,返回查询到的所有记录
		function select(){
			$this->comSQL();
			$sql = "SELECT ".$this->sql['field']." from ".$this->tName.$this->sql['innerjoin'].$this->sql['leftjoin'].$this->sql['rightjoin'].$this->sql['fulljoin'].$this->sql['on'].$this->sql['where'].$this->sql['having'].$this->sql['limit'].$this->sql['group'];
			$this->setNull();
			return $this->query($sql,__METHOD__);
		}
		
		//按条件查询记录,返回查询到的一条记录
		function find(){
			$this->comSQL();
			$sql = "SELECT ".$this->sql['field']." from ".$this->tName.$this->sql['innerjoin'].$this->sql['leftjoin'].$this->sql['rightjoin'].$this->sql['fulljoin'].$this->sql['on'].$this->sql['where'].$this->sql['having']." LIMIT 1".$this->sql['group'];
			$this->setNull();
			return $this->query($sql,__METHOD__);
			
		}
		//按条件查询返回符合的记录的条数
		function total(){
			$this->comSQL();
			$sql = "SELECT COUNT(*) as count FROM ".$this->tName.$this->sql['innerjoin'].$this->sql['leftjoin'].$this->sql['rightjoin'].$this->sql['fulljoin'].$this->sql['on'].$this->sql['where'];
			$this->setNull();
			return $this->query($sql,__METHOD__);
		}
		//修改记录
		function update($data){
			$this->comSQL();
			// $data = $this->check($data,$filter);
			$sql = "UPDATE ".$this->tName." SET ";
			if(is_array($data)){
				foreach($data as $key=>$val){
					if(is_null($val)){
						continue;
					}
					if(is_string($val)){
						$key_val[] = $key."='".$val."'";
					}else{
						$key_val[] = $key."=".$val;
					}		
				}
				$sql = $sql.implode(",", $key_val);
			}
			$sql = $sql.$this->sql['where'];
			$this->setNull();
			return $this->query($sql,__METHOD__);
		}
	
		//添加记录
		function insert($data,$filter=0){
			$this->comSQL();
			// $data = $this->check($data,$filter);
			$sql = "INSERT INTO ".$this->tName; 
			if(is_array($data)){
				foreach ($data as $key=>$val){
					if(is_null($val)){
						continue;
					}
					$keys[]=$key;
					if(is_string($val)){
						$vals[]="'".$val."'";
					}else{
						$vals[]=$val;
					}							
				}
				$sql = $sql."(".implode(",", $keys).") VALUES(".implode(",", $vals).")";
			}		
			$this->setNull();
			return $this->query($sql,__METHOD__);
		}
		
		//删除记录
		function delete(){
			$this->comSQL();
			$sql = "DELETE FROM ".$this->tName.$this->sql['where'];
			$this->setNull();
			return $this->query($sql,__METHOD__);
		}

		abstract function getFields();
		abstract function getTables();
		abstract function beginTransaction();
		abstract function commit();
		abstract function rollBack();
		abstract function dbSize();
		abstract function dbVersion();
		abstract function query($sql, $method);
		
	}
