<?php
$channel = new SaeChannel();
$token = $channel->createChannel('feiyang',3600);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>广播大厅</title>
<style>
	body{width:800px; margin-left:auto; margin-right:auto;}
	#con{  height:500px; padding:10px; overflow:scroll; border:1px solid #ccc;}
	#con div{ border-bottom:1px dashed #CCC; line-height:150%; padding-top:8px; padding-bottom:5px;}
	#xx{ margin-top:10px; margin-bottom:10px;padding:10px; border:1px dashed #09C;}
	#txt{ width:770px; height:50px; padding:10px;}
	#sub{ width:100px; height:30px; font-size:16px; margin-top:10px;}
	#xtxx{ display:inline-block; padding-left:10px; margin-top:10px;}
</style>
<script src="http://libs.baidu.com/jquery/1.9.0/jquery.js"></script>
<script src="http://channel.sinaapp.com/api.js"></script>
</head>
<body>
<script>
	var channel = {
		token:"<?php echo $token; ?>",
		onOpened:function(){
				channel.al("<font color=\"green\">连接服务端成功</font>");
				$.ajax({
					type:"POST",
					url:"list.php",
					data:{
					},
					success:function(data){
						if(data.code){
							for(var i in data.list){
								channel.writer(data.list[i]);
							}
						}else{
							//channel.writer(m.data);	
						}
					},
					dataType:"json"
				});
			},
		onMessage:function(m){
				newState = JSON.parse("\""+m.data+"\"");
				channel.al("<font color=\"green\">有新消息</font>");
				if(document.getElementById("bczx").checked){
					$("#con").scrollTop($("#con div:last").position().top);
				}
				channel.writer("\""+m.data+"\"");
			}, 
		writer:function(txt){
				var box = document.createElement("div");
				box.innerHTML = txt;
				document.getElementById("con").appendChild(box);
			},
		al:function(txt){
				var box = document.getElementById("xx");
				box.innerHTML = txt;
				//document.getElementById("con").appendChild(box);
			},
		up:function(){
				var txt = document.getElementsByName("txt").item(0).value;
				if(txt !=""){
					$.ajax({
						type:"POST",
						url:"up.php",
						data:{
							//token:channel.token,
							msg:""+txt+"",
						},
						success:function(data){
							if(data.code){
								channel.al("<font color=\"green\">消息发送成功</font>");
								$("#con").scrollTop($("#con div:last").position().top);
								document.getElementsByName("txt").item(0).value = "";
							}else{
								channel.al("<font color=\"red\">消息发送失败</font>");		
							}
						},
						dataType:"json"
					});
				}
			}
	};
	var socket = new WebSocket(channel.token);
        socket.onopen = channel.onOpened;
        socket.onmessage = channel.onMessage;
</script>
<div id="con">
<div><span>系统消息:</span><span id="xtxx">广播大厅出现了,不记名,不存储,欢迎吐槽,遵守法律法规感谢大家.</span></div>
</div>
<div class="">
	<div id="xx"></div>
	<input name="txt" id="txt"><br>
    <button type="button" id="sub" onClick="channel.up();">发送</button>
    <label><input type="checkbox" name="bczx" id="bczx"  checked >保持新消息可见.</label>
</div>
<div style="clear:both; margin-top:40px; margin-left:auto; margin-right:auto;">
<!-- 广告位：占位在线 -->
<script type="text/javascript" >BAIDU_CLB_SLOT_ID = "823188";</script>
<script type="text/javascript" src="http://cbjs.baidu.com/js/o.js"></script>
</div>
<div style="display:none;">
<script type="text/javascript">
	var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
	document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3Fa932ed9a565222f076e84c5cf1a0da39' type='text/javascript'%3E%3C/script%3E"));
</script>
</div>
<div>
<h2>提意见,我就用它了.</h2>
<div>
<!--兼容版，可保证页面完全兼容-->
<div id="SOHUCS"></div>
<script>
  (function(){
    var appid = 'cy2uGzAqyhYo',
    conf = 'prod_19bc916108fc6938f52cb96f7e087941';
    var doc = document,
    s = doc.createElement('script'),
    h = doc.getElementsByTagName('head')[0] || doc.head || doc.documentElement;
    s.type = 'text/javascript';
    s.charset = 'utf-8';
    s.src =  'http://assets.changyan.sohu.com/upload/changyan.js?conf='+ conf +'&appid=' + appid;
    h.insertBefore(s,h.firstChild);
  })()
</script>
</div>
</div>
</body>
</html>
