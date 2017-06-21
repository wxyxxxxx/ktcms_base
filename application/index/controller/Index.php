<?php
namespace app\index\controller;

use think\Cache;
use think\Controller;
use think\Db;
use think\Request;

class Index extends Controller
{

  
    public function _initialize()
    {
        header("Content-type:text/html;charset=utf-8");
        header('Access-Control-Allow-Origin:*');
        $sys=get_sys_set();
        $this->assign('sys',$sys);
        $this->assign("host", "http://" . $_SERVER['SERVER_NAME']);
  		$this->assign("path","/templets/default/index/");

        $this->assign('nav_list',nav());
 
    }

    public function index2(){
        $sid='5177';
        $Cataid='1';
        $Username='wxy112233';
        $Password='w112233';
        $Name='小明';
        $Email='1048277366@qq.com';
        $Province='中国';
        $Tel='13253631415';
        $Time=date("YmdHis");
        $Md5Str=md5($sid.$Cataid.$Username.$Password.$Name.$Email.$Province.$Tel.$Time);
        $str=UrlEnCode($Md5Str.'$'.$sid.'$'.$Cataid.'$$$$$$$$'.$Username.'$'.$Password.'$'.$Name.'$'.$Email.'$'.$Province.'$'.$Tel.'$'.$Time);
        // $str=UrlEnCode('Md5Str='.$Md5Str.'&sid='.$sid.'&Cataid='.$Cataid.'&Username='.$Username.'&Password='.$Password.'&Name='.$Name.'&Email='.$Email.'&Province='.$Province.'&Tel='.$Tel.'&Time='.$Time);

        $res=curl_get($str);
        // dump($Time);exit;
        dump($res);exit;
        exit;
    	return $this->fetch('index');
    }

    public function index(){

    	$id=input("get.id",0);

        $nid=$this->nav_info($id);
    	$arr=db("nav")->where("id",$nid)->where("status",1)->find();
        // dump($arr);exit;
    	if (!$arr) {
            $arr=db("nav")->where("status",1)->where("up_id",0)->order("sort asc")->find();
    		// $this->error("内容不存在");
    	}
        // dump($arr);exit;
        $this->assign('nav',$arr);
    	
    	return $this->fetch($arr['templet']);
    }

    public function detail(){

        $id=input("get.id",1);
        $news=db("news")->where("id",$id)->find();
        $nid=$this->nav_info($news['tid']);
        $arr=db("nav")->where("id",$nid)->find();
        if (!$arr) {
            $this->error("内容不存在");
        }

       
        $this->assign('nav',$arr);
        $this->assign('news',$news);
        return $this->fetch($arr['templet_detail']);
    }

    public function nav_info($id=0){
        $arr=get_top_nav($id);
        $arr=array_reverse($arr);
        $this->assign('up_nav',$arr);
        return $arr[0]['id'];
    }

    public function add_suggest(){
        $data=input("post.");
        $data['c_time']=time();
        db("suggest")->insert($data);
        ejson(1,'提交成功');
    }
    public function add_yxd(){
        $data=input("post.");
        $data['c_time']=time();
        db("yxd")->insert($data);
        ejson(1,'提交成功');
    }

}
    
    function nav(){
        $arr=db("nav")->where("status",1)->where("up_id",0)->order("sort asc")->select();
        return $arr;
    }
    
    function get_location($id=0){
        $arr=get_top_nav($id);
        $arr=array_reverse($arr);
        foreach ($arr as $key => $e) {
            
        }
       
    }


    function get_top_nav($id=0,&$new_arr=array()){
        $arr=db("nav")->where("id",$id)->field("name,sub_name,id,up_id,sort")->find();
        $new_arr[]=$arr;
        if ($arr['up_id']>0) {
            get_top_nav($arr['up_id'],$new_arr);
        }
        return $new_arr;
    }
    // function get_top_nav($id=0,&$nid=0){
    //     $arr=db("nav")->where("id",$id)->find();

        
    //     if ($arr['up_id']>0) {
    //         $nid=$arr['up_id'];
    //         $arr['parent']=get_top_nav($arr['up_id'],$nid);
    //     }
    //     $arr['top_id']=$nid;
    //     unset($nid);
    //     return $arr;
    // }


    function curl_get($str=''){
        $url="http://www.edu24ol.com/interface/public/Return.asp?en=".$str;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                ));
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

    }

