<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
use think\Cache;
class admin extends Common
{   
    public $admin;
    public $admin_id;

    public function _initialize()
     {
 
        $this->assign("title","WXY");
        $this->assign("host_url","http://".$_SERVER['SERVER_NAME']);
        $action       = Request::instance()->action();
        $this->check_admin_login();
        
         
     }


     public function index(){
        return $this->fetch();
     }


     public function test(){
        $arr['size']=1000;
        $arr['file_type']='*.ppt;*.pptx;*.png;*.jpeg;*.jpg;*.PNG';
        echo json_encode($arr);exit;
     }

	
	

}

