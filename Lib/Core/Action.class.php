<?php 

/**
* 
*/
namespace Done\Lib\Core;

class Action extends MySmarty{

		/**
		 * 该方法用来运行框架中的操制器，在入口文件中调用
		 */
		function run(){
			//如果子类继承了Common类，调用这个类的init()方法 做权限控制
			if(method_exists($this, "init")){	
				$this->init();
			}	
			//根据动作去找对应的方法
			$method=$_GET["a"];
			if(method_exists($this, $method)){
				$this->$method();
			}else{
				Debug::addmsg("<font color='red'>没有{$_GET["a"]}这个操作！</font>");
			}
		}

		/** 
		 * 用于在控制器中进行位置重定向
		 * @param	string	$path	用于设置重定向的位置
		 * @param	string	$args 	用于重定向到新位置后传递参数
		 * 
		 * $this->redirect("index")  /当前模块/index
		 * $this->redirect("user/index") /user/index
		 * $this->redirect("user/index", 'page/5') /user/index/page/5
		 */
		function redirect($path, $args=""){
			$path=trim($path,"/");
			if($args!="")
				$args="/".trim($args, "/");
			if(strstr($path, "/")){
				$url=$path.$args;
			}else{
				$url=$_GET["m"]."/".$path.$args;
			}

			$uri=ACCESS_APP_PATH.$url;
			//使用js跳转前面可以有输出
			echo '<script>';
			echo 'location="'.$uri.'"';
			echo '</script>';
		}

		/**
		 * 成功的消息提示框
		 * @param	string	$mess		用示输出提示消息
		 * @param	int	$timeout	设置跳转的时间，单位：秒
		 * @param	string	$location	设置跳转的新位置
		 */
		function success($mess="操作成功", $timeout=1, $location=""){
			$this->pub($mess, $timeout, $location);
			$this->assign("mark", true);  //如果成功 $mark=true
			$this->display("public/success");
			exit;
		}
		/**
		 * 失败的消息提示框
		 * @param	string	$mess		用示输出提示消息
		 * @param	int	$timeout	设置跳转的时间，单位：秒
		 * @param	string	$location	设置跳转的新位置
		 */
		function error($mess="操作失败", $timeout=3, $location=""){
			$this->pub($mess, $timeout, $location);
			$this->assign("mark", false); //如果失败 $mark=false
			$this->display("public/success");
			exit;
		}

		private function pub($mess, $timeout, $location=""){	
			$this->setCaching(false);   //设置缓存关闭
			if($location==""){
				$location="window.history.back();";
			}else{
				$path=trim($location, "/");
			
				if(strstr($path, "/")){
					$url=$path;
				}else{
					$url=$_GET["m"]."/".$path;
				}
				$location=ACCESS_APP_PATH.'/'.$url;
				$location="window.location='{$location}'";
			}
			$this->assign("mess", $mess);
			$this->assign("timeout", $timeout);
			$this->assign("location", $location);
			debug(0);
		}
}


?>
