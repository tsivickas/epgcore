<?php
namespace epgcore;

class utils
{

    public static function ejson($duom, $exit = true)
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: application/json");
        echo (empty($duom)) ? '{}' : json_encode($duom);
        if ($exit) exit;
    }
    
    public static function max_file_upload_in_bytes() {
        
        $return_bytes = function($val) {
            $val = (int) trim($val);
            $last = strtolower($val[strlen($val)-1]);
            switch($last) 
            {
                case 'g':
                $val *= 1024;
                case 'm':
                $val *= 1024;
                case 'k':
                $val *= 1024;
            }
            return $val;
        };
        
        //select maximum upload size
        $max_upload = $return_bytes(ini_get('upload_max_filesize'));
        //select post limit
        $max_post = $return_bytes(ini_get('post_max_size'));
        //select memory limit
        $memory_limit = $return_bytes(ini_get('memory_limit'));
        // return the smallest of them, this defines the real limit
        return min($max_upload, $max_post, $memory_limit);
    }

    public static function human_filesize($size, $precision = 2) {
        $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision).$units[$i];
    }
    
}    

?>
