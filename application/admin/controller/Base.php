<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
use think\Cache;
define("PREFIX", config("database.prefix"));
class base extends Controller
{   
    public $admin;
    public $admin_id;
    public $sys_config;


    public function _initialize()
     {
      // dump(PREFIX);exit;
        header("Content-type: text/html; charset=utf-8");
        $this->assign("title","KTCMS");
        $this->assign("host_url","http://".$_SERVER['SERVER_NAME']);
        $action       = Request::instance()->action();
        $this->check_admin_login();
        $this->sys_config=get_sys_config();
        $this->assign(['sys_config'=>$this->sys_config]);
        // get_nav();

     }

 



    public function check_admin_login(){

        $admin_id=session("admin_id");
        if ($admin_id>0) {
           $admin_name=session("account");
           $arr=db("sys_admin")->alias("a")->join(PREFIX."sys_role b","a.role_id=b.id")->where("a.id",$admin_id)->field("a.account,a.id,a.role_id,b.auth_ids,b.name as role_name")->find();

           $admin_type=session("admin_type");
           if ($admin_type==1) {
             $arr['auth_ids']=get_full_auth_ids();
           }
           session('admin',$arr);
           $this->assign("arr",$arr);
           $this->assign("admin_name",$admin_name);
           $this->assign("admin",$arr);
           $this->admin_id=$admin_id;
        }else{
            $this->redirect("admin/login/index");exit;
        }
    }

 

    public function login_out(){
      session("admin_id",null);
      session_destroy();
      $this->redirect("/kt");
    }




    
    

}



