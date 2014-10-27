<?php
	$channel = new SaeChannel();
	//$token = $channel->createChannel('test');
	//$message_content = 'hello,sae';
	// Send message
	$redata = array();
	if(!isset($_POST["msg"]) || empty($_POST["msg"])){
		$redata["code"] = 0;
		echo json_encode($redata);
		exit();	
	}
	$ip = GetIP();
	$ip_arr= explode('.', $ip);
  	$ip_arr[3]='*';
  	$ip= implode('.', $ip_arr);
	$ret = $channel->sendMessage('feiyang',htmlspecialchars($ip.":".$_POST["msg"]));
	
	$redata["txt"] = $ret;
	if($ret){
		$redata["code"] = 1;
	}else{
		$redata["code"] = 0;	
	}
	echo json_encode($redata);
	
	$c = new SaeCounter();
	$c->create('channel'); //创建计数器c1 创建成功返回true 如果该名字已被占用将返回false
	//$c->set('channel',100); // 返回true
	$key = $c->incr('channel'); // 返回101
	$kv = new SaeKV();
	// 初始化SaeKV对象
	$ret = $kv->init();
	$kv->add("channel_".($key-1),htmlspecialchars($_POST["msg"]));
	
	function GetIP(){ 
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) 
		$ip = getenv("HTTP_CLIENT_IP"); 
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) 
		$ip = getenv("HTTP_X_FORWARDED_FOR"); 
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) 
		$ip = getenv("REMOTE_ADDR"); 
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) 
		$ip = $_SERVER['REMOTE_ADDR']; 
		else 
		$ip = "unknown"; 
		return($ip); 
	}
?>