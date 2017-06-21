<?php
function get_nav(){
	return db("nav")->where("status",1)->where("up_id",0)->order("sort asc")->select();
}

function get_nav_detail($id=0){
	return db("nav")->where("status",1)->where("id",$id)->find();
}
function get_img($tid=0){
	$arr= db("imgs")->where("tid",$tid)->where("status",1)->find();
	if (!$arr) {
		$arr=[];
	}
	return $arr;
}
function get_imgs($tid=0){
	return db("imgs")->where("tid",$tid)->where("status",1)->select();
}

function get_sub_nav($id=0){
	return db("nav")->where("up_id",$id)->where("status",1)->order("sort asc")->select();
}

function get_list($tid=0,$page_size=8){
	$arr=db("news")->alias("a")->join("kt_nav b","a.tid=b.id")->where("a.tid",$tid)->where("a.status",1)->field("a.title,a.thumb,a.id,a.desc,a.p_time,b.name as tname")->order("a.p_time desc")->paginate($page_size,false,['query'=>input('get.')]);
	return $arr;
}

function get_detail($id=0){
	$arr=db("news")->where("id",$id)->where("status",1)->find();
	return $arr;
}

function get_news($type=1,$order='view_nums',$nums=6){
	return db("news")->alias("a")->join("kt_nav b","a.tid=b.id")->where("a.status",1)->where("a.type",$type)->field("a.title,a.thumb,a.id,a.desc,a.p_time,b.name as tname")->order("$order desc")->limit($nums)->select();

}

function str_to_arr($str='',$delimiter=','){
	$arr=explode($delimiter, $str);
	return $arr;
}

