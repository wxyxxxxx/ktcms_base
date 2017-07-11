<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
use think\Cache;
class develop extends Base
{   

   

     public function index(){
        return $this->fetch();
     }




//-------------------------------------------------------------数据库管理开始 ----------------------------------
//*********************************************
//*********************************************
//*********************************************

    public function table_list(){
      // $arr=Db::name("sys_table")->select();
      $arr=Db::query("show table status");
      // wlog($arr);
      // dump($arr);
      $this->assign("list",$arr);
      return $this->fetch();
    }
    public function table_edit(){
      $id=input("get.id",0);
      $table=input("post.table",'aaa');
      $comment=input("post.comment",'');
      $engine=input("post.engine",'InnoDb');
      $arr=Db::query("show tables");
      foreach ($arr as $key => $e) {
        if ($table==$e["Tables_in_".config("database.database")]) {
           ejson(-1,'数据表已存在');
        }
      }
     
      $sql="CREATE TABLE `$table` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`id`)) ENGINE=$engine DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='".$comment."';";
      $res=Db::query($sql);
      ejson(1,'新增成功');
    }

    public function table_delete(){
      $table=input("post.table",'');
      $sql="DROP TABLE `$table`;";
      if (in_array($table, [config("database.prefix").'sys_table',config("database.prefix").'sys_logs',config("database.prefix").'sys_fields',config("database.prefix").'sys_admin',config("database.prefix").'sys'])) {
        ejson(-1,'系统表不可删除');
      }
      $res=Db::query($sql);
      ejson(1,'删除数据表【'.$table.'】成功');
    }

    public function table_desgin(){
      $table=input("get.table",'');
      if ($table==config("database.prefix")) {
        echo "请先设置数据表";exit;
      }
      $arr = Db::query("show full columns from `$table`");
      wlog($arr);
      

      foreach ($arr as &$e) {
         // $e['Type']=rtrim($e['Type'],')');
         $arr2=explode("(", $e['Type']);
         $e['Type']=$arr2[0];
         if (isset($arr2[1])) {
           $arr3=explode(")", $arr2[1]);
           $e['Length']=$arr3[0];
         }else{
          $e['Length']='';
         }

      }
      // dump($arr);
      $this->assign("list",$arr);
      $this->assign("table",$table);
      return $this->fetch();
    }

    public function table_model(){
      $table=input("get.table",'');
      
      $arr=Db::table($table)->order("sort desc")->select();
      $this->assign("list",$arr);
      $this->assign("table",$table);
     
      return $this->fetch();
    }



    public function save_field(){
        $table=input("get.table",'');
        $data=input("post.");
        if ($data['cz']==0) {
          $op="ADD";
        }else{
          $op="CHANGE `".$data['old_field']."`";
        }
        if (!isset($data['default'])||$data['default']=='') {
          $default='DEFAULT NULL';
        }else{
          $default="DEFAULT '".$data['default']."'";
        }
        if ($data['length']=='') {
          $length='';
        }else{
          $length="(".$data['length'].")";
        }
        // dump($default);exit;
        $sql="ALTER TABLE `".$table."` $op `".$data['field']."` ".$data['type'].$length."  COMMENT '".$data['comment']."' $default ;";
        $res=Db::query($sql);
        ejson(1,'保存成功');
      // ALTER TABLE `a` ADD `cc` INT(11) NULL DEFAULT '33' AFTER `bb`, ADD PRIMARY KEY (`cc`);
    }

    public function del_field(){
        $table=input("get.table",'');
        $data=input("post.");
        $sql="ALTER TABLE `".$table."` DROP COLUMN `".$data['field']."` ;";
        $res=Db::query($sql);
        ejson(1,'删除成功');

    }

    public function table_data(){
      $tb=input("get.table",'');
      $fields = Db::query("show full columns from `$tb`");

      
      $arr= Db::table($tb)->order("id asc")->paginate(10);
      // dump($arr);exit;
      $this->assign("list",$arr);
      $this->assign("fields",$fields);
      $this->assign("table",$tb);
  
      return $this->fetch();
    }


    //-------------------------------------------------------------数据库管理结束 ----------------------------------
    //*********************************************
    //*********************************************
    //*********************************************



    public function sys_menu(){
        $arr=Db::name("sys_menu")->select();
        $this->assign("list",$arr);
        $this->assign("tb","sys_menu");
        return $this->fetch();
    }

    public function sys_model(){
        $arr=Db::name("sys_model")->select();
        $this->assign("list",$arr);
        $this->assign("tb","sys_menu");
        return $this->fetch();
    }

  

    public function model_fields(){
        $model_id=input("get.model_id",0);

        $model=Db::name("sys_model")->where("id",$model_id)->find();
        if (!isset($model['table'])) {
          echo "请先选择模型";exit;
        }
        $arr=Db::name("sys_fields")->where("model_id",$model_id)->order("sort asc")->select();
        $tab=Db::name("sys_field_tab")->where("mid",$model_id)->order("sort asc")->select();
        // dump($tab);exit;
        $this->assign("list",$arr);
        $this->assign("tb","sys_fields");
        $this->assign("model_id",$model_id);
        $this->assign("model",$model);
        $this->assign("tab",$tab);
        return $this->fetch();
    }

    public function save_model_fields(){
        $id=input("get.id",'');
        $data=input("post.");
        $arr=Db::name("sys_fields")->where('model_id',$data['model_id'])->where("field",$data['field'])->find();
        if ($id>0) {
          Db::name("sys_fields")->where("id",$id)->update($data);
        }else{
          if ($arr) {
            ejson(-1,'已存在');exit;
          }
          $data['c_time']=time();
          $id=Db::name("sys_fields")->insertGetId($data);
        }
        
        ejson(1,'保存成功',['id'=>$id]);
      // ALTER TABLE `a` ADD `cc` INT(11) NULL DEFAULT '33' AFTER `bb`, ADD PRIMARY KEY (`cc`);
    }

    public function del_model_fields(){
        $id=input("get.id",'');
        
        Db::name("sys_fields")->where("id",$id)->delete();
        
        ejson(1,'删除成功');

    }

    //  附件管理
    public function files_list(){
      $path=input("get.path",'');
      if ($path!='') {
        $path=urldecode($path).'/';
      }else{
        // $path=ROOT_PATH.'public/';
        $path=ROOT_PATH;
      }

      $a_path=str_replace(ROOT_PATH, '', $path);
      $a_path='11'.$a_path;
      
      if (strpos($a_path,'11public')!==false) {
        $a_path=ltrim($a_path,'11public');
        
      }else{
        $a_path=ltrim($a_path,'11');
      }
      // dump($a_path);exit;
      $arr=scandir($path);
      // dump($arr);exit;
      $new_arr=[];
      foreach ($arr as $key => $e) {
        if ($e=='..'||$e=='.') {
          continue;
        }
        $str=explode('.', $e);
        $file['u_time']=date("Y-m-d H:i:s",filemtime($path.$e));
        $file['path']=$path.$e;
        $file['name']=$e;
        $file['size']=0;
        $file['link']=$a_path.$e;

        
        if (!isset($str[1])) {
          if (is_dir($path.$e)) {
            $file['type']=1;
            $file['ext']='files';
            
          }else{
            $file['type']=0;
            $file['ext']='';
          }
        }else{
            $file['type']=2;
            $file['size']=round((filesize($path.$e)/1024),2);
            $file['ext']=$str[1]; 
        }

        $new_arr[]=$file;
      }
      $new_arr=arr_sort($new_arr,'type');
      // dump($new_arr);exit;
      $this->assign("list",$new_arr);
      $this->assign("file_path",$path);
      $this->assign("a_path",$a_path);
      return $this->fetch();
    }

    public function code_edit(){
      $path=input("get.path",'');
      $path=urldecode($path);
      $file_path = $path;
      if(file_exists($file_path)){
      $str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
      
      }
      $this->assign("list",$str);
      $this->assign("file_path",$file_path);
      return $this->fetch();
    }
    
  public function do_save_file(){
    $path=input("post.path",'');
    $data=input("post.data",'');
    $path=urldecode($path);
    if (is_writable($path)) {
        $res=file_put_contents($path,$data);
    }
    // dump($res);exit;
    if ($res>0) {
     ejson(1,'保存成功');
    }else{
      ejson(1,'保存失败');
    }
  }


  public function add_file(){
    $path=input("post.file_path",'');
    $file_name=input("post.file_name",'');
    $path=urldecode($path);
    $str=explode('.', $file_name);
    $full_path=$path.$file_name;
    // dump($full_path);exit;
    if (isset($str[1])) {
      $fp=fopen($full_path,"w+");
      fclose($fp);
      ejson(1,$file_name.'文件创建成功');
    }else{
      if (!file_exists($full_path)) {
        mkdir($full_path,0777,true);
        ejson(1,$file_name.'目录创建成功');
      }else{
        ejson(-1,'目录已存在');
      }
    }
  }

  public function del_file(){
    $path=input("post.file_path",'');
    // $file_name=input("post.file_name",'');
    $path=urldecode($path);
    $str=explode('.', $path);
    // $full_path=$path.$file_name;
    // dump($path);exit;
    ejson(-1,$path.'禁止删除');
    if (isset($str[1])) {
      if (is_file($path)) {
        unlink($path);
      }else{
        ejson(-1,$path.'文件删除失败');
      }
      ejson(1,$path.'文件删除成功');
    }else{
      if (is_sys_dir($path)) {
        ejson(-1,$path.'目录禁止删除');
      }
      // dump(is_sys_dir($path));exit;
      if (delDirAndFile($path,true)) {
        
        ejson(1,$path.'目录删除成功');
      }else{
        ejson(-1,'目录删除失败');
      }
    }
  }
    

}


function is_sys_dir($path=''){
  $path=rtrim($path,'/');
  $arr=explode('/', $path);
  $last=$arr[count($arr)-1];
  $sys_arr=['thinkphp','application','public','vendor','extend'];
  if (in_array($last, $sys_arr)) {
    return true;
  }else{
    return false;
  }
}
/**
 * 删除目录及目录下所有文件或删除指定文件
 * @param str $path   待删除目录路径
 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
 * @return bool 返回删除状态
 */
function delDirAndFile($path, $delDir = FALSE) {
    $handle = opendir($path);
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir)
            return rmdir($path);
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
}

