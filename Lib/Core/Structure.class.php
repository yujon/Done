<?php
/** ************************************************************************
 * 项目结构部署类，用于自动创建所需要的项目目录和文件结构。                *
 * ************************************************************************/
	namespace Done\Lib\Core;
	class Structure {
		static $mess=array();    //提示消息

		/*
		 * 创建文件
		 * @param	string	$fileName	需要创建的文件名
		 * @param	string	$str		需要向文件中写的内容字符串
		 */
		static function touch($fileName, $str){
			if(!file_exists($fileName)){
				if(file_put_contents($fileName, $str)){
					self::$mess[]="创建文件 {$fileName} 成功.";
				}
			}
		}
		/*
		 * 创建目录
		 * @param	string	$dirs		需要创建的目录名称
		 */
		static function mkdir($dirs){
			foreach($dirs as $dir){
				if(!file_exists($dir)){
					if(mkdir($dir,"0755")){
						self::$mess[]="创建目录 {$dir} 成功.";
					}
				}
			}
		}
		/**
		 * 创建系统运行时的文件
		 */
		static function runtime(){
			$dirs=array(
					APP_COMPILE_PATH.APP_NAME.DIRECTORY_SEPARATOR,
					APP_DATA_PATH.APP_NAME.DIRECTORY_SEPARATOR,
					APP_FIELDS_PATH.APP_NAME.DIRECTORY_SEPARATOR,
					APP_CACHE_PATH.APP_NAME.DIRECTORY_SEPARATOR,
					APP_LOG_PATH.APP_NAME.DIRECTORY_SEPARATOR,
					APP_CONTROLLS_PATH.APP_NAME.DIRECTORY_SEPARATOR,
					APP_MODELS_PATH.APP_NAME.DIRECTORY_SEPARATOR
				);
			self::mkdir($dirs);   //创建目录	
		}
		/**
		 *创建项目的目录结构
		 */
		static function create(){

			//文件锁，一旦生成，就不再创建
			$structFile=APP_RUNTIME_PATH.str_replace("/","_",$_SERVER["SCRIPT_NAME"]); 
			//主入口文件名

			if(!file_exists($structFile)){	
				
				$dirs=array(
					APP_RUNTIME_PATH, //运行时目录
					APP_COMPILE_PATH,
					APP_DATA_PATH,
					APP_FIELDS_PATH,
					APP_CACHE_PATH,
					APP_LOG_PATH,
					APP_CONTROLLS_PATH,
					APP_MODELS_PATH,
					APP_ClASS_PATH,    //项目的通用类
					APP_COM_PATH,    //项目的通用函数 functions.inc.php
					APP_CONF_PATH,   //配置文件
					APP_LANG_PATH,  //语言文件
					APP_PUBLIC_PATH,      //系统公共目录
					APP_PUBLIC_PATH."uploads/",  //系统公共上传文件目录
					APP_PUBLIC_PATH."css/",      //系统公css共目录
					APP_PUBLIC_PATH."js/",       //系统公共javascript目录
					APP_PUBLIC_PATH."images/",   //系统公共图片目录
					APP_PATH,                   //当前的应用目录
					APP_PATH."Models/",         //当前应用的模型目录
					APP_PATH."Controls/",       //当前应用的控制器目录
					APP_PATH."Views/",          //当前应用的视图目录
					APP_PATH."Views/".APP_SKIN_NAME, //当前应用的模板目录
					APP_PATH."Views/".APP_SKIN_NAME."/public/",           //公用模板目录
					APP_PATH."Views/".APP_SKIN_NAME."/resource/",        //当前应用模板公用资源目录
					APP_PATH."Views/".APP_SKIN_NAME."/resource/css/",     //当前应用模板CSS目录
					APP_PATH."Views/".APP_SKIN_NAME."/resource/js/",      //当前应用模板js目录
					APP_PATH."Views/".APP_SKIN_NAME."/resource/images/", //当前应用模板图标目录
					APP_TEST_PATH, //测试文件目录
					APP_REPORT_PATH, //测试报告文件目录
					APP_REPORT_PATH."coverage/",
					APP_REPORT_PATH."logs/",
					APP_REPORT_PATH."test/"
				);			
				self::mkdir($dirs);

				//创建通用函数目录
				$fileCon =  "<?php\n\t//全局可以使用的通用函数声明在这个文件中.";
				self::touch(APP_COM_PATH."function.inc.php",$fileCon);
				//创建统一的消息模板
				$success=APP_PATH."Views/".APP_SKIN_NAME."/public/success".APP_TPL_PREFIX;
				if(!file_exists($success))
					copy(Done_TPL_PATH."success".APP_TPL_PREFIX,$success);
				
				//创建控制器实例
				$fileCon=<<<st
<?php
	class Common extends Action {
		function init(){

		}		
	}
st;
				self::touch(APP_PATH."Controls".DIRECTORY_SEPARATOR."Common.class.php", $fileCon);
	
				$fileCon=<<<st
<?php
	class Index extends Common{
		function index(){
			echo "<h4>欢迎使用Done框架1.0！</h4>";
		}		
	}
st;
				self::touch(APP_PATH."Controls".DIRECTORY_SEPARATOR."Index.class.php", $fileCon);

				//创建模型实例
				$fileCon=<<<st
<?php	
	class Admin {
		
		private static \$instance;
		
		//单例模式
		public static function getInstance(){
			if(!(self::\$instance instanceof self)){
				self::\$instance = new self();
			}
			return self::\$instance;
		}
	}
st;
				self::touch(APP_PATH."Models".DIRECTORY_SEPARATOR."Admin.class.php", $fileCon);

				//创建默认数据库配置文件
				$fileCon=<<<st
<?php
	\$db = array(
		'dbtype'=>'mysql',
		'host'=>'localhost',
		'dbname'=>'test',
		'username'=>'root',
		'password'=>'',
		'tableprev'=>''
	);
	return \$db;
st;
				self::touch(APP_CONF_PATH."db.inc.php", $fileCon);

				//生成构建文件
				$fileCon=<<<st
<?xml version="1.0" encoding="UTF8"?>
<project name='Done' default='build' basedir='./'>
	<target name='prepare'>
		<echo>利用phing+svn+phpunit构建部署PHP项目,您需先全局安装phing,svn和phpunit</echo>
	</target>
	<target name='get' depends='prepare'>
		<svnupdate svnpath='svn仓库' username="svn用户名" password="svn密码" todir="将从svn仓库拷贝到该目录" />
	</target>
	<target name='test' depends='get' description='Run PHPUnit tests'>
		<coverage-setup database="\${project.basedir}/report/coverage/coverage.db">
		<fileset dir="\${project.basedir}/Test">
			<include name="**/*Test.php" />
			<include name="*Test.php" />
		</fileset>
		</coverage-setup>
		<phpunit codecoverage="true" failureproperty='failure.unittest'>
			<formatter	type='xml' todir="\${project.basedir}/Report/logs" outfile="test.unit.report.xml" />
			<batchtest>
				<fileset dir="\${project.basedir}/Test">
				<include name="**/*Test.php" />
				<include name="*Test.php"/>
				</fileset>
			</batchtest>
		</phpunit>
		<!-- <phpunitreport styledir='C:\Users\Administrator\AppData\Roaming\Composer\vendor\phing\phing\etc' format="frames" infile="\${project.basedir}/report/logs/test.unit.report.xml"  todir="\${project.basedir}/report/test"/> -->
		<!-- <coverage-report outfile="\${project.basedir}/report/coverage.xml">
		<report styledir="C:\Users\Administrator\AppData\Roaming\Composer\vendor\phing\phing\etc" todir="\${project.basedir}/report/coverage" />
		</coverage-report> -->
	</target>
	<target name='build' depends='test'>
		<echo message= "start building" />
	</target>
</project>
st;
				self::touch(APP_BUILD_PATH, $fileCon);

				//生成单元测试用例1
				$fileCon=<<<st
<?php 
	class ClassOneTest extends PHPUnit_Framework_TestCase{
	function setUp(){
		\$this->obj = new ClassOne();
	}
	function test***(){
		\$this->assertEquals("***",\$this->obj->***());
	}
	function tearDown(){
		unset(\$this->obj);
	}
}
st;
				self::touch(APP_TEST_PATH."ClassOneTest.php",$fileCon);

//生成单元测试用例2
				$fileCon=<<<st
<?php 
	class ClassTwoTest extends PHPUnit_Framework_TestCase{
	function setUp(){
		\$this->obj = new ClassTwo();
	}
	function test***(){		
		\$this->assertEquals("***",\$this->obj->***());
	}
	function tearDown(){
		unset(\$this->obj);
	}
}
st;
				self::touch(APP_TEST_PATH."ClassTwoTest.php", $fileCon);

				//生成单元测试集成执行文件
				$fileCon=<<<st
<?php
	//方法一：用phpunit执行
	class TestSuite extends PHPUnit_Framework_TestSuite{
		function __construct(){
			\$this->addTestFile('ClassOneTest.php');
			\$this->addTestFile('ClassTwoTest.php');
		}
		static function suite(){
			return new self();
		}
	}
	//方法二：用PHP执行
	// \$testsuite = new PHPUnit_Framework_TestSuite();
	// \$testsuite->addTestFile('ClassOneTest.php');
	// \$testsuite->addTestFile('ClassTwoTest.php');
	// PHPUnit_TextUI_TestRunner::run(\$testsuite);
st;
				self::touch(APP_TEST_PATH."TestSuite.php",$fileCon);



				//创建文件锁
				self::touch($structFile, implode("\n", self::$mess));
				
			}	
			self::runtime();
		}


		/**
		 * 父类控制器的生成
		 * @param	string	$srccontrolerpath	原基类控制器的路径
		 * @param	string	$controlerpath		目标基类控制器的路径
		 */ 
		static function commonControler($srccontrolerpath,$controlerpath){
			$srccommon=$srccontrolerpath."common.class.php";
			$common=$controlerpath."common.class.php";
			//如果新控制器不存在， 或原控制器有修改就重新生成
			if(!file_exists($common) || filemtime($srccommon) > filemtime($common)){

				//将控制器类中的内容读出来
				$classContent=file_get_contents($srccommon);	

				if(preg_match('/extends\s+(.+?)\s*{/i',$classContent, $matches)){					
					if($matches[1] == "Action"){
						$classContent=preg_replace('/extends\s+Action\s*{/i','extends \Done\Lib\Core\Action {',$classContent,1);
						//新生成控制器类
						file_put_contents($common, $classContent);
					}
				}else{
					copy($srccommon, $common); 
				}
					
			}	
		}

		static function controler($srccontrolerfile,$controlerpath,$m){
			$controlerfile=$controlerpath.ucfirst(strtolower($m))."Action.class.php";
			//如果新控制器不存在， 或原控制器有修改就重新生成
			if(!file_exists($controlerfile) || filemtime($srccontrolerfile) > filemtime($controlerfile)){
				//将控制器类中的内容读出来
				$classContent=file_get_contents($srccontrolerfile);	
				//看类中有没有继承父类

				//如果已经有父类
				if(preg_match('/class\s+(.+?)\s+extends\s+(.+?)\s*{/i',$classContent, $matches)){					
					if($matches[2] == "Action"){
						$classContent=preg_replace('/class\s+(.+?)\s+extends\s+(.+?)\s*{/i','class '.$matches[1].' extends \Done\Lib\Core\Action {',$classContent,1);
					}else{
						$classContent=preg_replace('/class\s+(.+?)\s+extends\s+(.+?)\s*{/i','class '.$matches[1].'Action extends '.$matches[2].' {',$classContent,1);
					}
					//新生成控制器类
					file_put_contents($controlerfile, $classContent);
				//没有父类时
				}else{ 
					//继承父类Common
					$classContent=preg_replace('/class\s+(.+?)\s*{/i','class \1Action extends Common {',$classContent,1);
					//生成控制器类
					file_put_contents($controlerfile,$classContent);	
				}
			}
		}

		static function model($className, $app){
			if($app==""){
				$src=APP_PATH."Models/".$className.".class.php";
				$psrc=APP_PATH."Models/___.class.php";
				$className=$className.'Model';
				$parentClass='___Model';
				$to=APP_MODELS_PATH.APP_NAME.DIRECTORY_SEPARATOR.$className.".class.php";
				$pto=APP_MODELS_PATH.APP_NAME.DIRECTORY_SEPARATOR.$parentClass.".class.php";
				
			}else{
				$src=PROJECT_PATH.$app."/Models/".strtolower($className).".class.php";
				$psrc=PROJECT_PATH.$app."/Models/___.class.php";
				$className=ucfirst($app).$className.'Model';
				$parentClass=ucfirst($app).'___Model';
				$to=APP_MODELS_PATH.APP_NAME.DIRECTORY_SEPARATOR.$className.".class.php";
				$pto=APP_MODELS_PATH.APP_NAME.DIRECTORY_SEPARATOR.$parentClass.".class.php";
			}		
			//如果有原model存在
			if(file_exists($src)) {	
				$classContent=file_get_contents($src);
				//如果已经有父类
				if(preg_match('/extends\s+(.+?)\s*{/i',$classContent, $arr)) {
					$psrc=str_replace("___", $arr[1], $psrc);
					$pto=str_replace("___", $arr[1], $pto);
					
					if(file_exists($psrc)){
						if(!file_exists($pto) || filemtime($psrc) > filemtime($pto)){
							$pclassContent=file_get_contents($psrc);
							$pclassContent=preg_replace('/class\s+(.+?)\s*{/i','class '.$arr[1].'Model extends '.ucfirst(strtolower(DRIVER)).' {',$pclassContent,1);			
							file_put_contents($pto, $pclassContent);			
						}
				
					}else{
						Debug::addmsg("<font color='red'>文件{$psrc}不存在!</font>");
					} 

					$pModel=$arr[1]."Model";
					include_once $pto;
				}else{
					$pModel = "Done\Lib\Core\\".ucfirst(strtolower(DRIVER));
				}

				if(!file_exists($to) || filemtime($src) > filemtime($to) ) {	
						$classContent=preg_replace('/class\s+(.+?)\s*{/i','class '.$className.' extends '.$pModel.' {',$classContent,1);
						//生成model
						file_put_contents($to,$classContent);
					}	
				
			}else{
				if(!file_exists($to)){

					$classContent="<?php\n\tclass {$className} extends Done\Lib\Core\\".ucfirst(strtolower(DRIVER))."{\n\t}";
					//生成model
					file_put_contents($to,$classContent);	
				}	
			}

			include_once $to;
			return $className;
		}

	}
