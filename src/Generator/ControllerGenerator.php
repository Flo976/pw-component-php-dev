<?php
namespace Pw\Generator;

use Pw\Generator\Helper\CoreHelper;
use Pw\Generator\Helper\FileHelper;

class ControllerGenerator {

    const PSR4 = "App";
    const FILE = "class_controller.php.pw";

    public static function getDefaultDir(){
        $project_dir = CoreHelper::getProjectDir();
        return "$project_dir/src/Controller";
    }

    public static function getFilename($name, $extension=false){
        $path = self::getPath($name);
        
        if($extension){
            return basename($path);
        }

        return basename($path, ".php");
    }

    public static function getPath($name){
        $default_dir = self::getDefaultDir();
        $project_dir = CoreHelper::getProjectDir();

        if(!is_int(strpos($name, ".php"))){
            $name = "$name.php";
        }

        if(CoreHelper::isPath($name)){
            $path = "$project_dir/$name";
            return $path;
        }
        else {
            $domain = explode("Controller", $name)[0];
            
            if(in_array(strtolower($domain), CoreHelper::DOMAINS)){
                return "$default_dir/$domain/$name";
            }
        }

        return "$default_dir/$name";
    }

    public static function getNamespace($name){
        $path = self::getPath($name);
        $path = dirname($path);

        $src_pos = strpos($path, "src");
        
        $namespace = self::PSR4.substr($path, $src_pos+3);
        $namespace = str_replace('/', '\\', $namespace);

        return $namespace;
    }

    public static function createController($name){
        $path = self::getPath($name);
        $dir = dirname($path);

        if(is_file($path)){
            return $path;
        }

        $text = FileHelper::getCompiled(self::FILE, [
            "name" => self::getFilename($name),
            "namespace" => self::getNamespace($name),
        ]);

        FileHelper::create($path, $text);

        return $path;
    }




}
