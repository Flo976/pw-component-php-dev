<?php
namespace Pw\Generator;

use Pw\Generator\Helper\CoreHelper;
use Pw\Generator\Helper\FileHelper;

class MethodGenerator {

    const FILE_API = "method_api.php.pw";
    const FILE_PAGE = "method_page.php.pw";
    const FILE_DEFAULT = "method_default.php.pw";

    public static function insertDefaultMethod($path, $data){
        return self::insertMethod(self::FILE_DEFAULT, $path, $data);
    }

    public static function insertApiMethod($path, $data){
        $get = [CoreHelper::class, "getIn"];
        $route_methods = $get($data, "route_methods");

        if(!$route_methods){
            $data["route_methods"] = "POST";
        }

        return self::insertMethod(self::FILE_API, $path, $data);
    }

    public static function insertPageMethod($path, $data){
        $get = [CoreHelper::class, "getIn"];
        $route_methods = $get($data, "route_methods");

        if(!$route_methods){
            $data["route_methods"] = "GET";
        }

        return self::insertMethod(self::FILE_PAGE, $path, $data);
    }

    private static function insertMethod($file, $path, $data){
        $get = [CoreHelper::class, "getIn"];

        $route_url = $get($data, "route_url");
        $route_name = $get($data, "route_name");
        $route_methods = $get($data, "route_methods");
        $name = lcfirst($get($data, "name"));
        $params = $get($data, "params");
        $twig = $get($data, "twig");
        $controller = basename($path, ".php");

        if(!$params){
            $params = 'Request $request';
        }

        $text = FileHelper::getCompiled($file, [
            "route_url" => $route_url,
            "route_name" => $route_name,
            "route_methods" => $route_methods,
            "name" => $name,
            "params" => $params,
            "twig" => $twig,
            "controller" => $controller,
        ]);

        FileHelper::insert($path, $text, "php");

        return true;
    }
}
