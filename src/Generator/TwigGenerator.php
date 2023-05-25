<?php
namespace Pw\Generator;

use Pw\Generator\Helper\CoreHelper;
use Pw\Generator\Helper\FileHelper;

class TwigGenerator {

    const FILE_PAGE = "templates_page.twig.pw";
    const FILE_LAYOUT = "templates_layout.twig.pw";

    public static function getDefaultDir(){
        $project_dir = CoreHelper::getProjectDir();
        return "$project_dir/templates";
    }

    public static function getBasePath($name){
        $base_path = "$name.html.twig";
        return $base_path;
    }

    public static function getTemplatePath($path){
        $template_pos = strpos($path, "templates");

        if(is_int($template_pos)){
            $template_path = substr($path, $template_pos+10);
            return $template_path;
        }

        return $path;
    }

    public static function getPath($controller, $name){
        $default_dir = self::getDefaultDir();
        $controller_subdir = CoreHelper::getControlerSubdir($controller);

        if(!is_int(strpos($name, ".html.twig"))){
            $name = "$name.html.twig";
        }

        if($controller_subdir){
            $path = "$default_dir/$controller_subdir/$name";
        }
        else {
            $path = "$default_dir/$name";
        }

        return $path;
    }

    public static function createLayout($page=null){
        $dir = self::getDefaultDir();

        if($page){
            $dir = dirname($page);
        }

        $path = "$dir/layout/index.html.twig";

        if(is_file($path)){
            return $path;
        }

        $text = FileHelper::getCompiled(self::FILE_LAYOUT);

        FileHelper::create($path, $text);
        
        return $path;
    }

    public static function createTwig($data){
        $get = [CoreHelper::class, "getIn"];

        $name = $get($data, "name");
        $name = CoreHelper::camelToSnake($name);

        $title = $get($data, "title");
        $controller = $get($data, "controller");
        $entrypoint = $get($data, "entrypoint");
        $description = $get($data, "description");
        $webpack_config = $get($data, "webpack_config");

        $path = self::getPath($controller, $name);

        if(is_file($path)){
            return $path;
        }

        if($webpack_config){
            $webpack_config = basename($webpack_config, ".js");
        }

        $layout = self::createLayout($path);
        $layout_tpath = self::getTemplatePath($layout);

        $text = FileHelper::getCompiled(self::FILE_PAGE, [
            "name" => $name,
            "title" => $title,
            "layout" => $layout_tpath,
            "description" => $description,
            "entrypoint" => $entrypoint,
            "webpack_config" => $webpack_config,
        ]);
        
        FileHelper::create($path, $text);

        return $path;
    }
}
