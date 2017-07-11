<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
use think\Cache;
class file extends Base
{   
    public $admin;
    public $admin_id;

  

     // public function nav_list(){
        
     // }
     public function index(){
        return $this->fetch();
     }

    
	

}

