<?php
use think\Cache;
use think\Db;


/**
 * 获取表信息
 * @param    string
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:24:11+0800
 */
function table_fields_info($table=''){

	$arr = Db::query("show full columns from `$table`");

	foreach ($arr as &$e) {
	   $arr2=explode("(", $e['Type']);
	   $e['Type']=$arr2[0];
	   if (isset($arr2[1])) {
	     $arr3=explode(")", $arr2[1]);
	     $e['Length']=$arr3[0];
	   }else{
	    $e['Length']='';
	   }

	}

	return $arr;
}
/**
 * 获取表字段
 * @param    string
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:24:37+0800
 */
function table_fields($table=''){
	$arr=Db::name("sys_fields")->where("table",$table)->select();
	return $arr;
}

/**
 * 获取后台显示菜单
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:25:01+0800
 */
function get_admin_menus(){
  $admin=session("admin");
  $admin_type=session("admin_type");
  $auth_arr=json_decode($admin['auth_ids'],true);
  // dump($auth_arr);exit;
  if (!isset($auth_arr)) {
  	$auth_arr=[];
  }
  $menu_ids=[];
  foreach ($auth_arr as $key => $e) {
  	$menu_ids[]=$key;
  }
  unset($e);

 if ($admin_type==1) {
 	$where['id']=['gt',0];
 }else{
 	$where['id']=['in',$menu_ids];
 	$where['status']=['eq',1];
 }

  $arr_top=Db::name("sys_menu")->distinct(true)->where($where)->field("up_id")->select();

  // dump($menu_ids);exit;
  foreach ($arr_top as $key => $e) {
  	$menu_ids[]=$e['up_id'];
  }
  unset($e);

 if ($admin_type==1) {
 	$where['id']=['gt',0];
 }else{
 	$where['id']=['in',$menu_ids];
 	$where['status']=['eq',1];
 }

  $arr2=Db::name("sys_menu")->where($where)->order("sort asc")->select();
  
  $new_arr=digui_menu($arr2);
 
  $nn=make_menus($new_arr);

  return $nn;
  // unset($e);
}
/**
 * 生成菜单
 * @param    [type]
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:25:56+0800
 */
function make_menus($arr){

	
	// dump(session('admin'));exit;
	$str='';
	foreach ($arr as $key => $e) {
		if (isset($e['child'])) {
			$str.='<li>';
			$str.='<a href="#"><i class="'.$e['class'].'"></i><span class="nav-label">'.$e['name'].'</span><span class="fa arrow"></span></a>';
			$str.='<ul class="nav nav-second-level">';
			$str.=make_menus($e['child']);
			$str.='</ul></li>';
		}else{
			if ($e['url']=='') {
				$url=url('admin/common/data_list',['menu_id'=>$e['id']]);
				if ($e['param']!='') {
					$url=$url.$e['param'];
				}
			}else{
				$url=$e['url'];
			}

			$str.='<li><a class="J_menuItem" href="'.$url.'" data-index="0">'.$e['name'].'</a></li>';
		}
	}
	return $str;
}

/**
 * 检查是否有操作权限
 * @param    [type]
 * @param    [type]
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:26:07+0800
 */
function check_auth($operation_id,$menu_id){
	$admin=session("admin");
	$auth_arr=json_decode($admin['auth_ids'],true);
	if (!isset($auth_arr[$menu_id])) {
		$auth_arr[$menu_id]=[];
	}
	// dump($auth_arr);exit;
	if (in_array($operation_id, $auth_arr[$menu_id])) {
		return true;
	}else{
		if (request()->isAjax()) {
			ejson(-1,'没有权限');
		}else{
			echo "权限不足";exit;
		}
		
	}
}


function get_all_auth(){
	$arr2=Db::name("sys_menu")->where("status",1)->field("id,name,operation,up_id")->order("sort asc")->select();

	$new_arr=digui_menu($arr2);
	return $new_arr;
	// dump($new_arr);exit;
}
/**
 * [菜单递归显示]
 * @param    array
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:26:33+0800
 */
function digui_menu($arr2=array()){
	$new_arr=[];
	$arr=[];
	foreach ($arr2 as $key => $e) {
	  $arr[$e['id']]=$e;
	}
	unset($e);
	foreach ($arr as $key => $e) {
	  if ($e['up_id']==0) {
	    $new_arr[]=& $arr[$key];
	  }else{
	    $arr[$e['up_id']]['child'][]=& $arr[$key];
	  }
	}
	unset($arr);

	foreach ($new_arr as $key => $e) {
		if (!isset($e['child'])) {
			unset($new_arr[$key]);
		}
	}
	
	return $new_arr;
}


function case_model_id($model_id=0,$id=0,$data=array()){
	$time=time();
}

/**
 * 获取菜单对应操作
 * @param    [type]
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:27:14+0800
 */
function get_sys_operation($ids){
	$arr=Db::name("sys_operation")->where("id",'in',$ids)->order("sort asc")->select();
	$new_arr=['top'=>[],'list'=>[]];
	foreach ($arr as $key => $e) {
		if ($e['type']==1) {
			$new_arr['top'][]=$e;
		}elseif ($e['type']==2) {
			$new_arr['list'][]=$e;
		}
	}
	return $new_arr;
}
/**
 * 获取后台所有操作选项
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:27:54+0800
 */
function get_operation(){
	return $arr=Db::name("sys_operation")->order("sort asc")->select();
}


/**
 * 上传图片限制
 * @param    string
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:28:17+0800
 */
// function upload_set($rule=''){
// 	$arr=json_decode($rule,true);
// 	if (!isset($arr['size'])) {
// 		$arr['size']='999999999';
// 	}
// 	if (!isset($arr['file_type'])) {
// 		$arr['file_type']='*.*';
// 	}
// 	return $arr;
// }
function upload_set($rule=''){
	$arr=explode('|', $rule);
	$res=[];
	if (!isset($arr[2])) {
		$res['size']='999999999';
	}else{
		$res['size']=$arr[2];
	}
	if (!isset($arr[1])) {
		$res['file_type']="*";
	}else{
		$res['file_type']=$arr[1];
	}

	return $res;
}
/**
 * [获取导航]
 * @Author   wxy
 * @DateTime 2017-07-08T17:16:42+0800
 * @return   [type]
 */
// function get_nav(){
// 	$arr=Db::name("nav")->order("sort asc")->select();
// 	// foreach ($arr as $key => &$e) {
// 	// 	$e['parent_id']=$e['up_id'];
// 	// }
// 	// unset($e);
// 	// $res=make_option_tree_for_select($arr);

// 	$res=getTree($arr);
// 	 unset($GLOBALS['tree']);
// 	return $res;
// 	// dump($res);exit;
// }
/**
 * 
 * 遍历文件夹目录
 * @param $dir 遍历路径
 * @author jourmy@hotmail.com 
 */
function folder_list($dir){
     $dir .= substr($dir, -1) == '/' ? '' : '/';
     $dirInfo = array();
    foreach (glob($dir.'*') as $v) {
          $dirInfo[] = $v; 
          if(is_dir($v)){
              $dirInfo = array_merge($dirInfo, folder_list($v));
          }
        }
 return $dirInfo;
}

/**
 * 获取default下的模版文件
 * 
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:54:09+0800
 */
function get_templet(){
	$dir=ROOT_PATH.'public/templets/default/index/';
	$arr=[];
	// $res=folder_list($dir);
	$res=glob($dir.'*.html');

	foreach ($res as $key => &$e) {
	    $e=str_replace($dir, '', $e);
	    $e=str_replace('.html', '', $e);
	    $arr[]=['id'=>$e,'name'=>$e];
	}
	unset($e);
	return $arr;
}

/**
 * 递归显示列表
 * @param    [type]
 * @param    string
 * @param    integer
 * @param    integer
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:55:20+0800
 */
function getTree($data,$recursive_param='up_id',$pid=0,$step=0){
	global $tree;
	foreach ($data as $key => $e) {

		if ($e[$recursive_param]==$pid) {
            $flg='';
            if ($step==0) {
                // $flg = "◆&nbsp;";
                $flg = "|-&nbsp;";
            }
			if ($step>0) {
                // $flg = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$step);
                // $flg.="▶";
                $flg = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$step);
                $ggf="|-";
                $flg.=$ggf."&nbsp;";

            }
			$e['name']=$flg.$e['name'];
			$tree[]=$e;
			getTree($data,$recursive_param,$e['id'],$step+1);
		}
	}
    
	return $tree;
}
/**
 * 获取要查找的表名
 * @param    string
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:55:39+0800
 */
function get_search_table($str=''){
	$arr2=explode('|',$str);
	if (isset($arr2[1])) {
		$arr3=explode(',', $arr2[1]);
	}else{
		$arr3=array();
	}
	return $arr2[0];
}
/**
 * [生成查询条件 已弃用]
 * @param    integer
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:56:20+0800
 */
// function build_search_condition($model_id=0){
// 	$input[$e['field']];
// 	$arr=Db::name("sys_fields")->where("model_id",$model_id)->where("is_search",1)->order("sort asc")->select();
// 	$str='';
// 	foreach ($arr as $key => $e) {
// 		if (isset($input[$e['field']])) {

// 		  $stable=get_search_table();
// 		  if ($stable!='') {
// 		  	$str.=" and ".$e['field']." like '%".$input[$e['field']]."%'";
// 		  }
// 		  $str.=" and ".$e['field']." like '%".$input[$e['field']]."%'";
// 		}
// 	}

// }

/**
 * 获取全部权限
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T17:57:41+0800
 */
function get_full_auth_ids(){
	$arr=Db::name("sys_menu")->field("id,operation")->select();
	$arr2=[];
	foreach ($arr as $key => $e) {
		$e['operation']=explode(',', $e['operation']);
		$arr2[$e['id']]=$e['operation'];
	}
	$auth=json_encode($arr2);
	return $auth;
}

/**
 * 获取字段类型
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:12:30+0800
 */
   function get_fields_type(){
        $arr=db("sys_fieldtype")->select();
        $new_arr=[];
        foreach ($arr as $key => $e) {
        	$new_arr[]=['id'=>$e['id'],'name'=>$e['name']];
        }
        return $new_arr;
        // ejson(1,'获取成功',$arr);
    }
  /**
   * 获取表的所有字段
   * @param    string
   * @return   [type]
   * @Author   wxy                      <www.b9n9.com>
   * @DateTime 2017-07-08T18:14:28+0800
   */
 function get_table_fields($table=""){
    $arr = Db::query("show full columns from `kt_$table`");	
 
     $new_arr=[];
     foreach ($arr as $key => $e) {
     	$new_arr[]=['id'=>$e['Field'],'name'=>$e['Field']];
     }
     return $new_arr;
     // ejson(1,'获取成功',$arr);
 }
/**
 * 返回所有表
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:15:14+0800
 */
 function get_tables(){
     $arr=Db::query("show table status");
     // 
     $new_arr=[];
     
     foreach ($arr as $key => $e) {
         // $id=substr($e['Name'], 3);
         $new_arr[]=['id'=>substr($e['Name'], 3),'name'=>$e['Name']];
         // $new_arr[]=['id'=>ltrim($e['Name'],'kt_'),'name'=>$e['Name']];
     }
     
     // dump($new_arr);exit;
     return $new_arr;
   
 }

/**
 * 返回字段对应的名称
 * @param    integer
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:15:42+0800
 */
 function get_fields_name($model_id=0){
 	$arr=db("sys_fields")->where("model_id",$model_id)->select();
 	return $arr;
 }
/**
 * 返回系统状态
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:25:21+0800
 */
 function get_status(){
     return [['id'=>1,'name'=>'正常'],['id'=>-1,'name'=>'禁用']];
 }
/**
 * 返回所有模型
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:25:58+0800
 */
 function get_sys_models(){
    $arr=db("sys_model")->order("id desc")->select();
    return $arr;
 }
/**
 * 返回系统菜单
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:29:14+0800
 */
 function get_sys_menus(){
    $arr=db("sys_menu")->order("sort desc")->select();
    return $arr;
 }

/**
 * 返回某个表里的所有数据
 * @param    string
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:29:51+0800
 */
 function get_table_data($table='',$where=''){
     $arr=Db::name($table)->where($where)->select();
     return $arr;
 }
 /**
  * 返回某个表里的所有数据
  * @param    string
  * @return   [type]
  * @Author   wxy                      <www.b9n9.com>
  * @DateTime 2017-07-08T18:30:18+0800
  */
 function gtd($table='',$where=''){
      $arr=Db::name($table)->where($where)->select();
     return $arr;
 }


 function export_excel($fields=[],$arr=[]){
 	  error_reporting("E_ALL");
 	  
 	  require_once VENDOR_PATH.'PHPExcel-1.8/Classes/PHPExcel.php';
 	  require_once VENDOR_PATH.'PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';

 	  $objPHPExcel = new \PHPExcel();
 
 	  $objPHPExcel->setActiveSheetIndex(0);
 	  $count = count($arr);
 	  $AZ=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
 	 foreach ($fields as $key => $e) {
 	     $objPHPExcel->setActiveSheetIndex(0)->setCellValue($AZ[$key].'1', $e['name']);
 	 }
 	
 	
 	  for ($i = 2; $i < $count+2; $i++) {

 	   foreach ($fields as $key => $e) {
 	     // switch ($e['edit_type']) {
 	     //   case '4':
 	     //   case '5':
 	     //   case '6':
 	     //   case '7':
 	     //   case '8':
 	     //     $arr[$i-2][$e['field']]=get_list_show($arr[$i-2][$e['field']],$e['data_from']);
 	     //     break;
 	     //   case '9':
 	     //     $arr[$i-2][$e['field']]=date("Y-m-d H:i:s",$arr[$i-2][$e['field']]);
 	     //     break;
 	     //   default:
 	     //     # code...
 	     //     break;
 	     // }

 	  
 	       $objPHPExcel->setActiveSheetIndex(0)->setCellValue($AZ[$key]. $i, $arr[$i-2][$e['field']]);
 	   }
 	  }

 	$objPHPExcel->setActiveSheetIndex(0);
 	  // Rename worksheet
 	  $objPHPExcel->getActiveSheet()->setTitle('Simple');



 	  ob_end_clean();
 	  header("Pragma: public");
 	  header("Expires: 0");
 	  header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
 	  header("Content-Type:application/force-download");
 	  header("Content-Type:application/vnd.ms-execl");
 	  header("Content-Type:application/octet-stream");
 	  header("Content-Type:application/download");;
 	  header('Content-Disposition:attachment;filename='.date("YmdHis").'.xlsx');
 	  header("Content-Transfer-Encoding:binary");
 	  ob_clean();
 	  $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 	  $objWriter->save('php://output');
 	  exit("导出成功");
 }
