# Done
一个简易的PHP MVC框架（基于Smarty模板引擎）。包括目录文件生成、单一入口、路由解析，类文件自动加载、运行时编译，数据模型ORM，表字段数据页面本地缓存memcache缓存等
***
### 1.使用说明：
	1.新建工程目录，将本框架文件拷贝到API目录下
	2.工程目录下新建index.php入口文件，定义应用名称APP_NAME,引入框架入口文件（include './Simplify/Simplify.php')
	3.多应用则新建多个入口文件,定义不同的APP_NAME，操作同2
	3.开启服务器，分别访问入口文件,生成目录结构
<center>![](http://i.imgur.com/k6oX4t2.png)</center>

<center>![](http://i.imgur.com/TdJNoXw.png)</center>

- Classes 工具类库目录<br>
- Common 通用函数目录 <br>
	function.inc.php 存放全局通用函数<br>
- Conf	配置文件目录<br>
	db.inc.php 数据库连接配置文件 可修改<br>
- Done 框架<br>
- Home 应用目录<br>
	Controls 控制器<br>
	Models 模型<br>
	Views视图<br>
- Lang 语言文件<br>
- Public 公共静态文件目录<br>
	uploads：上传文件目录
	css|js、iamges:公共css、js、图片
- Report 测试结果目录<br>
	coverage：代码覆盖率文件
	logs:测试报告
- TEST 测试文件目录<br>
	ClassOneTest.php 测试用例样例1
	ClassTwoTest.php 测试用例样例2
	TestSuite.php 测试组件样例
- build.xml phing自动化构建项目入口文件<br>
- Runtime 运行时文件目录
	Cache：页面静态化缓存
	Controls:控制器目录
	Data:数据缓存目录
	Fields:表结构缓存目录
	Logs:日志文件目录
	Models:数据模型目录
	Templates_c:编译后的模板目录
	_test_index.php：入口文件
- index.php项目入口<br>

>以上文件目录为默认设定的，可通过入口文件定义修改（define)以下常量参数从而改变生成的项目结构<br>

	APP_NAME：应用目录名称
	APP_SKIN_NAME：应用皮肤名称
	APP_TPL_PREFIX：y应用视图后缀，默认为.tpl
    APP_ClASS_PATH :工具类库目录
	APP_COM_PATH：公共方法目录
	APP_CONF_PATH：配置文件目录
	APP_CONF_EXT：配置文件后缀，默认为.inc.php
	APP_LANG_PATH:公共语言目录
	APP_PUBLIC_PATH：公共静态文件目录
	APP_RUNTIME_PATH：运行时文件目录
	APP_CACHE_PATH:模板缓存目录
	APP_CACHE_LIFTTIME：模板缓存时间
	APP_COMPILE_PATH：模板编译后目录
    APP_DATA_PATH：数据缓存目录
	APP_FIELDS_PATH：表结构缓存目录
	APP_LOG_PATH：日志目录
	APP_CONTROLLS_PATH：运行时控制器目录
	APP_MODELS_PATH：运行时模型目录
	APP_TEST_PATH：测试文件目录
	APP_REPORT_PATH：测试结果目录
	APP_BUILD_PATH：自动构建文件目录，默认为API目录下

>此外，我们可以通过设置以下参数控制调试缓存情况
    
    
    DRIVER:数据库驱动，支持mysqli与pdo,默认为pdo
    APP_DEBUG:是否开启调试模式模式,默认为true
	APP_CACHE：是否开启模板静态化存储，默认为false
	APP_COMPILE：是否开启模板编译，默认为true
    APP_MEMCACHE：是否要memcache缓存（确保你已添加Memcache扩展以及开启Memcache服务器）,默认为false
    APP_DATA：是否进行数据缓存,默认为false
    APP_FIELDS：是否要表字段缓存,默认为false

>至此，你已经可以愉快地进行开发了。不过还有一些需要记得的：

1. 确保所有的工具类，控制器，模型类都使用了.class.php后缀，首字母大写
2. 公共方法文件function.inc.php已经引入，不需要再手动引入
3. 已经实现了类文件自动加载，所有类文件定义在其他文件后直接调用即可
4. 控制器以及模型类在运行时进行了简单的封装，封装后置于runtime中，实际访问的是此中文件
5. 控制器如果没有继承其他类，会默认继承common控制器
6. 模型会根据设定的Driver继承相应的驱动类，驱动类封装了链式操作数据库的各种方法
7. 模板引擎使用Smarty,不了解可自定查找学习

### 2.框架说明：
#### 1）目录结构
<center>![](http://i.imgur.com/1737f6k.png)</center>

- Common 公共方法目录
- Extend 扩展工具类
	Upload.class.php：文件件上传类，可以上传一个或同时上传多个文件<br>
	Image.class.php：图像处理类，可以完成对图像进行缩放和加图片水印的操作<br>
	SimFile.class.php：文件操作类<br>
	Page.class.php：分页类，可以自定义分页显示内容<br>
	Validate.class.php：自动验证类，通过解析XML文件实现对表单在服务器端的自动验证<br>
	Vcode.class.php:验证码类，该类的对象能动态获取验证码图片，验证字符串保存在服务器中<br> 
- Lib核心库
	- Core 核心类库<br>
		Action.class.php 所有控制器的基类，实现控制器初始化以及启动<br>
		DB.class.php：所有模型类的基类，封装了一些常用的数据库操作函数<br>
		Debug.class.php:调试模式类，用于在开发阶段调试程序使用<br>
		MemcacheSQL.class.php:内存缓存Memcache类，用于将SQL语句的查询结果缓存在指定服务器内存中<br>
		MemcacheSession.class.php:会话控制Session类，用于将Session数据保存在Memcached服务器中<br>
		MySmarty.class.php：Smarty类的子类，进行了相应扩展
		Mysql.class.php:数据库mysqli驱动类，通过该类使用PHP的mysqli扩展连接处理数据库<br>
		Pdo.class.php:数据库pdo驱动类，通过该类使用PHP的pdo扩展连接处理数据库<br>RewriteUrl.class.php:URL解析类,PATHINFO的格式的URL（静态URL)则提取出control和action以及参数,非PATHINFO的格式的URL则转换成PATHINFO格式再重定向<br>
		Structure.class.php:项目结构部署类，用于自动创建所需要的项目目录和文件结构。
	- Vendor 第三方类库<br>   
- Simplify 框架入口文件

#### 2）公共方法：
    getOS()：获取当前服务器的操作系统
    getIp()：获取客户端的IP
    isMobileRequest()：判断是否为移动端请求
    timeAgo($time)：判断$time具体现在多长时间
    getUUID()：获取唯一id
    cutStr($string, $sublen, $start = 0, $code = 'UTF-8'):汉字截取
    toSize($bytes):文件尺寸转换，将大小将字节转为各种单位大小
    writeLog($type="access",$log)；写访问日志和错误日志
    validateSign($param, $sign)：$param,需要加密的字符串;$sign, 第三方已经机密好的用来比对的字串
    xmlToArray（$xml）:xml转为数组
    arrayToXml（$arr):数组转为xml
    encodeUnicode($str)：unicode编码
    decodeUnicode（$str):unicode解码
    postRequest($api,$data=array(),$post=true,$send="json",$retJson=true,$timeout=30)：发起一个post请求到指定接口
    D($modelName):创建模型对象
    C(fileName,,$dir=null):引入配置文件，默认在配置文件目录

#### 3）数据库操作：
	field（）:所需要获取字段，无参表示获取所有
			//field(array(a,b,c)) 
			//field("a b c")
			//field("a,b,c")
			//field(a,b,c)
	where()：where条件
			//where(array("a"=>0,"b"=>0)) 
			//where(array("a"=>0,"b"=>0),array("c"=>0,"d"=>0)) 
			//innerjoin("a,b,c")
	innerjoin()：内连接
			//rightjoin(array(a,b,c)) 
			//rightjoin("a b c")
			//rightjoin("a,b,c")
			//rightjoin(a,b,c)
	leftjoin（）:左连接
			//fulljoin(array(a,b,c)) 
			//fulljoin("a b c")//fulljoin("a,b,c")
			//fulljoin(a,b,c)			
	rightjoin():右连接
			// on(array("a"=>c,"b"=>d)) 
			// on(array("'a=b','c'='d'"))//on(array("'a=b' 'c'='d'"))
	fulljoin（）:全连接
	on（）：连接条件,同一数组表and，不同数组为or
			// on(array("a"=>c,"b"=>d)) 
			// on(array("'a=b','c'='d'"))
			//on(array("'a=b' 'c'='d'"))			
	order（）:排序
			//order(array(a,b,c)) 
			//order("a b c") //order("a,b,c")
			//order(a,b,c)
	limit（）:数目限制
			//limit(array(a,b)) //limit(array(a))
			//limit("a b") //limit("a,b")
			//limit(a,b)
	group（）：分组
			//group(array(a,b,c)) 
			//group("a b c") //group("a,b,c")
			//group(a,b,c)
	having（）:设置Having条件
			//having("SUM(a)>1") 
	getFields()：获取表结构
	getTables（）:获取数据库所有表
	dbSize()：数据库使用大小
	dbVersion（）：数据库版本
	beginTransaction（）:开启事务
	commit（）:提交事务
	rollBack（）:事务回滚
	








