<?php
$mysql_table_name = "deferredjob_log"; //要存储备份记录的表名
$bakphp_dir = "mysqlbak/";  //你把备份的php 放在哪个目录了 根目录请为空 不要加/;
$bak_Storagename = "mysqlbakStoragename";  //备份下来的数据库放在哪个Storag 中,一定要是一个公开是的Storag
$sqlbak_key = "okklkjaksjdk"; // 这可以一段随意的字符串,只有知道此字符串才能执行添加备份任务 (字母加数字组合)

/*
注意:自动删除七天前备份,可以在下面配置更长. 定时任务方式大概会在第二天的1点左右开始备份

此脚本运行演示:
1.创建表 执行  http://my.sinaapp.com/mysqlbak/sqlbak.php?install=1
  这里的 mysqlbak 是你配置的  $bakphp_dir 目录

2. 可以手动执行访问app url 执行备份;
    http://my.sinaapp.com/mysqlbak/sqlbak.php?sqlbak=okklkjaksjdk
	这里的 "okklkjaksjdk" 是配置 的 $sqlbak_key 值;

3. 或者你可以加入cron 定时执行;
   示例: config.yaml 配置 (每天16:30添加定时备份任务;大概会在第二天凌晨1点左右执行备份)
   cron:
    - description: mysqlbak
    url: mysqlbak/sqlbak.php?sqlbak=okklkjaksjdk
    schedule: every day of month 16:30


参考资料:
	SaeDeferredJob API : http://sae.sina.com.cn/?m=devcenter&catId=196
	sae Cron :           http://sae.sina.com.cn/?m=devcenter&catId=195#anchor_981f67ed22c70ce0e39996ea82c80916
	sae mysql:			 http://sae.sina.com.cn/?m=devcenter&catId=192
	sae Storage :		 http://sae.sina.com.cn/?m=devcenter&catId=204

你还可以继续增加从Storage 推到你的网盘中,以免消耗云豆.

为防止数据库全量备份消耗大量云豆,本程序设置了自动删除七天前备份功能,如果不需要要可以自己调整时间

定时任务什么的都能在sae 看到;
备份下载地址也能在sae 看到DeferredJob 里面;

*/

$dj = new SaeDeferredJob();
/* 连主库 */
$link=mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);

if(isset($_GET["install"])){ //执行新建表
	$sql = "
	CREATE TABLE ".$mysql_table_name." (
 	 	id int(8) NOT NULL COMMENT '任务id',
  		sqlname varchar(50) DEFAULT NULL COMMENT '备份文件名',
  		addtime datetime DEFAULT NULL COMMENT '任务添加时间',
  		stat int(1) DEFAULT NULL COMMENT '状态码:3备份成功;-2为已删除; 其它参考SaeDeferredJob API使用介绍',
 	 	overtime datetime DEFAULT NULL COMMENT '完成时间'
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$mysql_table_name."'")==1)){
		echo "数据表已存在,请确认.";	
	}else{
		if($rql = mysql_query($sql,$link)){
			echo "数据表新建成功";	
		}else{
			echo "数据库新建失败,请刷新重试";	
		}
	}
}

if( isset($_GET["sqlbak"]) && $_GET["sqlbak"] === $sqlbak_key){
	 //添加任务
	 $nowtime = date("YmdHms");
	 $sqlname = $nowtime.SAE_MYSQL_DB."bak.sql.zip";
	 $callbackurl = $bakphp_dir."sqlbak.php?sqlbakcallback=".$nowtime;
	 $taskID=$dj->addTask("export","mysql",$bak_Storagename,$sqlname,SAE_MYSQL_DB,NULL,$callbackurl);
	 if($taskID===false){
		 var_dump($dj->errno(), $dj->errmsg());
	 }else{
		echo "备份任务添加成功,任务id:".$taskID;
		mysql_select_db(SAE_MYSQL_DB,$link);
		$sql = "INSERT INTO `".$mysql_table_name."` (`id`, `sqlname`, `addtime`, `stat`, `overtime`) VALUES ('".$taskID."', '".$sqlname."', now(), '0', '');";
        if($link && $rql = mysql_query($sql,$link))
		{
			echo "入库成功.";	
		}else{
			echo "入库失败";
		}
		//测试时顺带删除
		if(isset($_GET["test"]) && $_GET["test"] === "del"){
			$ret=$dj->deleteTask($taskID);
			if($ret===false){
				var_dump($dj->errno(), $dj->errmsg());
			}else{
				echo "删除任务完成";
				$sql = "UPDATE  `".$mysql_table_name."` SET  `stat` = '-1' , `overtime` = NOW() WHERE `deferredjob_log`.`id` ='".$taskID."'";
				if($link && $rql = mysql_query($sql,$link))
				{
					echo "修改记录成功.";		
				}else{
					echo "修改记录失败";
				}
			}
		}
	 }
	 
}
if( isset($_GET["sqlbakcallback"])){
	//查看状态
	$sql ="SELECT * FROM  `".$mysql_table_name."` WHERE `sqlname` LIKE  '%".$_GET["sqlbakcallback"]."%' LIMIT 1";
	mysql_select_db(SAE_MYSQL_DB,$link);
	if($link && $rql = mysql_query($sql,$link))
	{
		$row = mysql_fetch_row($rql);
		$ret=$dj->getStatus($row["0"]);
		echo $ret;
		if($ret===false){
			$start = -1 ;
			//var_dump($dj->errno(), $dj->errmsg());
		}else{
			switch($ret){
				case "waiting" :
					$start = 0 ;
					break ;	
				case "inqueue" :
					$start = 1 ;
					break ;
				case "delete" :
					$start = 2 ;
					break ;
				case "excuting" :
					$start = 3 ;
					break ;
				case "done" :
					$start = 4 ;
					break ;
				case "abort" :
					$start = -2 ;
					break ;	
				default:
					$start = 0 ;
					break ;
			}	
		}
		$sql = "UPDATE `".$mysql_table_name."` SET  `stat` = '$start' , `overtime` = NOW() WHERE `".$mysql_table_name."`.`id` ='".$row["0"]."'";
		if($rql = mysql_query($sql,$link)){
			echo $row["0"]."入库成功.";
			$sql = "SELECT DISTINCT * FROM  `".$mysql_table_name."` WHERE  `stat` = 3 ORDER BY  `id` DESC LIMIT 7 , 30"; //删除7天前成功备份 可以在这里设置留多少天的备份
			if($rqlold = mysql_query($sql,$link)){
				$s = new SaeStorage();
				while($rowold=mysql_fetch_array($rqlold,MYSQL_ASSOC)){
					$dellstat = $s->delete($bak_Storagename,$rowold["sqlname"]);
					if($dellstat){
						$sql = "UPDATE `".$mysql_table_name."` SET  `stat` = '-2' , `overtime` = NOW() WHERE `".$mysql_table_name."`.`id` ='".$rowold["id"]."'";
						if(mysql_query($sql,$link)){
							echo "删除".$rowold["overtime"]."成功";
							echo "<br>";
						}else{
							echo "删除".$rowold["overtime"]."成功";
							echo "入库失败";
						}
					}else{
						echo "删除".$rowold["overtime"]."失败";
						echo "<br>";
					}	
				}
			}
			
		}else{
			echo $row["0"]."入库失败2";	
		}
	}else{
		echo $_GET["sqlbakcallback"]."入库失败";
	}	
}

mysql_close($link);
?>