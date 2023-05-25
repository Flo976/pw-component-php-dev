<?php
namespace Pw\Generator\Helper;

use Exception;

class CoreHelper {

    const DOMAINS = [
        "admin",
        "front",
        "member",
    ];

    public static function applyParams($text, $params=[]){
        if(!$text || !is_array($params)){
            return $text;
        }
      
        foreach($params as $key => $value){
            $value = $value ? $value : "";
            $text = str_replace('{{'.$key.'}}', $value, $text);
        }

        while(is_int($left = strrpos($text, '{{'))){
            $len = strlen($text);

            $right = strrpos($text, '}}');
            $right = ($right > 0) ? $right+2 : $left+2;

            $safe = substr($text, 0, $left);
            $safe = $safe.substr($text, $right, $len - $right);

            $text = $safe;
        }

        $text = str_replace('[[:brace]]', '{{', $text);
        $text = str_replace('[[brace:]]', '}}', $text);
       
        return $text;
    }

    public static function getDomain($controller){
        $subdir = self::getControlerSubdir($controller);

        if($subdir){
            return explode("/", $subdir)[0];
        }

        throw new Exception("CoreHelper::getDomain - domain not found");
    }

    public static function getControlerSubdir($path){
        $dir = dirname($path);
        $controller_pos = strpos($dir, "Controller");

        if(is_int($controller_pos)){
            $subdir = substr($dir, $controller_pos+11);

            if($subdir){
                $subdir = strtolower($subdir);
                return $subdir;
            }

            $subdir = basename($path, "Controller.php");
            $subdir = strtolower($subdir);

            if(in_array($subdir, self::DOMAINS)){
                return $subdir;
            }
        }

        return null;
    }

    public static function getComponentSubdir($path){
        $dir = dirname($path, 2);
        $modules_pos = strrpos($dir, "modules");

        if(is_int($modules_pos)){
            $subdir = substr($dir, $modules_pos+8);
            $subdir = strtolower($subdir);
            return $subdir;
        }

        return null;
    }

    public static function getIn($array, $key, $default=null){
        if(is_array($array) && $key){
            if(strpos($key, '|') > 0){
                list($field, $prop) = explode('|', $key);
                
                if(isset($array[$field][$prop]) && $array[$field][$prop]){
                    return $array[$field][$prop];
                }
            }
            else if(isset($array[$key]) && $array[$key]){
                return $array[$key];
            }
        }
        return $default;
    }

    public static function isPath($text){
        if(is_int(strpos($text, "/"))){
            return true;
        }

        return false;
    }

    public static function camelToSnake($text){
        $regex = ['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'];
        return strtolower(preg_replace($regex, '$1_$2', $text));
    }

    public static function getProjectDir(){
        return getcwd();
    }
}
