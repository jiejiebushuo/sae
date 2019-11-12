<?php
/**
 * php文件缓存类 FileCache<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130513
 * @package
 * @example
 * <pre>
 * $oFC = new CFileCache('./tmp/'); //创建文件缓存类
 * $sKey = 'ab_123'; //缓存键值
 * $data = $oFC->get($sKey); //取得缓存
 * if(is_null($data))
 *   $oFC->set($sKey, array('name'=>'ttt', 'datetime'=>date('Y-m-d H:i:s')), 10); //缓存不存在创建缓存
 * print_r($data);
 * </pre>
 */
final class CFileCache
{
	/**
	 * 缓存目录
	 * @var string
	 */
	private static $msCachePath  = './cache/';
	/**
	 * 默认缓存失效时间(1000小时)
	 * @var int
	 */
	const miEXPIRE = 3600000;
 
 
	/**
	 * 构造<br />
	 * self::$msCachePath 缓存目录为共享目录
	 * @param string $sCachePath
	 */
	function __construct($sCachePath='./cache/')
	{
		if (is_null(self::$msCachePath))
			self::$msCachePath = $sCachePath;
	}
 
	/**
	 * 读取缓存<br />
	 * 返回: 缓存内容,字符串或数组；缓存为空或过期返回null
	 * @param string $sKey 缓存键值(无需做md5())
	 * @return string | null
	 * @access public
	 */
	public function get($sKey)
	{
		//echo $sKey;
		if(empty($sKey))
			return false;
 
		$sFile  = self::getFileName($sKey);
		//echo $sFile;
		if(!file_exists($sFile)){
			//echo 099999;
			return null;
		}
		else
		{
			$handle = fopen($sFile,'rb');
			//echo 999;
			if (intval(fgets($handle)) > time())//检查时间戳
			{	//未失效期，取出数据
				$sData = fread($handle, filesize($sFile));
				fclose($handle);
				return unserialize($sData);
				//echo 3;
			}
			else
			{	//已经失效期
				//echo 5;
				fclose($handle);
				return null;
			}
		}
	}
 
	/**
	 * 写入缓存
	 *
	 * @param string $sKey 缓存键值
	 * @param mixed $mVal 需要保存的对象
	 * @param int $iExpire 失效时间
	 * @return bool
	 * @access public
	 */
	public function set($sKey, $mVal, $iExpire=null)
	{
		//echo 111111;
		if(empty($sKey))
			return false;
 		
 		//echo 88888;
		$sFile = self::getFileName($sKey);
		//echo $sFile;
		if (!file_exists(dirname($sFile)))
			if (!self::is_mkdir(dirname($sFile)))
				return false;
 		
 		//echo 77777;
		$aBuf = array();
		$aBuf[] = time() + ((empty($iExpire)) ? self::miEXPIRE : intval($iExpire));
		$aBuf[] = serialize($mVal);
		/*写入文件操作*/
		$handle = fopen($sFile,'wb');
		fwrite($handle, implode("\n", $aBuf));
		fclose($handle);
		return true;
	}
 
	/**
	 * 删除指定的缓存键值
	 *
	 * @param string $sKey 缓存键值
	 * @return bool
	 */
	public function del($sKey)
	{
		if(empty($sKey))
			return false;
		else
		{
			@unlink(self::getFileName($sKey));
			return true;
		}
	}
 
	/**
	 * 获取缓存文件全路径<br />
	 * 返回: 缓存文件全路径<br />
	 * $sKey的值会被转换成md5(),并分解为3级目录进行访问
	 * @param string $sKey 缓存键
	 * @return string
	 * @access protected
	 */
	private static function getFileName($sKey)
	{
		if(empty($sKey))
			return false;
 
		$key_md5 = md5($sKey);
		$aFileName = array();
		$aFileName[]  = rtrim(self::$msCachePath,'/');
		$aFileName[]  = $key_md5{0} . $key_md5{1};
		$aFileName[]  = $key_md5{2} . $key_md5{3};
		$aFileName[]  = $key_md5{4} . $key_md5{5};
		$aFileName[]  = $key_md5;
		return implode('/', $aFileName);
	}
 
	/**
	 * 创建目录<br />
	 *
	 * @param string $sDir
	 * @return bool
	 */
	private static function is_mkdir($sDir='')
	{
		if(empty($sDir))
			return false;
 
		/*如果无法创建缓存目录，让系统直接抛出错误提示*/
		if(!mkdir($sDir, 0766, true))
			return false;
		else
			return true;
	}
}
