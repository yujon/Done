<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>提示消息</title>

		<style type="text/css">
			body { background:#a8b7c5; font-family:"microsoft yahei"; }
			#notice { width: 620px; height:320px; background: #FFF; border: 5px solid #dae3eb;
			position: absolute; left: 50%; top: 50%; margin-left: -312px; margin-top: -157px; }
			#notice .title{height:30px; line-height:25px; text-indent:3px;  background:#dae3eb;color:#3a3a3a;font-size:14px;}
			#notice .content{height:240px;padding:10px;}
			#notice .content .sign{width:216px;height:204px;line-height:204px;text-align:center;float:left;font-size:180px; font-weight:bolder;}
			#notice .content .mess{width:380px;height:204px;float:left;line-height:20px;font-size:14px;}
			#notice .bot{height:30px;line-height:30px;text-align:center;font-size:12px;}
			#notice p { background: #FFF; margin: 0; padding: 0 0 20px; }
			a { color: #f00} a:hover { text-decoration: none; }
			
		</style>
	</head>
	<body>
    	<div id="notice">
        	<div class="title">网站消息</div>
            <div class="content">
            	<div class="sign"><{if $mark}><span style="color:green;">√</span><{else}><span style="color:red">×</span><{/if}>	</div>
                <div class="mess"><{$mess}></div>
            </div>
            <div class="bot">
            	在( <span id="sec" style="color:blue;font-weight:bold"><{$timeout}></span> )秒后自动跳转，或直接点击 <a href="javascript:<{$location}>">这里</a> 跳转
            </div>
        </div>
		
						
		 <script>
		 		var seco=document.getElementById("sec");
				var time=<{$timeout}>;
				var tt=setInterval(function(){
						time--;
						seco.innerHTML=time;	
						if(time<=0){
							<{$location}>
							return;
						}
					}, 1000);
				function stop(obj){
					clearInterval(tt);
					obj.style.display="none";
				}
		</script>
	</body>
</html>
