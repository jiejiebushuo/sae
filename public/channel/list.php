<?php
	$c = new SaeCounter();
	//$c->create('channel'); //创建计数器c1 创建成功返回true 如果该名字已被占用将返回false
	//$c->set('channel',100); // 返回true
	$key = $c->get('channel'); // 返回101
	$kv = new SaeKV();
	// 初始化SaeKV对象
	$ret = $kv->init();
	// 一次获取多个key-values
	$keys = array();
	for($i=$key-10;$i<$key;$i++){
		array_push($keys,"channel_".$i);
	}
	$redata = array();
	if($ret && $rets = $kv->mget($keys)){
		$redata["code"] = 1;
		$redata["list"] =  $rets;
	}else{
		$redata["code"] = 0;
		$redata["list"] = "";	
	}
	echo json_encode($redata);
?>