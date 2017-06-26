<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
use think\Cache;
class common extends Controller
{   
    public $admin;
    public $admin_id;
    public $sys_config;

    public function _initialize()
     {
        header("Content-type: text/html; charset=utf-8");
        $this->assign("title","KTCMS");
        $this->assign("host_url","http://".$_SERVER['SERVER_NAME']);
        $action       = Request::instance()->action();
        $this->check_admin_login();
        $this->sys_config=get_sys_config();
        $this->assign(['sys_config'=>$this->sys_config]);

        get_nav();
// get_tables();exit;
        // $arr=explode(",",'');
        // dump(json_decode(json_decode(json_encode([['id'=>1,'name'=>'内部'],['id'=>1,'name'=>'内部']])))) ;exit;
     }

     public function _empty($name)
     {  
        header("Content-type:text/html;charset=utf-8");
        exit("找不到该方法".$name);
         
     }

     public function welcome()
     {  
       // echo "V1.0.0";exit;
         return $this->fetch();
     }

     public function edit_admin_pwd(){
      $admin_id=session("admin_id");
      $old_pwd=input("post.old_pwd",'');
      $new_pwd=input("post.new_pwd",'');
      $arr=db("sys_admin")->where("id",$admin_id)->field("pwd")->find();
      if ($arr['pwd']!=md5($old_pwd)) {
        ejson(-1,"原始密码错误");
      }
      $data['pwd']=md5($new_pwd);
      db("sys_admin")->where("id",$admin_id)->update($data);
      ejson(1,"修改成功");
     }



     public function index(){
        $menus=get_admin_menus();
        $this->assign(['menus'=>$menus]);
       
        return $this->fetch();
     }



    public function check_admin_login(){
        $admin_id=session("admin_id");
        if ($admin_id>0) {
           $admin_name=session("account");
           $arr=db("sys_admin")->alias("a")->join("kt_sys_role b","a.role_id=b.id")->where("a.id",$admin_id)->field("a.account,a.id,a.role_id,b.auth_ids,b.name as role_name")->find();

           // if ($admin_id==1) {
             
           // }
           session('admin',$arr);
           $this->assign("arr",$arr);
           $this->assign("admin_name",$admin_name);
           $this->assign("admin",$arr);
           $this->admin_id=$admin_id;
        }else{
            $this->redirect("admin/login/index");exit;
        }
    }

 

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
            $arr=db($model['table'])->where($where)->field($search_fields)->limit(10000)->select();
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

    public function data_list(){
      $menu_id=input("get.menu_id",'');
     $input=input("get.");
      $menu=get_menu($menu_id);
      $model_id=$menu['model_id'];
      $model=get_model($model_id);
      $fields=get_fields_search($model_id,['is_display'=>1]);

      $admin=session("admin");
      $auth_arr=json_decode($admin['auth_ids'],true);
      $op_ids=$auth_arr[$menu_id];

      $op_ids2=explode(',', rtrim($menu['operation'],','));
      $op_ids3=array_intersect($op_ids, $op_ids2);
      $operation=get_sys_operation($op_ids3);
      // $operation=get_sys_operation($menu['operation']);
      
      $where="1=1";
      $joins=[];
      $search_fields='';

      foreach ($fields as $key => $e) {
          $search_fields.=$e['field'].",";
          if (isset($input[$e['field']])) {
            $where.=" and ".$e['field']."='".$input[$e['field']]."'";
          }
      }
      unset($e);
      $search_fields=rtrim($search_fields,',');
      if ($model['table']!='') {
        switch ($menu_id) {
          case '35':
            $arr=Db::name($model['table'])->field("*")->select();

            $arr=getTree($arr);
             unset($GLOBALS['tree']);
            // dump($arr);exit;
            // dump(getTree($arr));exit;
            break;
          
          default:
            $arr=Db::name($model['table'])->where($where)->field($search_fields)->paginate($this->sys_config['page_size'],false,['query'=>$input]);
            break;
        }
          
      }else{
          $arr=[];
      }
      
      // $arr=db($tb)->paginate(10,true,['type'=>'bootstrap','var_page' => 'page']);
      $this->assign("list",$arr);
      $this->assign("model",$model);
      $this->assign("fields",$fields);
      $this->assign("model_id",$model_id);
      $this->assign("menu_id",$menu_id);
      $this->assign("menu",$menu);
      $this->assign("operation",$operation);
      return $this->fetch();
    }


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
      $arr=db($model['table'])->where("id",$id)->find();

    if ($id>0) {
      # code...
    }else{
      if (isset($_SERVER['HTTP_REFERER'])) {
        
      
        $refer=$_SERVER['HTTP_REFERER'];
        $refer=parse_url($refer);
        $refer=explode('&', $refer['query']);
        foreach ($refer as $key => $e) {
          $arr88=explode('=', $e);
          if ($arr88[0]!='menu_id') {
           
              $arr[$arr88[0]]=$arr88[1];
            
              
          }
        }

      }
    }

    // $tab=db("sys_field_tab")->where("mid",$model_id)->order("sort asc")->select();
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
        ]);
      return $this->fetch();
    }

    public function data_detail(){
        // $model_id=input("get.model_id",0);
        $menu_id=input("get.menu_id",'');
        $menu=get_menu($menu_id);
        $model_id=$menu['model_id'];
        $id=input("get.id",0);
        $model=get_model($model_id);

        check_auth(5,$menu_id);
        
        $fields=get_fields($model_id,['is_edit'=>1]);
        $arr=db($model['table'])->where("id",$id)->find();
        
        $this->assign([
            "list"=>$arr,
            "fields"=>$fields,
            "id"=>$id,
            "model_id"=>$model_id,
            "menu_id"=>$menu_id,
          ]);
        return $this->fetch();
    }

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
      $fields=db("sys_fields")->where("model_id",$model['id'])->where("is_edit",1)->order("sort asc")->select();

      $data=filter_input_data($data,$fields);


      // dump($data);exit;
      if ($id>0) {
        // $data['u_time']=time();
        db($model['table'])->where("id",$id)->strict(false)->update($data);
        case_model_id($model_id,$id,$data);//针对每个表不同的处理
        ejson(1,'更新成功');
      }else{
        $data['c_time']=time();
        $data['status']=1;
        $id=db($model['table'])->strict(false)->insertGetId($data);
        case_model_id($model_id,$id,$data);//针对每个表不同的处理
        ejson(1,'添加成功');
      }

    }

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

        $res=db($model['table'])->delete($id);
        if ($res>0) {
            ejson(1,'删除成功');
        }else{
            ejson(-1,'删除失败');
        }
    }

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

      $res=db($model['table'])->where("id",$id)->update($data);
      if ($res>0) {
        ejson(1,'操作成功');
      }else{
         ejson(-1,'操作失败');
      }

    }

    public function change_sort(){
      $id=input("get.id",0);
      $menu_id=input("get.menu_id",'');
      $menu=get_menu($menu_id);
      $model_id=$menu['model_id'];
      $model=get_model($model_id);



      $data['sort']=input("post.sort",0);




      $res=db($model['table'])->where("id",$id)->update($data);
      if ($res>0) {
        ejson(1,'操作成功');
      }else{
         ejson(-1,'操作失败');
      }

    }

    public function role_auth(){
      $id=input("get.id",0);
      $menu_id=input("get.menu_id",'');
      $menu=get_menu($menu_id);

      check_auth(11,$menu_id);

      $model_id=$menu['model_id'];
      $model=get_model($model_id);
      $arr=db($model['table'])->where("id",$id)->find();
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

    public function save_role_auth(){
      $id=input("get.id",0);
      $menu_id=input("get.menu_id",0);

      check_auth(11,$menu_id);
      $data=input("post.");
      $data=json_encode($data);
      $map['auth_ids']=$data;
      $res=db("sys_role")->where("id",$id)->update($map);
      if ($res>0) {
        ejson(1,'操作成功');
      }else{
         ejson(-1,'操作失败');
      }
    }



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

