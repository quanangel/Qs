<?php
/**
 * File Name:Image.php
 * Auth:Qs
 * Name:生成水印功能
 * Note:主要使用PHP GD扩展
 * Time:2018/1/3  10:33
 **/
namespace Qs\other;

Class Image{

    // 是否开启水印，默认0为不开启、1为文字水印、2为图片水印、3为文字图片混合水印
    private $watermark_type = 0;
    // 水印图片地址
    private $watermark_img;
    // 水印文字
    private $watermark_text = 'Qs';
    // 水印位置
    private $watermark_position = 1;
    // 水印旋转角度，默认角度0
    private $watermark_angle = 0;
    // 水印透明度，默认为100%
    private $watermark_transparency = 100;
    // 水印字体颜色透明度，默认80%
    private $font_alpha = 80;
    // 字体样式，默认黑体
    private $font = 'c:/Windows/Fonts/simhei.ttf';
    // 字体颜色，默认为黑色
    private $font_color = '#000000';
    // 字体大小，默认为16px
    private $font_size = 16;
    // 生成图片后缀
    private $output_image_type = 'jpg';

    
    public function get_gd_info(){
        return gd_info();
    }

    // 实例
    protected static $handler;

    public function __construct($options){

        foreach ( $options as $name => $value ) { if ( property_exists($this, $name) ) $this->$name = $value; }
    }

    /**
     * Auth:Qs
     * Name:实例化后用于该实例设置变量
     * Note:
     * Time:2018/1/3  14:26
     **/
    public function set_value($options = []){
        foreach ( $options as $name => $value ) { if ( property_exists($this, $name) ) $this->$name = $value; }
        return true;
    }

    /**
     * Auth:Qs
     * Name:实例化
     * Note:
     * Time:2018/1/3  11:55
     **/
    public static function handler($options = []) {
        if (is_null(self::$handler)) self::$handler = new static($options);
        return self::$handler;
    }


    public function add_watermark($old_image,$output_image = '') {
        $old_image_info = getimagesize($old_image);  // 获取图像的相关信息
        $old_image_data = imagecreatefromstring(file_get_contents($old_image)); // 从字符串中的图像流新建一图像

        $watermark_info = getimagesize($this->watermark_img); // 获取水印图像信息
        $watermark_data = imagecreatefromstring(file_get_contents($this->watermark_img)); // 获取水印图像信息
        // 水印X,Y位置
        $x_y = $this->position_x_y($this->watermark_position, $old_image_info[0], $old_image_info[1], '','');

        $r = hexdec(substr($this->font_color, 1,2)); // 根据16进制的颜色码提取RGB模式里的R
        $g = hexdec(substr($this->font_color, 3,2)); // 根据16进制的颜色码提取RGB模式里的G
        $b = hexdec(substr($this->font_color, 5,2)); // 根据16进制的颜色码提取RGB模式里的B
        $color=imagecolorallocatealpha($old_image_data,$r,$g,$b,$this->font_alpha); // 为一幅图像分配颜色
        $new_image_date = null;

        // 加水印
        switch ( $this->watermark_type ) {
            case 1 :
                imagettftext($old_image_data, $this->font_size, $this->watermark_angle, $x_y['x'], $x_y['y'], $color, $this->font, $this->watermark_text);
                break;
            case 2 :
                imagecopymerge($old_image_data, $watermark_data, $x_y['x'], $x_y['y'], 0, 0, $watermark_info[0], $watermark_info[1], $this->watermark_transparency);
                break;
            case 3 :
                imagettftext($old_image_data, $this->font_size, $this->watermark_angle, $x_y['x'], $x_y['y'], $color, $this->font, $this->watermark_text);
                imagecopymerge($old_image_data, $watermark_data, $x_y['x'], $x_y['y'], 0, 0, $watermark_info[0], $watermark_info[1], $this->watermark_transparency);
                break;
            default : break;
        }

        // 生成图片
        switch ( $this->output_image_type ) {
            case 'jpg' : imagejpeg($old_image_data, $output_image); break;
            case 'png' : imagepng($old_image_data, $output_image); break;
            case 'gif' : imagegif($old_image_data, $output_image); break;
            default : break;
        }
        return true;
    }

    /**
     * Auth:Qs
     * Name:获取定位X,Y位置
     * Note:
     * Time:2018/1/4  11:15
     **/
    private function position_x_y($watermark_position, $output_width, $output_height, $watermark_width, $watermark_height){
        // 水印位置
        $watermark_position = $watermark_position ? : $this->watermark_position;
        $data = [];
        switch ( $watermark_position ) {
            // 中间
            case 1 :
                $data['x'] = round(($output_width-$watermark_width)/2);
                $data['y'] = round(($output_height - $watermark_height)/2);
                break;
            // 左上角
            case 2 : $data['x'] = 0; $data['y'] = $watermark_height; break;
            // 右上角
            case 3 : $data['x'] = round( $output_width - $watermark_width ); $data['y'] = $watermark_height; break;
            // 左下角
            case 4 : $data['x'] = 0; $data['y'] = round( $output_height - $watermark_height); break;
            // 右下角
            case 5 : $data['x'] = round( $output_width - $watermark_width ); $data['y'] = round($output_height - $watermark_height); break;
            // 默认值
            default : $data['x'] = $data['y'] = 0; break;
        }
        return $data;
    }

    public function try_show_image($image, $mime){
        header('Content-Type: '.$mime);
        imagepng($image);
        imagedestroy($image);
    }

    private function base64_image($image,$mime){
        $image = file_get_contents($image);
        $image = chunk_split(base64_encode($image));
        $data = 'data:' . $mime . ';base64,' . $image;
        return $data;
    }

    /**
     * Auth:Qs
     * Name:检查图片是否存在
     * Note:
     * Time:2018/1/3  11:08
     **/
    private function check_img($img){
        $type = array('.jpg','.jpeg','.png','.gif');
        $img_type = strtolower(strrchr($img, '.'));
        return extension_loaded('gd') && file_exists($img) && in_array($img_type, $type);
    }
}