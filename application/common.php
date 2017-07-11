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
use think\Db;
use weixin\WxApi;

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


function get_data_from($data_from,$menu=[],$field_param=''){
	$arr=$data_from;
	$new_arr=[];
	// dump(strpos($arr,'TB|'));exit;
	if(strpos($arr,':')){

	    $arr2=explode(';', rtrim($arr,';'));

	    $new_arr=[];
	     
	    foreach ($arr2 as $key => $e) {
	    	$arr3=explode(':', $e);
	    	$new_arr[]=['id'=>$arr3[0],'name'=>$arr3[1]];
	    	
	    }


	}elseif(strstr($arr,'TB|')){
		$str_arr=explode('|', $arr);
		// dump($str_arr);exit;
		$con_table_name=get_model_table_by_id($str_arr[1]);
		$field=(isset($str_arr[4])&&$str_arr[4]!='')?$str_arr[4]:'*';
		$where=(isset($str_arr[5])&&$str_arr[5]!='')?$str_arr[5]:'1=1';
		$field=explode(',', trim($field,','));
		if (!in_array($str_arr[2], $field)) {
			$field[]=$str_arr[2];
		}
		if (!in_array($str_arr[3], $field)) {
			$field[]=$str_arr[3];
		}
		if (isset($menu['recursive_param'])&&$menu['recursive_param']==$field_param) {

			if ($menu['is_recursive']==1) {
				if (!in_array($field_param, $field)) {
					$field[]=$field_param;
				}
			  
			}
		}

		$new_arr=Db::name($con_table_name)->where($where)->field($field)->select();
		// dump($new_arr);exit;
		if (isset($menu['recursive_param'])&&$menu['recursive_param']==$field_param) {

			if ($menu['is_recursive']==1) {

			  $recursive_param=!empty($menu['recursive_param'])?$menu['recursive_param']:'up_id';
			  $new_arr=getTree($new_arr,$recursive_param);
			  unset($GLOBALS['tree']);
			}
		}

		// foreach ($new_arr as $key => &$e) {
		// 	// if (isset($e['name'])) {
				
		// 	// }
		// 	// $e['name']=$e[$str_arr[3]];
		// }
		// dump($new_arr);exit;
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

function msg($str=''){
	echo "<script>alert('".$str."');</script>";exit;
}
function get_model_table_by_id($id=0){
	$table=db("sys_model")->where("id",$id)->value("table");
	if (!$table) {
		if (request()->isGet()){
			msg("模型不存在");
		}else{
			ejson(-1,"模型不存在");
		}
	}
	return $table;
}
function filter_input_data($data,$fields){
	
	foreach ($fields as $key => $e) {
		if ($e['is_allow_null']==1) {
			// if (!isset($data[$e['field']])||empty($data[$e['field']])) {
			if (!isset($data[$e['field']])||$data[$e['field']]==='') {
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
	$arr=db("sys_fields")->alias("a")->join("kt_sys_field_tab b","a.tab=b.id",'left')->where("a.model_id",$model_id)->where($where)->field("a.*,b.name as tab_name,b.sort as tab_sort")->order("a.sort asc")->select();
	$new_arr=[];
	foreach ($arr as $key => $e) {
		if (!isset($new_arr[$e['tab']])) {
			if ($e['tab']==0) {
				$e['tab_name']='基础';
				$e['tab_sort']=0;
				$e['tab_id']=0;
			}
			$new_arr[$e['tab']]['name']=$e['tab_name'];
			$new_arr[$e['tab']]['sort']=$e['tab_sort'];
			$new_arr[$e['tab']]['tab_id']=$e['tab'];
		}
		$new_arr[$e['tab']]['child'][]=$e;
	}

	$new_arr=arr_sort($new_arr,'sort');
	// dump($new_arr);exit;
	return $new_arr;

}
function get_fields_search($model_id=0,$where){
	return Db::name("sys_fields")->alias("a")->where("model_id",$model_id)->where($where)->order("sort asc")->select();
}
function get_new_fields($model_id=0,$fields=''){
	return db("sys_fields")->where("model_id",$model_id)->where("field",'in',$fields)->order("sort asc")->select();
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


//二维数组按照某个键值排序
function arr_sort($arr,$field,$sort='SORT_ASC'){
	$sort = array(
	        'direction' => $sort, //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
	        'field'     => $field,       //排序字段
	);
	$arrSort = array();
	foreach($arr AS $uniqid => $row){
	    foreach($row AS $key=>$value){
	        $arrSort[$key][$uniqid] = $value;
	    }
	}
	if($sort['direction']){
		if (isset($arrSort[$sort['field']])) {
			
		}else{
			$arrSort[$sort['field']]=[];
		}
	    array_multisort($arrSort[$sort['field']], constant($sort['direction']), $arr);
	}
	return $arr;
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
function error_page(){
	header("Location:http://{$_SERVER['SERVER_NAME']}/404.html");exit;
}
function get_url() {
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
	return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
}
function wx(){
	// $wx = new WxApi();
	$wx = new WxApi(config('sys_set')['wx_appid'],config('sys_set')['wx_appsecret']);

	return $wx;
}

function wx_signPackage(){
	$wx=wx();
	$signPackage = $wx->wxJsapiPackage();
	return $signPackage;
}

/**@name 获取随机字符串，大小写字母+数字，转变不区分大小写
*@param $len 长度
*@param $ignoreCase 忽略大小写
 * */
function get_salt($len=6, $ignoreCase=true)
{
  //return substr(uniqid(rand()), -$len);
  $discode="123546789wertyupkjhgfdaszxcvbnm".($ignoreCase?'': 'QABCDEFGHJKLMNPRSTUVWXYZ');
  $code_len = strlen($discode);
  $code = "";  
  for($j=0; $j<$len; $j++){
    $code .= $discode[rand(0, $code_len-1)];
  }
  return $code;
}
