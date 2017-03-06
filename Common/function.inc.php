<?php 
		use Done\Lib\Core\Debug;
		use Done\lib\Core\Structure;
		/**
     	* 判断当前服务器系统
	    * @return string
	    */
	    function getOS(){
	         if (PATH_SEPARATOR == ':') {
	             return 'Linux';
	         } else {
	             return 'Windows';
	        }
	     }

		//==========================================
		// 函数: get_ip()
		// 功能: 返回IP地址
		// 参数: 无
		//==========================================
		function getIP(){
			if(getenv('HTTP_CLIENT_IP')){
				$onlineip=getenv('HTTP_CLIENT_IP');
			}else if(getenv('HTTP_X_FORWARDED_FOR')){
				$onlineip=getenv('HTTP_X_FORWARDED_FOR');
			}else if(getenv('REMOTE_ADDR')){
				$onlineip=getenv('REMOTE_ADDR');
			}else{
				$onlineip=$_SERVER['REMOTE_ADDR'];
			}
			return $onlineip;
		}
		//判断是否是移动客户端请求
		function isMobileRequest(){  

			 $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';  
			 $mobile_browser = '0';  
			 if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
			    $mobile_browser++;  
			 if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))  
			    $mobile_browser++;  
			 if(isset($_SERVER['HTTP_X_WAP_PROFILE']))  
			    $mobile_browser++;  
			 if(isset($_SERVER['HTTP_PROFILE']))  
			 	$mobile_browser++;  
			 $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));  
			 $mobile_agents = array(  
				'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',  
				'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',  
				'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',  
				'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',  
				'newt','noki','oper','palm','pana','pant','phil','play','port','prox',  
				'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',  
				'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',  
				'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',  
				'wapr','webc','winw','winw','xda','xda-'
				);  
			 if(in_array($mobile_ua, $mobile_agents))  
			 	$mobile_browser++;  
			 if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)  
			 	$mobile_browser++;  
			 // Pre-final check to reset everything if the user is on Windows  
			 if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)  
			  	$mobile_browser=0;  
			 // But WP7 is also Windows, with a slightly different characteristic  
			 if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)  
			  	$mobile_browser++;  
			 if($mobile_browser>0)  
				return true;  
			 else
				return false;
		}
		
		//==========================================
		// 函数: time_ago()		// 功能: 返回距现在多长时间
		// 参数: time 时间戳
		//==========================================
		function timeAgo($time){
			$t=time()-$time;
			$f=array(
				'31536000'=>'年',
				'2592000'=>'个月',
				'604800'=>'星期',
				'86400'=>'天',
				'3600'=>'小时',
				'60'=>'分钟',
				'1'=>'秒'
			);
			foreach ($f as $k=>$v)    {
				if (0 !=$c=floor($t/(int)$k)) {
					return $c.$v.'前';
				}
			}
		}
		
		
		
		/* 
		Utf-8、gb2312都支持的汉字截取函数 
		cut_str(字符串, 截取长度, 开始长度, 编码); 
		编码默认为 utf-8 
		开始长度默认为 0 
		*/ 

		function cutStr($string, $sublen, $start = 0, $code = 'UTF-8'){ 
			if($code == 'UTF-8') { 
				$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/"; 
				preg_match_all($pa, $string, $t_string); 
				
				if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen))."..."; 
				return join('', array_slice($t_string[0], $start, $sublen)); 
				} 
				else 
				{ 
				$start = $start*2; 
				$sublen = $sublen*2; 
				$strlen = strlen($string); 
				$tmpstr = ''; 
				
				for($i=0; $i< $strlen; $i++) 
				{ 
				if($i>=$start && $i< ($start+$sublen)) 
				{ 
				if(ord(substr($string, $i, 1))>129) 
				{ 
				$tmpstr.= substr($string, $i, 2); 
				} 
				else 
				{ 
				$tmpstr.= substr($string, $i, 1); 
				} 
				} 
				if(ord(substr($string, $i, 1))>129) $i++; 
				} 
				if(strlen($tmpstr)< $strlen ) $tmpstr.= "..."; 
				return $tmpstr; 
			} 
		} 

		/**
		 * 文件尺寸转换，将大小将字节转为各种单位大小
		 * @param	int	$bytes	字节大小
		 * @return	string	转换后带单位的大小
		 */
		function toSize($bytes) {       	 	     //自定义一个文件大小单位转换函数
			if ($bytes >= pow(2,40)) {      		     //如果提供的字节数大于等于2的40次方，则条件成立
				$return = round($bytes / pow(1024,4), 2);    //将字节大小转换为同等的T大小
				$suffix = "TB";                        	     //单位为TB
			} elseif ($bytes >= pow(2,30)) {  		     //如果提供的字节数大于等于2的30次方，则条件成立
				$return = round($bytes / pow(1024,3), 2);    //将字节大小转换为同等的G大小
				$suffix = "GB";                              //单位为GB
			} elseif ($bytes >= pow(2,20)) {  		     //如果提供的字节数大于等于2的20次方，则条件成立
				$return = round($bytes / pow(1024,2), 2);    //将字节大小转换为同等的M大小
				$suffix = "MB";                              //单位为MB
			} elseif ($bytes >= pow(2,10)) {  		     //如果提供的字节数大于等于2的10次方，则条件成立
				$return = round($bytes / pow(1024,1), 2);    //将字节大小转换为同等的K大小
				$suffix = "KB";                              //单位为KB
			} else {                     			     //否则提供的字节数小于2的10次方，则条件成立
				$return = $bytes;                            //字节大小单位不变
				$suffix = "Byte";                            //单位为Byte
			}
			return $return ." " . $suffix;                       //返回合适的文件大小和单位
		}

		
		
		/**
	     * 日志方法
	     * @param $log
	     */
	    function writeLog($type="access",$log){
	    	if($type="access"){
	    		$dir = APP_LOG_PATH."access";
	    	}else{
	    		$dir = APP_LOG_PATH."error";
	    	}
	       if (!is_dir($dir)) {
	           mkdir($dir);
	        }
	        $filename = $dir."/".date("Y-m-d").".log";
	        $content = "[".date("Y-m-d H:i:s")."][".getIP()."]:\t".$log.PHP_EOL;
	        file_put_contents($filename,$content,FILE_APPEND);
	    }


	     /**
      	* 签名验证函数
     	* @param $param   需要加密的字符串
	     * @param $sign     第三方已经机密好的用来比对的字串
	     * @return bool
	     */
	    function validateSign($param, $sign){
	        if (md5($param) == $sign) {
	             return true;
	        } else {
	            return false;
	        }
	    }

	    /**
     	* 将xml转换为数组
    	* @param $xml  需要转化的xml
   		* @return mixed
   		*/
   	    function xmlToArray($xml){
	        $ob = simplexml_load_string($xml);
	        $json = json_encode($ob);
	        $array = json_decode($json, true);
	        return $array;
	    }

	    /**
	    * 将数组转化成xml
	     * @param $data 需要转化的数组
	     * @return string
	     */
	     function dataToXml($data){
	        if(is_object($data)) {
	            $data = get_object_vars($data);
	       	}
	        $xml='';
	        foreach($data as $key=>$val) {
	            if (is_null($val)) {
	                $xml.="<$key/>\n";
		        }else{
		            if(!is_numeric($key)){
		                $xml .= "<$key>";
		            }
		            $xml.= (is_array($val) || is_object($val))?data_to_xml($val):$val;
		                 if(!is_numeric($key)){
		                   $xml .= "</$key>";
		                }
		            }
		         }
	        return $xml;
     	}

     	//unicode编码
     	function encodeUnicode($name)  
		{  
		    $name = iconv('UTF-8', 'UCS-2', $name);  
		    $len = strlen($name);  
		    $str = '';  
		    for ($i = 0; $i < $len - 1; $i = $i + 2)  
		    {  
		        $c = $name[$i];  
		        $c2 = $name[$i + 1];  
		        if (ord($c) > 0)  
		        {    // 两个字节的文字  
		            $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);  
		        }  
		        else  
		        {  
		            $str .= $c2;  
		        }  
		    }  
		    return $str;  
		}  
		//unicode解码
		function decodeUnicode($str)
		{
		    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
		        create_function(
		            '$matches',
		            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
		        ),
		        $str);
		}

		/**
		 * 发起一个post请求到指定接口
		 * 
		 * @param string $api 请求的接口
		 * @param array $params post参数
		 * @param int $timeout 超时时间
		 * @return string 请求结果
		 */
		function postRequest($api,$data=array(),$post=true,$send="json",$retJson=true,$timeout=30) {
			$header = array(
				"Accept:application/json"
			);
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $api );
			// 以返回的形式接收信息
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			// 设置为POST方式
			if($post){
				curl_setopt( $ch, CURLOPT_POST, 1 );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($data));
			}
			if($send=="json"){
				$header[] = 'Content-Type:application/json;charset=UTF-8';
			}elseif($send=="xml"){
				$header[] = 'Content-Type:text/xml;charset=UTF-8';
			}else{
				$header[] = 'Content-Type:application/x-www-form-urlencoded;charset=UTF-8';
			}
			// 不验证https证书
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
			curl_setopt( $ch, CURLOPT_HTTPHEADER,$header); 
			// 发送数据
			$response = curl_exec($ch);
			//$httpHeader  = curl_getinfo($ch);
			if(curl_errno($ch)){
				return false;
			}
			// 不要忘记释放资源
			curl_close( $ch );
			if($retJson){
				$response = json_decode($res);
			}  
			return $response;
		}


		/**
		 * 创建Models中的数据库操作对象
		 *  @param	string	$className	类名或表名
		 *  @param	string	$app	 应用名,访问其他应用的Model
		 *  @return	object	数据库连接对象
		 */
		function D($className=null){
			$model=null;	
			//如果没有传表名或类名，则直接创建DB对象，但不能对表进行操作
			if(is_null($className)){
				$className = ucfirst(DRIVER);
				$model=new $className();
			}else{
				$className=ucfirst($className);
				$model=Structure::model($className);	
				$model=new $model();
			}
			return $model;
		}

		//获取配置文件
		function C($filename,$dir=null){
			if(!$dir){
				$dir = API_CONF_PATH;
			}
			$file = $dir.$filename.API_CONF_EXT;
			if(file_exists($file))
				return require_once($file);
			else
				Debug::addmsg("配置文件".$dir.$file."不存在！");
		}


?>