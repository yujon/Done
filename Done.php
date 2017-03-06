<?php 
	namespace Done;

	use Done\Extend;
	use Done\Lib\Core\Action;
	use Done\Lib\Core\DB;
	use Done\Lib\Core\Debug;
	use Done\Lib\Core\Memcachesql;
	use Done\Lib\Core\Memcachesession;
	use Done\Lib\Core\MySmarty;
	use Done\Lib\Core\Mysqli;
	use Done\Lib\Core\Pdo;
	use Done\Lib\Core\RewriteUrl;
	use Done\Lib\Core\Structure;
	use Done\Lib\Vendor\Smarty\Smarty;

	header("Content-Type:text/html;charset=utf-8");  //设置系统的输出字符为utf-8
	date_default_timezone_set("PRC");    		 //设置时区（中国）

	//记录开始运行时间
	define('BEGIN_TIME',microtime(TRUE));

	//记录内存初始使用
	define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
	if(MEMORY_LIMIT_ON){
		define("START_USE_MEMS",memory_get_usage());
	}

	//版本信息
	const PHPAPI = 1.0;

	//框架的目录
	define('Done_PATH',__DIR__.DIRECTORY_SEPARATOR);
	//核心类与第三方类库目录
	define('Done_LIB_PATH',Done_PATH.'Lib'.DIRECTORY_SEPARATOR);
	define('Done_CORE_PATH',Done_LIB_PATH.'Core'.DIRECTORY_SEPARATOR); 
	define('Done_VENDOR_PATH',Done_LIB_PATH.'Vendor'.DIRECTORY_SEPARATOR);
	//扩展类库目录
	define('Done_EXTEND_PATH',Done_PATH.'Extend'.DIRECTORY_SEPARATOR); 
	//框架公共方法文件目录
	define('Done_COM_PATH',Done_PATH.'Common'.DIRECTORY_SEPARATOR); 
	//框架配置文件目录
	define('Done_CONF_PATH',Done_PATH.'Conf'.DIRECTORY_SEPARATOR); 
	//框架模板文件目录
	define('Done_TPL_PATH',Done_PATH.'Tpl'.DIRECTORY_SEPARATOR); 
	//Smarty左定界符
	defined('LEFT_DELIMITER') or define('LEFT_DELIMITER','{');
	//Smarty右定界符
	defined('RIGHT_DELIMITER') or define('RIGHT_DELIMITER','}');

	//工程目录
	defined('PROJECT_PATH') or define('PROJECT_PATH',dirname(__DIR__).DIRECTORY_SEPARATOR);
	//应用名称，默认为Home
	defined('APP_NAME') or define('APP_NAME',"Home");
	//应用目录
	define('APP_PATH',PROJECT_PATH.APP_NAME.DIRECTORY_SEPARATOR);
	//模板皮肤名称，默认为default
	defined('APP_SKIN_NAME') or define('APP_SKIN_NAME','default');
	//应用视图后缀
	defined('APP_TPL_PREFIX') or define('APP_TPL_PREFIX','.tpl');

	//公共类目录
	defined('APP_ClASS_PATH') or define('APP_ClASS_PATH', PROJECT_PATH.'Classes'.DIRECTORY_SEPARATOR); 
	//公共方法目录
	defined('APP_COM_PATH') or define('APP_COM_PATH', PROJECT_PATH.'Common'.DIRECTORY_SEPARATOR); 
	//公共配置文件目录
	defined('APP_CONF_PATH') or define('APP_CONF_PATH', PROJECT_PATH.'Conf'.DIRECTORY_SEPARATOR);
	defined('APP_CONF_EXT') or define('APP_CONF_EXT','.inc.php'); // 配置文件后缀
	//公共语言目录
	defined('APP_LANG_PATH') or define('APP_LANG_PATH', PROJECT_PATH.'Lang'.DIRECTORY_SEPARATOR); 
	// 公共静态资源目录
	defined('APP_PUBLIC_PATH') or define('APP_PUBLIC_PATH',PROJECT_PATH.'Public'.DIRECTORY_SEPARATOR); 
	//工程运行时目录
	defined('APP_RUNTIME_PATH') or define('APP_RUNTIME_PATH',PROJECT_PATH.'Runtime'.DIRECTORY_SEPARATOR);
	defined('APP_COMPILE_PATH') or define('APP_COMPILE_PATH',APP_RUNTIME_PATH.'Templates_c'.DIRECTORY_SEPARATOR); //编译目录
	defined('APP_DATA_PATH')  or define('APP_DATA_PATH', APP_RUNTIME_PATH.'Data'.DIRECTORY_SEPARATOR); // 数据缓存目录
	defined('APP_FIELDS_PATH')  or define('APP_FIELDS_PATH', APP_RUNTIME_PATH.'Fields'.DIRECTORY_SEPARATOR); // 表结构缓存目录
	defined('APP_CACHE_PATH') or define('APP_CACHE_PATH',APP_RUNTIME_PATH.'Cache'.DIRECTORY_SEPARATOR); //模板缓存目录
	defined('APP_CACHE_LIFTTIME') or define('APP_CACHE_LIFTTIME',3600);//缓存保存时间
	defined('APP_LOG_PATH')  or define('APP_LOG_PATH',APP_RUNTIME_PATH.'Logs'.DIRECTORY_SEPARATOR);// 日志目录
	defined('APP_CONTROLLS_PATH')  or define('APP_CONTROLLS_PATH',APP_RUNTIME_PATH.'Controlls'.DIRECTORY_SEPARATOR);// 控制器目录
	defined('APP_MODELS_PATH')  or define('APP_MODELS_PATH',APP_RUNTIME_PATH.'Models'.DIRECTORY_SEPARATOR);// 模型目录

	// 测试文件目录
	defined('APP_TEST_PATH') or define('APP_TEST_PATH',PROJECT_PATH.'Test'.DIRECTORY_SEPARATOR); 
	// 测试报告目录
	defined('APP_REPORT_PATH') or define('APP_REPORT_PATH',PROJECT_PATH.'Report'.DIRECTORY_SEPARATOR); 
	//构建文件
	defined('APP_BUILD_PATH') or define('APP_BUILD_PATH',PROJECT_PATH.'build.xml'); 

	//定义数据库驱动
	defined('DRIVER') or define('DRIVER','pdo');


	//是否调用调试模式
	defined('APP_DEBUG') or define('APP_DEBUG',true);
	//是否调用编译模式
	defined('APP_COMPILE') or define('APP_COMPILE',true);
	//是否启用页面缓存模式
	defined('APP_CACHE') or define('APP_CACHE',false);
	//是否启用memcache缓存
	defined('APP_MEMCACHE') or define('APP_MEMCACHE',false);
	//是否启用数据缓存
	defined('APP_DATA') or define('APP_DATA',false);
	//是否启用字段缓存
	defined('APP_FIELDS') or define('APP_FIELDS',false);

	/*
	在magic_quotes_gpc=On的情况下，如果输入的数据有单引号（’）、双引号（”）、反斜线（）与 NUL（NULL 字符）等字符都会被加上反斜线。这些转义是必须的，如果这个选项为off，那么我们就必须调用addslashes这个函数来为字符串增加转义。
	在php5.4以后就废除了此特性。所以我们在以后就不要依靠这个特性了。为了使自己的程序不管服务器是什么设置都能正常执行。可以在程序开始用get_magic_quotes_runtime检测该设置的状态决定是否要手工处理，或者在开始（或不需要自动转义的时候）用set_magic_quotes_runtime(0)关掉该设置。
	判断php版本，小于5.4的就手动关掉，定义常量。大于5.4直接定义常量为false。
	*/
	if(version_compare(PHP_VERSION,'5.4.0','<')){
		ine_set('magic_quotes_runtime',0);  //set_magic_quotes_runtime(0);
		define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?true:false);
	}else{
		define('MAGIC_QUOTES_GPC',false);
	}


	//php运行模式
	/*
	php判断解析php服务是由那种服务器软件，是采用那种协议,PHP_ASPI是一个可以直接使用的常量。
	如果是nginx+fastcgi环境，那么它的值是cgi-fcgi
	如果是apache环境，那么他的值是apache2handler
	如果是命令行的形式，那么它的值是cli
	PHP_OS PHP所在的操作系统的名字，例如linux和WIN。
	*/
	define('IS_CGI',(0===strpos(PHP_SAPI,'cgi')||false!==strpos(PHP_SAPI,'fcgi'))? 1 : 0);
	define('IS_CLI',PHP_SAPI=='cli');
	define('IS_WIN',strstr(PHP_OS,'WIN')?1:0);
	//如果不是命令行模式的话，指定当前运行脚本的文件名。
	if(!IS_CLI){
		if(!defined('_PHP_FILE_')){
			if(IS_CGI){
				$_temp = explode('.php',$_SERVER['PHP_SELF']);
				$_file = rtrim(str_replace($_SERVER['HOST_NAME'],'',$_temp[0].'.php'),'/');
				define('_PHP_FILE_',$_file);
			}else{
				define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
			}
		}
		if(!defined('__ROOT__')){
			$_root = rtrim(dirname(_PHP_FILE_),'/');
			define('__ROOT__',(($_root=='/'||$_root=='\\')?'':$_root));
		}
	}

	//设置Debug模式
	if(defined("APP_DEBUG") && APP_DEBUG){
		error_reporting(E_ALL ^ E_NOTICE);   //输出除了注意的所有错误报告
		include Done_CORE_PATH."Debug.class.php";  //包含debug类
		Debug::start();                               //开启脚本计算时间
		set_error_handler(array("Done\Lib\Core\Debug", 'Catcher')); //设置捕获系统异常
	}else{
		ini_set('display_errors', 'Off'); 		//屏蔽错误输出
		ini_set('log_errors', 'On');             	//开启错误日志，将错误报告写入到日志中
		ini_set('error_log', APP_LOG_PATH.'error_log'); //指定错误日志文件

	}

	//类文件后缀
	const EXT = '.class.php';
	//设置包含目录（类所在的全部目录）,  PATH_SEPARATOR 分隔符号 Linux(:) Windows(;)
	$include_path=get_include_path();                         //原基目录
	$include_path.=PATH_SEPARATOR.Done_CORE_PATH;       //框架中核心类所在的目录
	$include_path.=PATH_SEPARATOR.Done_VENDOR_PATH."Smarty".DIRECTORY_SEPARATOR;//Smarty所在的目录
	$include_path.=PATH_SEPARATOR.Done_VENDOR_PATH."Smarty".DIRECTORY_SEPARATOR."sysplugins".DIRECTORY_SEPARATOR;//Smarty内部插件的目录
	$include_path.=PATH_SEPARATOR.Done_EXTEND_PATH; //框架中扩展类的目录
	$include_path.=PATH_SEPARATOR.PROJECT_PATH."Classes".DIRECTORY_SEPARATOR;  //项目中用的到的工具类  
	$controlerpath=APP_CONTROLLS_PATH.APP_NAME.DIRECTORY_SEPARATOR;  //生成控制器所在的路径
	$include_path.=PATH_SEPARATOR.$controlerpath;             //当前应用的控制类所在的目录 
	//设置include包含文件所在的所有目录	
	set_include_path($include_path);
   
	//自动加载类 	
	function autoload($className){
		if($className=="memcache"){        //如果是系统的Memcache类则不包含
			return;
		}elseif(preg_match("/Smarty_/",$className)){ //如果是Smarty内部插件
			include_once strtolower($className).'.php';
		}else{                  //如果是其他类，将类名转为小写并将首字母转为大写
			include_once ucfirst(strtolower($className)).EXT;	
		}
		Debug::addmsg("<b> $className </b>类", 1);  //在debug中显示自动包含的类
	}
	spl_autoload_register("Done\autoload");

	//引入框架函数库
	require_once Done_COM_PATH."function.inc.php";

	//包含全局的函数库文件，用户可以自己定义函数在这个文件中
	if(file_exists(APP_COM_PATH."functions.inc.php"))
		include_once APP_COM_PATH."function.inc.php";

	//判断是否开启了页面静态化缓存
	if(defined("APP_CACHE") && !APP_CACHE){
		Debug::addmsg("<font color='red'>没有开启页面缓存!</font>（但可以使用，通过设置APP_CACHE常量为true）"); 
	}else{
		Debug::addmsg("开启页面缓存，实现页面静态化!"); 
	}

	//判断是否开启了数据缓存
	if(defined("APP_DATA") && !APP_DATA){
		Debug::addmsg("<font color='red'>没有开启数据缓存!</font>（但可以使用，通过设置APP_DATA常量为true）"); 
	}else{
		Debug::addmsg("开启数据缓存!"); 
	}

	//判断是否开启了字段缓存
	if(defined("APP_FIELDS") && !APP_FIELDS){
		Debug::addmsg("<font color='red'>没有开启字段缓存!</font>（但可以使用，通过设置APP_FIELDS常量为true）"); 
	}else{
		Debug::addmsg("开启字段缓存!"); 
	}
	
	//启用memcache缓存
	if(APP_MEMCACHE && !empty($memServers)){
		//判断memcache扩展是否安装
		if(extension_loaded("memcache")){
			$mem=new Memcachesql($memServers);
			//判断Memcache服务器是否有异常
			if(!$mem->mem_connect_error()){
				Debug::addmsg("<font color='red'>连接memcache服务器失败,请检查!</font>"); 
			}else{
				define("APP_MEMCACHE",true);
				Debug::addmsg("启用了Memcache");
			}
		}else{
			Debug::addmsg("<font color='red'>PHP没有安装memcache扩展模块,请先安装!</font>"); 
		}	
	}else{
		Debug::addmsg("<font color='red'>没有使用Memcache</font>(为程序的运行速度，建议使用Memcache)");  
	}

	//如果Memcach开启，设置将Session信息保存在Memcache服务器中
	if(defined("APP_MEMCACHE") && APP_MEMCACHE && extension_loaded("memcache")){
		Memcachesession::start($mem->getMem());
		Debug::addmsg("开启会话Session (使用Memcache保存会话信息)"); //debug
	}else{
		session_start();
		Debug::addmsg("<font color='red'>开启会话Session </font>(但没有使用Memcache，开启Memcache后自动使用)"); //debug
	}
	Debug::addmsg("会话ID:".session_id());

	//初使化时，创建项目的目录结构
	Structure::create();  

	//引入数据库配置文件并设置
	$db = C('db');
	isset($db['dbtype'])?define('DBTYPE',$db['dbtype']):define('DBTYPE','mysql');
	isset($db['username'])?define('USERNAME',$db['username']):'root';
	isset($db['password'])?define('PASSWORD',$db['password']):define('PASSWORD','');
	isset($db['host'])?define('HOST',$db['host']):define('HOST','localhost');
	isset($db['dbname'])?define('DBNAME',$db['dbname']):define('DBNAME','test');
	isset($db['tableprev'])?define('TABLEPREV',$db['tableprev']):define('TABLEPREV','');	

	//静态文件中所有要的路径，将要用到的文件的绝对路径转化为可访问获取的路径	
	$access_root_path = rtrim(substr(dirname(str_replace("\\", '/', dirname(__FILE__))), strlen(rtrim($_SERVER["DOCUMENT_ROOT"],"/\\"))), '/\\');
	$access_app_path = $_SERVER["SCRIPT_NAME"].DIRECTORY_SEPARATOR;	
	$static_public_path = $access_root_path.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR;  //项目的全局资源目录
	$static_res_path = rtrim(dirname($_SERVER["SCRIPT_NAME"]),"/\\").'/'.ltrim(APP_NAME, './')."views".DIRECTORY_SEPARATOR.APP_SKIN_NAME.DIRECTORY_SEPARATOR."resource".DIRECTORY_SEPARATOR; //当前应用模板的资源
	define("ACCESS_ROOT_PATH",$access_root_path);
	define("ACCESS_APP_PATH",$access_app_path);
	define("ACCESS_PUBLIC_PATH",$static_public_path);
	define("ACCESS_RES_PATH", $static_res_path);

	 //解析处理URL 
	RewriteUrl::parse();   

	//控制器类所在的路径
	$srccontrolerfile=APP_PATH."Controls".DIRECTORY_SEPARATOR.strtolower($_GET["m"]).".class.php";
	Debug::addmsg("当前访问的控制器类在项目应用目录下的: <b>$srccontrolerfile</b> 文件！");
	//控制器类的创建
	if(file_exists($srccontrolerfile)){

		Structure::commonControler(APP_PATH."Controls".DIRECTORY_SEPARATOR,$controlerpath);
		Structure::controler($srccontrolerfile, $controlerpath, $_GET["m"]); 
	
		$className=ucfirst($_GET["m"])."Action";
		
		$controler=new $className();
		$controler->run();

	}else{
		Debug::addmsg("<font color='red'>对不起!你访问的模块不存在,应该在".APP_PATH."controls目录下创建文件名为".strtolower($_GET["m"]).".class.php的文件，声明一个类名为".ucfirst($_GET["m"])."的类！</font>");
		
	}

	//设置输出Debug模式的信息
	if(defined("APP_DEBUG") && APP_DEBUG){
		Debug::stop();
		Debug::message();
	}

	//记录访问日志
	$log = "Access：".$_SERVER["SCRIPT_NAME"]."/".$_GET['m']."/".$_GET['a'];
	writeLog("access",$log);

    