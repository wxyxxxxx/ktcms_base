<?php
// if (!defined('in_mx')) {exit('Access Denied');}

/*开天CMS系统安装*/

define(TABLEPRE, "yec_");

if(!file_exists('ktcms.lock'))
{
	$is_lock=0;	
dump(ROOT_PATH);exit;
	switch ($step) {
		case 1:
			if($act=='get_envinfo')
			{
				$env =array();
				$env['system'] = array("name"=>'操作系统',"require"=>'不限制',"recom"=>"Linux","status"=>1,"val"=>PHP_OS);
				$env['ver'] = array("name"=>'PHP版本',"require"=>'5.4',"recom"=>"7.0","status"=>(PHP_VERSION>='5.4' ? 1:0),"val"=>PHP_VERSION); 
				
				$maxsize = get_cfg_var("upload_max_filesize");
				$env['maxsize']=array("name"=>'附件上传',"require"=>'2M',"recom"=>"5M");
				$env['maxsize']['status'] =($maxsize && $maxsize>=2) ? 1 : 0;
				$env['maxsize']["val"] = $maxsize ? $maxsize : "不允许上传";
				
				$env['gd']=array("name"=>'GD库',"require"=>'1.0',"recom"=>"2.0");
				if(defined("GD_VERSION"))
				{
				 	$env['gd']['status'] =1;
				 	$env['gd']['val'] =GD_VERSION;
				}
				else {
					$env['gd']['status'] =0;
				 	$env['gd']['val'] ="未开启GD";
				}
				
				$install_dir =array(); 
				$check_dir[]=ROOT_PATH;

				$check_dir =array('cache','upload','config');
				foreach ($check_dir as $k => $v) {
					$install_dir[$k]['name'] = $v;
					$install_dir[$k]['status'] = is_writeable($v) ? 1:0;
				}
				$data['env']=$env;
				$data['installdir']=$install_dir;
				die(json_encode($data));
			}
			break;
		case 2:
			$host_info = explode(":", $dbconfig['host']);
			$dbconfig['host'] = $host_info[0];
			$dbconfig['port'] = count($host_info)==2 ? $host_info[1] :'3306';
			if($act=='checkinfo')
			{			
				$res = pdo_ping($dbconfig);
				die(json_encode($res['err']));
			}
			elseif($act=='install')
			{
	
				$res = array('err' => '', 'res' => '', 'data' => array());
				$tablepre = $dbconfig['dbpre'];
				$res = pdo_ping($dbconfig);
				if($res['err']!= 'ok')
				{
					$res['err'] ="数据库连接失败，请检查";
					die(json_encode($res));
				}
				
				$db = $res['res'];
							
				$sql =read_file("install/data/ktcms.sql");						
				runSql($sql, $tablepre);
				
				// $sql_data =read_file("install/data/yec_data.sql");
				// runSql($sql_data, $tablepre);
									
				$config_con =read_file("../application/database.php");
				// $config_con = str_replace('{ym_token}', get_salt(16), $config_con);
				$config_con = str_replace('{dbhost}', $dbconfig['host'], $config_con);
				$config_con = str_replace('{dbport}', $dbconfig['port'], $config_con);
				$config_con = str_replace('{dbuser}', $dbconfig['uname'], $config_con);
				$config_con = str_replace('{dbpw}', $dbconfig['upwd'], $config_con);
				$config_con = str_replace('{dbname}', $dbconfig['dbname'], $config_con);
				$config_con = str_replace('{tablepre}', $dbconfig['dbpre'], $config_con);
				write_file("../application/database.php", $config_con);
				write_file("ktcms.lock", "");
				
				$salt = get_salt();
				$pwd = encryptStr($dbconfig['admpwd'], $salt);
				runSql("update ".$tablepre."user set username='".trim($dbconfig['admname'])."',pwd='".$pwd."',salt='".$salt."',status=1,addtime=".time()." where id=1",$tablepre);	
				$res['err'] = '';
				$res['res'] = 'ok';
				$_SESSION['ym_intalled'] =1;
				die(json_encode($res));
			}	
			break;
			
			default:		
			break;
	}

}
else {
	$is_lock=1;			
}

/*检查连接是否可用*/
function pdo_ping($config){
    try{
    	
    	$db = new PDO("mysql:host=".$config['host'].";port=".$config['port'].";", $config['uname'], $config['upwd'], array(PDO::ATTR_PERSISTENT => false));  //dbname=.$config['dbname']  
		$db->query('SET NAMES utf8mb4');
		$db->query('create database '.$config['dbname']);
		$db = new PDO("mysql:host=".$config['host'].";port=".$config['port'].";dbname=".$config['dbname'], $config['uname'], $config['upwd'], array(PDO::ATTR_PERSISTENT => false));  //  
		$db->query('SET NAMES utf8mb4');
		$res['err'] ='ok';
		$res['res'] =$db;
    } 
    catch (PDOException $e) {
    	$res['err'] = $e->getMessage();
		$res['res'] ='';
    }
    return $res;
}

function runSql($sql, $tablepre) {
	global $db;

	if(!isset($sql) || empty($sql)) return;
	
	$sql = str_replace("\r", "\n", str_replace(' '.TABLEPRE, ' '.$tablepre, $sql));
	$sql = str_replace(' `'.TABLEPRE, ' `'.$tablepre, $sql);
	$ret = array();
	$i = 0;
	foreach(explode(";\n", trim($sql)) as $q) {
		$ret[$i] = '';
		$qs = explode("\n", trim($q));
		foreach($qs as $s) {
			$ret[$i] .= (isset($s[0]) && $s[0] == '#') || (isset($s[1]) && isset($s[1]) && $s[0].$s[1] == '--') ? '' : $s;
		}
		$i++;
	}
	unset($sql);

	//$res['done']= 0;
	foreach($ret as $q) {
		$q = trim($q);// print $q."<br>";
		if($q) {
			if(substr($q, 0, 12) == 'CREATE TABLE'){
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $q);
				//$res['msg']="创建表 ".$name." 成功";
				//showAjaxMsg($res);
				$db->query($q);
			} else {
				$db->query($q);
			}
		}
	}

}

/*if($act=='test')
{				
	for ($i=0; $i < 20; $i++) {
		//sleep(1);
		showAjaxMsg("创建表 ".$i." 成功<br>");
	}
}*/

function showAjaxMsg($res)
{
	echo json_encode($res);// json_encode($res);
	 // send headers to tell the browser to close the connection
	 header("Content-Length: ".ob_get_length());
	 header('Connection: close');
	 ob_end_flush();
	
	ob_flush();
	flush();
	 
	usleep(100000);//100微秒
	
	ignore_user_abort(true);//在关闭连接后，继续运行
	set_time_limit(0); //不设置超时时间
}

$title ="开天CMS建站系统 v1.0 安装向导";
@include template("install", "install/", 1);
die();
?>