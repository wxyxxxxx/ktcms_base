<?php
header('Content-Type:text/html;charset=utf-8');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//给图片添加水印
Class Water{
    //开启水印
    private $watermark_on = '1';
    
    public $water_img;
    
    //水印位置
    public $pos = 1;    
    
    //压缩比
    public $pct = 80;
    
    //透明度
    public $quality = 80;
    
    public $text = '易图库 www.ainippt.com';
    
    public $size = 12;
    
    public $color = '#000000';
   
    public $font = "/alidata/www/a-a/kt-project/kt-249/extend/Water/1.ttf";
    
    public function watermark( $img,$pos='',$out_img='',$water_img='',$text='' ){
        if(!$this->check($img) || !$this->watermark_on) return false;
        
        $water_img = $water_img ? $water_img : $this->water_img;
        //水印的开启状态
        $waterimg_on = $this->check($water_img) ? 1 : 0;
        //判断是否在原图上操作
        $out_img = $out_img ? $out_img : $img;
        //判断水印的位置
        $pos = $pos ? $pos : $this->pos;
        //水印文字
        $text = $text ? $text : $this->text;
        
        
        $img_info = getimagesize($img);
        $img_w = $img_info[0];
        $img_h = $img_info[1];
        //判断水印图片的类型
        
        
        if( $waterimg_on ){
            $w_info = getimagesize($water_img);
            $w_w = $w_info[0];
            $w_h = $w_info[1];
            if ( $img_w < $w_w || $img_h < $w_h ) return false;
            switch ( $w_info[2] ){
                case 1:
                    $w_img = imagecreatefromgif($water_img);
                    break;
                case 2:
                    $w_img = imagecreatefromjpeg($water_img);
                    break;
                case 3:
                    $w_img = imagecreatefrompng($water_img);
                    break;
            }
        }else{
            if( empty($text) || strlen($this->color)!=7 ) return FALSE;
            $text_info = imagettfbbox($this->size, 0, $this->font, $text);
            $w_w = $text_info[2] - $text_info[6];
            $w_h = $text_info[3] - $text_info[7];
        }
        
        //建立原图资源
        
        switch ( $img_info[2] ){
            case 1:
                $res_img = imagecreatefromgif($img);
                break;
            case 2:
                $res_img = imagecreatefromjpeg($img);
                break;
            case 3:
                $res_img = imagecreatefrompng($img);
                break;
        }
        //确定水印的位置
        switch ( $pos ){
            case 1:
                $x = $y =25;
                break;
            case 2:
                $x = ($img_w - $w_w)/2; 
                $y = 25;
                break;
            case 3:
                $x = $img_w - $w_w;
                $y = 25;
                break;
            case 4:
                $x = 25;
                $y = ($img_h - $w_h)/2;
                break;
            case 5:
                $x = ($img_w - $w_w)/2; 
                $y = ($img_h - $w_h)/2;
                break;
            case 6:
                $x = $img_w - $w_w;
                $y = ($img_h - $w_h)/2;
                break;
            case 7:
                $x = 25;
                $y = $img_h - $w_h;
                break;
            case 8:
                $x = ($img_w - $w_w)/2;
                $y = $img_h - $w_h;
                break;
            case 9:
                $x = $img_w - $w_w;
                $y = $img_h - $w_h;
                break;
            default :
                $x = mt_rand(25, $img_w - $w_w);
                $y = mt_rand(25, $img_h - $w_h);
        }
        
        //写入图片资源
        if( $waterimg_on ){
            imagecopymerge($res_img, $w_img, $x, $y, 0, 0, $w_w, $w_h, $this->pct);  
    }else{
        $r = hexdec(substr($this->color, 1,2));
        $g = hexdec(substr($this->color, 3,2));
        $b = hexdec(substr($this->color, 5,2));
        $color = imagecolorallocate($res_img, $r, $g, $b);
        imagettftext($res_img, $this->size, 0, $x, $y, $color, $this->font, $text);    
    }
    
    //生成图片类型
    switch ( $img_info[2] ){
        case 1:
            imagecreatefromgif($res_img,$out_img);
            break;
        case 2:
            //imagecreatefromjpeg($res_img,$out_img);
            imagejpeg($res_img,$out_img);
            break;
        case 3:
            imagejpeg($res_img,$out_img);
            break;
    }
    if(isset($res_img)) imagedestroy ($res_img);
    if(isset($w_img))   imagedestroy($w_img);
    return TRUE;
}   
    //验证图片是否存在
        private function check($img){
            $type = array('.jpg','.jpeg','.png','.gif');
            $img_type = strtolower(strrchr($img, '.'));
            return extension_loaded('gd') && file_exists($img) && in_array($img_type, $type);
        } 
}