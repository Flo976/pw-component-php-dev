<?php
namespace Pw\Generator\Helper;

use Exception;

class FileHelper {

    public static function getCompiled($filename, $params=[]){
        $project_dir = CoreHelper::getProjectDir();

        $left = 1 + strpos($filename, ".");
        $right = strrpos($filename, ".");

        $size = $right - $left;
        $type = substr($filename, $left, $size);

        $path = "$project_dir/src/pw_models/$type/$filename";

        if(is_file($path)){
            $text = file_get_contents($path);
            $text = CoreHelper::applyParams($text, $params);
            return $text;
        }

        return "";
    }

    public static function getLineOfInterest($line, $index, $type){
        if(($type == "php") && is_int(strpos($line, "}"))){
            return $index;
        }
        else if(($type == "entrypoint") && is_int(strpos($line, "addEntry"))){
            return $index + 1;
        }
        else if(($type == "require") && is_int(strpos($line, "require"))){
            return $index + 1;
        }
        else if(($type == "module") && is_int(strpos($line, "]"))){
            return $index;
        }
        else if(is_int(strpos($line, $type))){
            return $index + 1;
        }

        return null;
    }

    public static function create($path, $text){
        if(is_file($path)){
            return true;
        }

        $dir = dirname($path);

        if(!file_exists($dir)){
            mkdir($dir, 0775, true);
        }

        file_put_contents($path, $text);

        return true;
    }

    public static function insert($path, $text, $type){
        if(!is_file($path)){
            throw new Exception("FileHelper:insert - file not found");
        }

        $lines = file($path);
        $length = count($lines);

        for($i=$length-1; 0<=$i; $i--){
            $line_of_interest = self::getLineOfInterest(
                $lines[$i], 
                $i, 
                $type
            );

            if(is_int($line_of_interest)){
                array_splice($lines, $line_of_interest, 0, [$text]);
                break;
            }
        }

        file_put_contents($path, join("", $lines));

        return true;
    }
}
