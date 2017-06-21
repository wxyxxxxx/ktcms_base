<?php
use think\Cache;
use think\Db;



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

function table_fields($table=''){
	$arr=db("sys_fields")->where("table",$table)->select();
	return $arr;
}


function get_admin_menus(){
  $admin=session("admin");
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

 if ($admin['id']==0) {
 	$arr66=db("sys_menu")->where("status",1)->field("id")->select();
 	foreach ($arr66 as $key => $e) {
 		$menu_ids[]=$e['id'];
 	}
 	// $where="1=1";
 	$where['id']=['in',$menu_ids];
 }else{
 	$where['id']=['in',$menu_ids];
 }
  $arr_top=db("sys_menu")->distinct(true)->where("status",1)->where($where)->field("up_id")->select();

  // dump($menu_ids);exit;
  foreach ($arr_top as $key => $e) {
  	$menu_ids[]=$e['up_id'];
  }
  unset($e);

  $arr2=db("sys_menu")->where("status",1)->where('id','in',$menu_ids)->order("sort asc")->select();
  // dump($arr2);exit;
  $new_arr=digui_menu($arr2);
  // dump($new_arr);exit;
  $nn=make_menus($new_arr);
  // dump($nn);exit;
  return $nn;
  // unset($e);
}

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
	$arr2=db("sys_menu")->where("status",1)->field("id,name,operation,up_id")->order("sort asc")->select();

	$new_arr=digui_menu($arr2);
	return $new_arr;
	// dump($new_arr);exit;
}

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
	// switch ($model_id) {
	// 	case '1':
	// 	return;
	// 		$arr=$data['operation'];
	// 		$arr=explode(',', $arr);
	// 		// $data=[];
	// 		foreach ($arr as $key => $e) {
	// 			$data=['menu_id'=>$id,'operation_id'=>$e,'c_time'=>$time];
	// 			$arr=db("sys_auth")->where("menu_id",$id)->where("operation_id",$e)->find();
	// 			if (!$arr) {
	// 				db("sys_auth")->insert($data);
	// 			}
	// 		}
	// 		// db("sys_auth")->insertAll($data);
	// 		unset($e);
	// 		break;
		
	// 	default:
	// 		# code...
	// 		break;
	// }
}


function get_sys_operation($ids){
	// if ($type>0) {
	// 	$where="type=$type";
	// }else{
	// 	$where="1=1";
	// }


	$arr=db("sys_operation")->where("id",'in',$ids)->order("sort asc")->select();
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

function get_operation(){
	return $arr=db("sys_operation")->order("sort asc")->select();
}



function upload_set($rule=''){
	$arr=json_decode($rule,true);
	if (!isset($arr['size'])) {
		$arr['size']='999999999';
	}
	if (!isset($arr['file_type'])) {
		$arr['file_type']='*.*';
	}
	return $arr;
}


function get_nav(){
	$arr=db("nav")->order("sort asc")->select();
	foreach ($arr as $key => &$e) {
		$e['parent_id']=$e['up_id'];
	}
	unset($e);
	$res=make_option_tree_for_select($arr);
	return $res;
	// dump($res);exit;
}
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


function make_tree($arr){
    if(!function_exists('make_tree1')){
        function make_tree1($arr, $parent_id=0){
            $new_arr = array();

            foreach($arr as $k=>$v){
            	// dump($v);exit;
                if($v['parent_id'] == $parent_id){
                    $new_arr[] = $v;
                    unset($arr[$k]);
                }
            }
            foreach($new_arr as &$a){
                $a['children'] = make_tree1($arr, $a['id']);
            }
            return $new_arr;
        }
    }
    return make_tree1($arr);
}
 
function make_tree_with_namepre($arr)
{
    $arr = make_tree($arr);
    if (!function_exists('add_namepre1')) {
        function add_namepre1($arr, $prestr='') {
            $new_arr = array();
            foreach ($arr as $v) {
                if ($prestr) {
                    if ($v == end($arr)) {
                        $v['name'] = $prestr.'└─ '.$v['name'];
                    } else {
                        $v['name'] = $prestr.'├─ '.$v['name'];
                    }
                }
 
                if ($prestr == '') {
                    $prestr_for_children = '　 ';
                } else {
                    if ($v == end($arr)) {
                        $prestr_for_children = $prestr.'　　 ';
                    } else {
                        $prestr_for_children = $prestr.'│　 ';
                    }
                }
                $v['children'] = add_namepre1($v['children'], $prestr_for_children);
 
                $new_arr[] = $v;
            }
            return $new_arr;
        }
    }
    return add_namepre1($arr);
}
 
/**
 * @param $arr
 * @param int $depth，当$depth为0的时候表示不限制深度
 * @return string
 */

function make_option_tree_for_select($arr, $depth=0)
{
    $arr = make_tree_with_namepre($arr);
    if (!function_exists('make_options1')) {
        function make_options1($arr, $depth, $recursion_count=0, $ancestor_ids='') {
            $recursion_count++;
            $str = [];
            foreach ($arr as $v) {
                $str[]= ['id'=>$v['id'],'name'=>$v['name']];
                if ($v['parent_id'] == 0) {
                    $recursion_count = 1;
                }
                if ($depth==0 || $recursion_count<$depth) {
                    // = ;
                    $str=array_merge($str,make_options1($v['children'], $depth, $recursion_count, $ancestor_ids.','.$v['id']));
                }
    
            }
            return $str;
        }
    }
    return make_options1($arr, $depth);
}
function make_option_tree_for_select22($arr, $depth=0)
{
    $arr = make_tree_with_namepre($arr);
    if (!function_exists('make_options1')) {
        function make_options1($arr, $depth, $recursion_count=0, $ancestor_ids='') {
            $recursion_count++;
            $str = '';
            foreach ($arr as $v) {
                $str .= "<option value='{$v['id']}' data-depth='{$recursion_count}' data-ancestor_ids='".ltrim($ancestor_ids,',')."'>{$v['name']}</option>";
                if ($v['parent_id'] == 0) {
                    $recursion_count = 1;
                }
                if ($depth==0 || $recursion_count<$depth) {
                    $str .= make_options1($v['children'], $depth, $recursion_count, $ancestor_ids.','.$v['id']);
                }
 
            }
            return $str;
        }
    }
    return make_options1($arr, $depth);
}
