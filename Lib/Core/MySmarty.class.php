<?php  

namespace Done\Lib\Core;

class MySmarty extends \Smarty{

		public function __construct(){

			// 定义左右定界符
			$this->setLeftDelimiter(LEFT_DELIMITER);
			$this->setRightDelimiter(RIGHT_DELIMITER);

			//定义模板文件目录
			$this->addTemplateDir(APP_PATH.'/Views/'.APP_SKIN_NAME);
			
			//是否调试
			$this->setDebugging(APP_DEBUG);

			//定义编译文件
			$this->setCompileCheck(APP_COMPILE);
			$this->setCompileDir(APP_COMPILE_PATH);			
			
			//是否开启缓存，调试模式下不开启缓存
			if(!APP_DEBUG){
				$this->setCaching(APP_CACHE);
				$this->setCacheDir(APP_CACHE_PATH);
				$this->setCacheLifetime(APP_CACHE_LIFTTIME);
			}
			

			//定义项目入口
			$this->assign('app',ACCESS_APP_PATH);
			//定义公共资源目录
			$this->assign('public',ACCESS_PUBLIC_PATH);
			//定义资源目录
			$this->assign('res',ACCESS_RES_PATH);

			parent::__construct();
		}

}


?>