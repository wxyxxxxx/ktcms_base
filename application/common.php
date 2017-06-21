<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
require_once("data.php");
 function ejson($code,$msg,$data='')
{
	echo json_encode(['msg'=>$msg,'code'=>$code,'data'=>$data]);exit;
}

function wlog($arr){
	$arr=json_encode($arr);
	echo "<script>console.log(".$arr.")</script>";
}


function unicode_decode($name){
 
  $json = '{"str":"'.$name.'"}';
  $arr = json_decode($json,true);
  if(empty($arr)) return '';
  return $arr['str'];
}


function is_json($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
}


function get_data_from($data_from){
	$arr=$data_from;
	$new_arr=[];
	if(strpos($arr,':')){

	    $arr2=explode(';', rtrim($arr,';'));

	    $new_arr=[];
	     
	    foreach ($arr2 as $key => $e) {
	    	$arr3=explode(':', $e);
	    	$new_arr[]=['id'=>$arr3[0],'name'=>$arr3[1]];
	    	
	    }


	}else{
		$arr2=explode('|',$arr);

		if (isset($arr2[1])) {
			$arr3=explode(',', $arr2[1]);
		}else{
			$arr3=array();
		}
		// dump($data_from);
	    $new_arr=call_user_func_array($arr2[0],$arr3);
	}
	return $new_arr;
}

function filter_input_data($data,$fields){
	
	foreach ($fields as $key => $e) {
		if ($e['is_allow_null']==1) {
			if (!isset($data[$e['field']])||empty($data[$e['field']])) {
				ejson(-1,$e['name'].'不能为空');
			}
		}
		if ($e['edit_type']==7||$e['edit_type']==5||$e['edit_type']==16) {
			if(isset($data[$e['field']])){
				$data[$e['field']]=implode(',', $data[$e['field']]);
			}else{
				$data[$e['field']]='';
			}
						
		}
		if ($e['edit_type']==17) {
			$data[$e['field']]=md5($data[$e['field']]);
		}

		if ($e['edit_type']==9) {

			$data[$e['field']]=strtotime($data[$e['field']]);
		}
		if ($e['edit_type']==12||$e['edit_type']==13) {
			$data[$e['field']]=rtrim($data[$e['field']],',');
			$data[$e['field']]=ltrim($data[$e['field']],',');
		}
	}
	// dump($data);exit;
	return $data;
}


function get_list_show($str,$data_from){
	$arr=get_data_from($data_from);
	$str=explode(',', $str);
	$res='';

	foreach ($arr as $key2 => $e) {
		foreach ($str as $key => $n) {
			if ($e['id']==$n) {
				$res.=$e['name'].',';
			}
		}

	}
	$res=rtrim($res,',');
	if ($res=='') {
		$res='——————';
	}
	return $res;
}

function get_menu($menu_id=0){
	return db("sys_menu")->where("id",$menu_id)->find();
}
function get_model($model_id=0){
	return db("sys_model")->where("id",$model_id)->find();
}

function get_fields($model_id=0,$where){
	return db("sys_fields")->where("model_id",$model_id)->where($where)->order("sort asc")->select();
}
function get_sys_config(){
	return db("sys_config")->where("status",1)->find();
}
function get_sys_set(){
	return db("sys_set")->where("status",1)->find();
}
// function get_sys_operation(){
// 	return db("sys_operation")->select();
// }


function send_sms($type,$mobile="18501992404"){


	//创蓝接口参数
	$url='http://sms.253.com/msg/send';
	
	//创蓝短信余额查询接口URL, 如无必要，该参数可不用修改
	// const API_BALANCE_QUERY_URL='http://sms.253.com/msg/balance';
	
	$un='N6808839';//创蓝账号 替换成你自己的账号
	
	$pw='c7a7c4bf';//创蓝密码 替换成你自己的密码

	$code=rand(1000,9999);
	switch ($type) {
		case '1':
			session("reg_arr",['phone'=>$mobile,'code'=>$code,'c_time'=>time()]);
			$msg="#感谢您注册茶叶网，你的验证码是：".$code;
			break;
		case '2':
			session("edit_pwd",['phone'=>$mobile,'code'=>$code,'c_time'=>time()]);
			$msg="～您正在修改密码，你的验证码是:".$code;
			break;
		default:
			# code...
			break;
	}

	$postArr = array (
	                  'un' => $un,
	                  'pw' => $pw,
	                  'msg' => $msg,
	                  'phone' => $mobile,
	                  'rd' => 1
	             );

	$postFields = http_build_query($postArr);
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
	$result = curl_exec ( $ch );
	curl_close ( $ch );
	// dump($result);
	return $result;
}


function getIP()  
{  
if (@$_SERVER["HTTP_X_FORWARDED_FOR"])  
$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];  
else if (@$_SERVER["HTTP_CLIENT_IP"])  
$ip = $_SERVER["HTTP_CLIENT_IP"];  
else if (@$_SERVER["REMOTE_ADDR"])  
$ip = $_SERVER["REMOTE_ADDR"];  
else if (@getenv("HTTP_X_FORWARDED_FOR"))  
$ip = getenv("HTTP_X_FORWARDED_FOR");  
else if (@getenv("HTTP_CLIENT_IP"))  
$ip = getenv("HTTP_CLIENT_IP");  
else if (@getenv("REMOTE_ADDR"))  
$ip = getenv("REMOTE_ADDR");  
else  
$ip = "Unknown";  
return $ip;  
} 