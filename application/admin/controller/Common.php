<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
use think\Cache;
class common extends Base
{   

/**
 * 欢迎页面
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:32:16+0800
 */
     public function welcome()
     {  
       // echo "V1.0.0";exit;
         return $this->fetch();
     }
/**
 * 修改管理员密码
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:32:40+0800
 */
     public function edit_admin_pwd(){
      $admin_id=session("admin_id");
      $old_pwd=input("post.old_pwd",'');
      $new_pwd=input("post.new_pwd",'');
      $arr=Db::name("sys_admin")->where("id",$admin_id)->field("pwd")->find();
      if ($arr['pwd']!=md5($old_pwd)) {
        ejson(-1,"原始密码错误");
      }
      $data['pwd']=md5($new_pwd);
      Db::name("sys_admin")->where("id",$admin_id)->update($data);
      ejson(1,"修改成功");
     }


/**
 * 首页
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:33:55+0800
 */
     public function index(){
        $menus=get_admin_menus();
        $this->assign(['menus'=>$menus]);
       
        return $this->fetch();
     }

/**
 * 导出excel
 */

    public function daochu(){
       error_reporting("E_ALL");
        $menu_id=input("get.menu_id",'');
        $input=input("get.");
        $menu=get_menu($menu_id);
        $model_id=$menu['model_id'];
        $model=get_model($model_id);
        $fields=get_fields_search($model_id,['is_display'=>1]);   
        $where="1=1";
        $joins=[];
        $search_fields='';

        foreach ($fields as $key => $e) {
            $search_fields.=$e['field'].",";
            if (isset($input[$e['field']])) {
              $where.=" and ".$e['field']."='".$input[$e['field']]."'";
            }
        }
        $search_fields=rtrim($search_fields,',');
        if ($model['table']!='') {
            $arr=Db::name($model['table'])->where($where)->field($search_fields)->limit(10000)->select();
        }else{
            $arr=[];
        }
       require_once VENDOR_PATH.'PHPExcel-1.8/Classes/PHPExcel.php';
       require_once VENDOR_PATH.'PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';

       $objPHPExcel = new \PHPExcel();
       // $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
       // $objWriter->save("xxx.xlsx");
       // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
       $objPHPExcel->setActiveSheetIndex(0);
       $count = count($arr);
       $AZ=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
      foreach ($fields as $key => $e) {
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue($AZ[$key].'1', $e['name']);
      }
    
    
       for ($i = 2; $i < $count+2; $i++) {

        foreach ($fields as $key => $e) {
          switch ($e['edit_type']) {
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
              $arr[$i-2][$e['field']]=get_list_show($arr[$i-2][$e['field']],$e['data_from']);
              break;
            case '9':
              $arr[$i-2][$e['field']]=date("Y-m-d H:i:s",$arr[$i-2][$e['field']]);
              break;
            default:
              # code...
              break;
          }

       
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
/**
 * 数据列表
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:34:44+0800
 */
    public function data_list(){
      $menu_id=input("get.menu_id",'');
      $input=input("get.");
      $do_export=input("get.do_export",0);
      $menu=get_menu($menu_id);
      $model_id=$menu['model_id'];
      $model=get_model($model_id);
      $fields=get_fields_search($model_id,['is_display'=>1]);
      $s_fields=get_fields_search($model_id,['is_search'=>1]);

      $admin=session("admin");
      $auth_arr=json_decode($admin['auth_ids'],true);
      $op_ids=$auth_arr[$menu_id];

      $op_ids2=explode(',', rtrim($menu['operation'],','));
      $op_ids3=array_intersect($op_ids, $op_ids2);
      $operation=get_sys_operation($op_ids3);
      // $operation=get_sys_operation($menu['operation']);

      $where=[];
      $joins=[];
      $show_fields=[];
      $search_type=trim(input("get.search_type",'eq'));
      $sss_fields='';
      $sss_keys='';

      foreach ($fields as $key => $e) {
          // $show_fields.=$e['field'].",";
          // $show_fields[$e['field']]=$model_id.'_'.$e['field'];
          $show_fields[$e['field']]=$e['field'];
      }
      unset($e);



      if ($model['table']!='') {
        // $show_fields=rtrim($show_fields,',');

        $view=Db::view($model['table'],$show_fields);
        $table=$model['table'];

        $fields2=$fields;
        $unset_i=0;
        foreach ($fields2 as $key => $e) {
          
          if (strstr($e['data_from'],'TB|')) {

            $con_table=explode('|', $e['data_from']);
            // dump($con_table);exit;
            $con_table_name=get_model_table_by_id($con_table[1]);

            $con_fields=[];
            $con_table[4]=isset($con_table[4])?$con_table[4]:$con_table[3];
            $con_table[4]=explode(',', trim($con_table[4],','));
            $new_fields=get_new_fields($con_table[1],$con_table[4]);
            foreach ($new_fields as $key2 => &$n) {
              $con_fields[$n['field']]=$e['field'].'_'.$con_table[1].'_'.$n['field'];
              $n['field']=$e['field'].'_'.$con_table[1].'_'.$n['field'];

              if ($menu['is_recursive']==1) {
                if ($menu['recursive_param']==$e['field']&&$n['field']==$e['field'].'_'.$con_table[1].'_'.$con_table[3]) {
                  // dump($e['field'].'_'.$con_table[1].'_'.$con_table[3]);exit;
                  unset($new_fields[$key2]);
                }
              }
            }
            unset($n);
            
            array_splice($fields, (array_search($e, $fields)+1-$unset_i), 0, $new_fields);
            array_splice($s_fields, (array_search($e, $s_fields)+1-$unset_i), 0, $new_fields);
            
            unset($fields[$key-$unset_i]);
            unset($s_fields[$key-$unset_i]);
            $unset_i++;
            // dump($fields);exit;
            // foreach ($con_table[4] as $key2 => $n) {
            //   $con_fields[$n]=$con_table[1].'_'.$n;
            // }
            // dump($con_table);exit;
            $alias=$e['field'].'_'.$con_table_name;
            $view->view([PREFIX.$con_table_name=>$alias],$con_fields,"$alias.".$con_table[2]."=$table.".$e['field']."",'LEFT');
          }
        }
        unset($e);
        //排序
          $order=$table.'.';
          $order.=isset($menu['sort_field'])?$menu['sort_field']:'id';
          $order.=" ";
          $order.=isset($menu['sort_rule'])?$menu['sort_rule']:"DESC";
         // dump($order);exit;
          $page_size=isset($menu['page_size'])&&$menu['page_size']>0?$menu['page_size']:$this->sys_config['page_size'];
        //



          foreach ($s_fields as $key => $e) {
              
              if (isset($input[$e['field']])) {
                // $where.=" and ".$e['field']."='".$input[$e['field']]."'";
                // $where.=" and ".$e['field']." like '%".$input[$e['field']]."%'";
                switch ($search_type) {
                  case 'like':
                    $where[$e['field']]=["$search_type","%".$input[$e['field']]."%"];
                    break;
                  
                  default:
                    $where[$e['field']]=["$search_type",$input[$e['field']]];
                    break;
                }
                $sss_fields=$e['field'];
                $sss_keys=$input[$e['field']];
                
              }
          }
          unset($e);

         
        if ($do_export==1) {
          $arr=$view->where($where)->order($order)->fetchSql(false)->select();
          export_excel($fields,$arr);exit;
        }else{
          $arr=$view->where($where)->order($order)->fetchSql(false)->paginate($page_size,false,['query'=>$input]);
        }
        
         
        if ($menu['is_recursive']==1) {

          $recursive_param=!empty($menu['recursive_param'])?$menu['recursive_param']:'up_id';
          $arr_tree=$arr;
          $arr=getTree($arr,$recursive_param);
          
          unset($GLOBALS['tree']);
          if ($arr=='') {
            $arr=$arr_tree;
          }
        }
      }else{
          $arr=[];
      }
      // $arr=getTree($arr);
      // dump($arr);exit;
      // dump($where);exit;

      // if ($model['table']!='') {
      //   switch ($menu_id) {
      //     case '35':
      //       $arr=Db::name($model['table'])->field("*")->select();

      //       $arr=getTree($arr);
      //        unset($GLOBALS['tree']);
      //       // dump($arr);exit;
      //       // dump(getTree($arr));exit;
      //       break;
          
      //     default:
      //       $arr=Db::name($model['table'])->where($where)->field($search_fields)->paginate($this->sys_config['page_size'],false,['query'=>$input]);
      //       break;
      //   }
          
      // }else{
      //     $arr=[];
      // }
      // dump($_SERVER["QUERY_STRING"]);exit;
      // $arr=Db::name($tb)->paginate(10,true,['type'=>'bootstrap','var_page' => 'page']);
      // dump($menu);exit;
      $this->assign("search_type",$search_type);
      $this->assign("list",$arr);
      $this->assign("model",$model);
      $this->assign("fields",$fields);
      $this->assign("s_fields",$s_fields);
      $this->assign("model_id",$model_id);
      $this->assign("menu_id",$menu_id);
      $this->assign("menu",$menu);
      $this->assign("operation",$operation);
      $this->assign("sss_fields",$sss_fields);
      $this->assign("sss_keys",$sss_keys);
      return $this->fetch();
    }

/**
 * 编辑数据
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:35:03+0800
 */
    public function edit_data(){
      // $model_id=input("get.model_id",0);
      $menu_id=input("get.menu_id",'');
      $menu=get_menu($menu_id);
      $model_id=$menu['model_id'];
      $id=input("get.id",0);
      if ($id>0) {
        check_auth(3,$menu_id);
      }else{
        check_auth(2,$menu_id);
      }
      $model=get_model($model_id);
      $fields=get_fields($model_id,['a.is_edit'=>1]);
      // dump($fields);exit;
      $arr=Db::name($model['table'])->where("id",$id)->find();

    if ($id>0) {
      # code...
    }else{
      if (isset($_SERVER['HTTP_REFERER'])) {
        
      
        $refer=$_SERVER['HTTP_REFERER'];
        $refer=parse_url($refer);
        if (isset($refer['query'])) {
          $refer=explode('&', $refer['query']);
          foreach ($refer as $key => $e) {
            $arr88=explode('=', $e);
            if ($arr88[0]!='menu_id') {
             
                $arr[$arr88[0]]=$arr88[1];
              
                
            }
          }
        }


      }
    }

    // $tab=Db::name("sys_field_tab")->where("mid",$model_id)->order("sort asc")->select();
    // $new_arr=[];
    // foreach ($arr as $key => $e) {
    //   if ($e['tab']) {
    //     # code...
    //   }
    //   $new_arr[$e['tab']][]=$e;
    // }
    
      $this->assign([
          "list"=>$arr,
          "fields"=>$fields,
          "id"=>$id,
          "model_id"=>$model_id,
          "menu_id"=>$menu_id,
          "menu"=>$menu,
        ]);
      return $this->fetch();
    }
/**
 * 数据详情
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:35:15+0800
 */
    public function data_detail(){
        // $model_id=input("get.model_id",0);
        $menu_id=input("get.menu_id",'');
        $menu=get_menu($menu_id);
        $model_id=$menu['model_id'];
        $id=input("get.id",0);
        $model=get_model($model_id);

        check_auth(5,$menu_id);
        
        $fields=get_fields_search($model_id,['is_edit'=>1]);
        $arr=Db::name($model['table'])->where("id",$id)->find();
        
        $this->assign([
            "list"=>$arr,
            "fields"=>$fields,
            "id"=>$id,
            "model_id"=>$model_id,
            "menu_id"=>$menu_id,
          ]);
        return $this->fetch();
    }
/**
 * 保存数据
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:35:28+0800
 */
    public function do_edit_data(){
      // $model_id=input("get.model_id",'');
      $menu_id=input("get.menu_id",'');
      $menu=get_menu($menu_id);
      $model_id=$menu['model_id'];
      $id=input("get.id",0);
      $data=input("post.");

      if ($id>0) {
        check_auth(3,$menu_id);
      }else{
        check_auth(2,$menu_id);
      }
      
      $model=get_model($model_id);
      $fields=Db::name("sys_fields")->where("model_id",$model['id'])->where("is_edit",1)->order("sort asc")->select();

      $data=filter_input_data($data,$fields);


      // dump($data);exit;
      if ($id>0) {
        // $data['u_time']=time();
        Db::name($model['table'])->where("id",$id)->strict(false)->update($data);
        case_model_id($model_id,$id,$data);//针对每个表不同的处理
        ejson(1,'更新成功');
      }else{
        $data['c_time']=time();
        $data['status']=1;
        $id=Db::name($model['table'])->strict(false)->insertGetId($data);
        case_model_id($model_id,$id,$data);//针对每个表不同的处理
        ejson(1,'添加成功');
      }

    }
/**
 * 删除数据
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:35:43+0800
 */
    public function del_data(){
        $id=input("get.id",0);
        // $model_id=input("get.model_id",0);
        $menu_id=input("get.menu_id",'');
        $menu=get_menu($menu_id);
        $model_id=$menu['model_id'];
        $model=get_model($model_id);

        
        
        $id = explode(',', rtrim($id,','));
      
        if (count($id)>1) {
          check_auth(6,$menu_id);
        }else{
          check_auth(4,$menu_id);
        }

        $fields=get_table_fields($model['table']);
        $is_sys=0;
        foreach ($fields as $key => $e) {
          if ($e['id']=='is_sys') {
            $is_sys=1;
          }
        }
        if ($is_sys==1) {
          foreach ($id as $key => $e) {
            $arr=Db::name($model['table'])->where('id',$e)->field("is_sys,id")->find();
            if ($arr['is_sys']==1) {
              ejson(-1,'记录'.$e.'为系统内容不可删除');
            }else{
              Db::name($model['table'])->delete($e);
            }
          }
          $res=1;
        }else{
          // dump($fields);exit;
          $res=Db::name($model['table'])->delete($id);
        }


        if ($res>0) {
            ejson(1,'删除成功');
        }else{
            ejson(-1,'删除失败');
        }
    }
/**
 * 修改状态
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:35:56+0800
 */
    public function change_status(){
      $id=input("get.id",0);
      $menu_id=input("get.menu_id",'');
      $menu=get_menu($menu_id);
      $model_id=$menu['model_id'];
      $model=get_model($model_id);



      $data=input("get.");
      unset($data['id']);
      unset($data['menu_id']);

      if (isset($data['status'])) {
        if ($data['status']==1) {
          check_auth(9,$menu_id);
        }
        if ($data['status']==2) {
          check_auth(10,$menu_id);
        }
        
      }

      $res=Db::name($model['table'])->where("id",$id)->update($data);
      if ($res>0) {
        ejson(1,'操作成功');
      }else{
         ejson(1,'操作成功');
      }

    }


    /**
     * 修改字段值
     * @return   [type]                   [description]
     * @Author   wxy                      <www.b9n9.com>
     * @DateTime 2017-07-11T09:59:41+0800
     */
    public function change_field_value(){
      $id=input("get.id",0);
      $menu_id=input("get.menu_id",'');
      // $field=input("get.field",'');
      $menu=get_menu($menu_id);
      $model_id=$menu['model_id'];
      $model=get_model($model_id);




      $data=input("get.");
      unset($data['id']);
      unset($data['menu_id']);
    

      if (empty($data)) {
        ejson(-1,'操作失败');
      }

      $res=db($model['table'])->where("id",$id)->update($data);
      if ($res>0) {
        ejson(1,'操作成功');
      }else{
        ejson(1,'操作成功');
      }

    }
/**
 * 修改排序
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:36:11+0800
 */
    public function change_sort(){
      $id=input("get.id",0);
      $menu_id=input("get.menu_id",'');
      $menu=get_menu($menu_id);
      $model_id=$menu['model_id'];
      $model=get_model($model_id);



      $data['sort']=input("post.sort",0);




      $res=Db::name($model['table'])->where("id",$id)->update($data);
      if ($res>0) {
        ejson(1,'操作成功');
      }else{
         ejson(-1,'操作失败');
      }

    }
/**
 * 角色权限设置
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:36:27+0800
 */
    public function role_auth(){
      $id=input("get.id",0);
      $menu_id=input("get.menu_id",'');
      $menu=get_menu($menu_id);

      check_auth(11,$menu_id);

      $model_id=$menu['model_id'];
      $model=get_model($model_id);
      $arr=Db::name($model['table'])->where("id",$id)->find();
      $chose_auth=json_decode($arr['auth_ids'],true);
      // dump($chose_auth);exit;
      $auth=get_all_auth();
      $this->assign([
          "list"=>$arr,
          "auth"=>$auth,
          "id"=>$id,
          "model_id"=>$model_id,
          "menu_id"=>$menu_id,
          "chose_auth"=>$chose_auth,
        ]);
      return $this->fetch();
    }
/**
 * 保存权限
 * @return   [type]
 * @Author   wxy                      <www.b9n9.com>
 * @DateTime 2017-07-08T18:36:44+0800
 */
    public function save_role_auth(){
      $id=input("get.id",0);
      $menu_id=input("get.menu_id",0);

      check_auth(11,$menu_id);
      $data=input("post.");
      $data=json_encode($data);
      $map['auth_ids']=$data;
      $res=Db::name("sys_role")->where("id",$id)->update($map);
      if ($res>0) {
        ejson(1,'操作成功');
      }else{
         ejson(-1,'操作失败');
      }
    }


    /**
     * 上传文件
     * @return   [type]
     * @Author   wxy                      <www.b9n9.com>
     * @DateTime 2017-07-08T18:37:07+0800
     */
    public function uploadify(){
      $targetFolder = '/uploads'; // Relative to the root
      $str=date('YmdHis') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);

      $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;

      $addtime=date("Ymd",time());      
      $testdir=$targetPath.'/'.$addtime."/";   
      if(file_exists($testdir)){

      } else{
        mkdir($testdir,0777); 
      }  
      $targetPath = $testdir;

      if (!empty($_FILES) ) {
        $tempFile = $_FILES['file']['tmp_name'];
        
        // $fileTypes = array('jpg','jpeg','gif','png','pem','JPG',"PNG",'GIF','JPEG'); // File extensions
        $fileParts = pathinfo($_FILES['file']['name']);
        $sname=$targetFolder.'/'.$addtime.'/'.$str.'.'.$fileParts['extension'];
        $targetFile = rtrim($targetPath,'/') . '/'.$str.'.'.$fileParts['extension'];
        // if (in_array($fileParts['extension'],$fileTypes)) {
        if (1==1) {
          move_uploaded_file($tempFile,$targetFile);
        
          echo $sname;exit;
        } else {
          echo 'Invalid file type.';exit;
        }
      }
    }



    public function menu_icon(){

      return $this->fetch();
    }
    /**
     * 退出
     * @return   [type]
     * @Author   wxy                      <www.b9n9.com>
     * @DateTime 2017-07-08T18:37:27+0800
     */
    public function login_out(){
      session("admin_id",null);
      session_destroy();
      $this->redirect("/kt");
    }




	
	

}


function buid_edit_html($fields,$arr=array()){
  for ($i=0; $i < 1; $i++) { 
    # code...
  }
}

