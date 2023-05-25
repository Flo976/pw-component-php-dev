<?php
namespace Pw\Generator;

use Pw\Generator\Helper\CoreHelper;
use Pw\Generator\Helper\FileHelper;

class WebpackGenerator {

    const FILE_CONFIG = "webpack_config.js.pw";
    const FILE_MODULE = "webpack_module.js.pw";
    const FILE_REQUIRE = "webpack_require.js.pw";
    const FILE_WEBPACK = "webpack_webpack.js.pw";

    const FILE_ENCORE = "webpack_encore.yaml.pw";
    const FILE_BUILDS = "webpack_builds.yaml.pw";
    const FILE_PACKAGES = "webpack_packages.yaml.pw";

    public static function getDefaultDir(){
        $project_dir = CoreHelper::getProjectDir();
        return "$project_dir/webpack";
    }

    public static function getFilename($name, $extension=false){
        $path = self::getPath($name);
        
        if($extension){
            return basename($path);
        }

        return basename($path, ".js");
    }

    public static function getPath($name){
        $default_dir = self::getDefaultDir();
        $path = "$default_dir/$name"."_config.js";
        return $path;
    }

    public static function getEncorePath(){
        $project_dir = CoreHelper::getProjectDir();
        $path = "$project_dir/config/packages/webpack_encore.yaml";
        return $path;
    }

    public static function getWebpackPath(){
        $project_dir = CoreHelper::getProjectDir();
        $path = "$project_dir/webpack.config.js";
        return $path;
    }

    public static function createEncore(){
        $path = self::getEncorePath();
        
        if(is_file($path)){
            return $path;
        }

        $text = FileHelper::getCompiled(self::FILE_ENCORE);
        FileHelper::create($path, $text);

        return $path;
    }

    public static function createWebpack(){
        $path = self::getWebpackPath();
        
        if(is_file($path)){
            return $path;
        }

        $text = FileHelper::getCompiled(self::FILE_WEBPACK);
        FileHelper::create($path, $text);

        return $path;
    }

    public static function insertToEncore($name){
        $encore = self::createEncore();

        // builds
        $text = FileHelper::getCompiled(self::FILE_BUILDS, [
            "name" => $name,
        ]);

        FileHelper::insert($encore, $text, "builds");

        // packages
        $text = FileHelper::getCompiled(self::FILE_PACKAGES, [
            "name" => $name,
        ]);
        
        FileHelper::insert($encore, $text, "packages");

        return true;
    }

    public static function insertToWebpack($name){
        $webpack = self::createWebpack();

        // require
        $text = FileHelper::getCompiled(self::FILE_REQUIRE, [
            "name" => $name,
        ]);

        FileHelper::insert($webpack, $text, "require");

        // export.module
        $text = FileHelper::getCompiled(self::FILE_MODULE, [
            "name" => $name,
        ]);
        
        FileHelper::insert($webpack, $text, "module");

        return true;
    }

    public static function createConfig($name){
        $path = self::getPath($name);
        $dir = dirname($path);

        if(is_file($path)){
            return $path;
        }

        $text = FileHelper::getCompiled(self::FILE_CONFIG, [
            "name" => $name,
        ]);

        FileHelper::create($path, $text);

        self::insertToEncore($name);
        self::insertToWebpack($name);
        
       return $path;
    }




}
