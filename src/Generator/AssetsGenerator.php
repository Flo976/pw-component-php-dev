<?php
namespace Pw\Generator;

use Pw\Generator\Helper\CoreHelper;
use Pw\Generator\Helper\FileHelper;

class AssetsGenerator {

    const SUFFIX = "";

    const FILE_MAIN = "assets_main.js.pw";
    const FILE_INDEX = "assets_index.js.pw";
    const FILE_CONFIG = "assets_config.js.pw";
    const FILE_ENTRYPOINT = "assets_entrypoint.js.pw";
    
    const FILE_JSX = "assets_component.jsx.pw";
    const FILE_SCSS = "assets_component.scss.pw";

    public static function createFiles($controller, $name){
        $jsx = self::createJsx($controller, $name);
        $scss = self::createScss($controller, $name);

        $main = self::createMain($jsx);
        $index = self::createIndex($jsx);
        $config = self::createConfig($jsx);

        return [
            "jsx" => $jsx,
            "scss" => $scss,
            "main" => $main,
            "index" => $index,
            "config" => $config,
        ];
    }

    public static function getAssetsPath($path){
        $assets_pos = strpos($path, "assets");

        if(is_int($assets_pos)){
            $assets_path = substr($path, $assets_pos+7);
            return $assets_path;
        }

        return $path;
    }

    public static function getComponentsPath($controller, $name, $suffix){
        $project_dir = CoreHelper::getProjectDir();
        $controller_subdir = CoreHelper::getControlerSubdir($controller);

        $default_path = "$project_dir/assets/vue/components/modules";

        $parent = $name;
        $filename = $name.$suffix;

        if($controller_subdir){
            $path = "$default_path/$controller_subdir/$parent/$filename";
        }
        else {
            $path = "$default_path/$parent/$filename";
        }

        return $path;
    }

    public static function createJsx($controller, $name){
        $name = ucfirst($name).(self::SUFFIX);
        $path = self::getComponentsPath($controller, $name, ".jsx");

        if(is_file($path)){
            return $path;
        }

        $text = FileHelper::getCompiled(self::FILE_JSX, [
            "name" => $name,
        ]);

        FileHelper::create($path, $text);
        
        return $path;
    }

    public static function createScss($controller, $name){
        $name = ucfirst($name).(self::SUFFIX);
        $path = self::getComponentsPath($controller, $name, ".scss");

        if(is_file($path)){
            return $path;
        }

        $text = FileHelper::getCompiled(self::FILE_SCSS);

        FileHelper::create($path, $text);
        
        return $path;
    }

    public static function getModulesPath($component, $suffix){
        $project_dir = CoreHelper::getProjectDir();
        $default_path = "$project_dir/assets/modules";

        $filename = self::getModulesFilename($component, $suffix);
        $component_subdir = CoreHelper::getComponentSubdir($component);

        if($component_subdir){
            $path = "$default_path/$component_subdir/$filename";
        }
        else {
            $path = "$default_path/$filename";
        }

        return $path;
    }

    public static function getModulesFilename($component, $suffix){
        $component_name = basename($component, ".jsx");
        $component_name = CoreHelper::camelToSnake($component_name);

        $filename = $component_name.$suffix;
        return $filename;
    }

    public static function createConfig($component){
        $path = self::getModulesPath($component, "_config.js");

        if(is_file($path)){
            return $path;
        }

        $text = FileHelper::getCompiled(self::FILE_CONFIG);

        FileHelper::create($path, $text);
        
        return $path;
    }

    public static function createIndex($component){
        $path = self::getModulesPath($component, "_index.js");

        if(is_file($path)){
            return $path;
        }

        $main_filename = self::getModulesFilename($component, "_main.js");

        $text = FileHelper::getCompiled(self::FILE_INDEX, [
            "main_filename" => $main_filename,
        ]);

        FileHelper::create($path, $text);
        
        return $path;
    }

    public static function createMain($component){
        $path = self::getModulesPath($component, "_main.js");

        if(is_file($path)){
            return $path;
        }

        $component_name = basename($component, ".jsx");
        $component_path = self::getAssetsPath($component);

        $config_filename = self::getModulesFilename($component, "_config.js");

        $text = FileHelper::getCompiled(self::FILE_MAIN, [
            "component_name" => $component_name,
            "component_path" => $component_path,
            "config_filename" => $config_filename,
        ]);

        FileHelper::create($path, $text);
        
        return $path;
    }
   
    public static function addEntrypoint($config, $index){
        $name = basename($index, "_index.js");

        $path = self::getAssetsPath($index);
        $path = "./assets/$path";

        $text = FileHelper::getCompiled(self::FILE_ENTRYPOINT, [
            "name" => $name,
            "path" => $path,
        ]);

        FileHelper::insert($config, $text, "entrypoint");

        return $name;
    }
}
