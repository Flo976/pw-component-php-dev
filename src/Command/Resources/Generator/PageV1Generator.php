<?php
namespace Pw\Command\Resources\Generator;

use Pw\Command\Resources\help\PwStr;
use Pw\Command\Resources\help\PwGenerator;
use Pw\Command\Resources\help\PwFileManager;

class PageV1Generator {

    public static function generate( $data, $io) {   
        $type = $data['type' ] ? $data['type'] : 'page';
        $request = $data['request' ] ? $data['request'] : 'POST';
        $className = $data['className'] ? $data['className'] : null;       
        $route_url = $data['route_url' ] ? $data['route_url'] : null;
        $twig_path = $data['twig_path' ] ? $data['twig_path'] : null;
        $route_name = $data['route_name' ] ? $data['route_name'] : null;
        $methodName = $data['methodName' ] ? $data['methodName'] : 'index';

        $controllerClassNameDetails =   PwGenerator::createClassNameDetails(
                                            $className,
                                            'Controller',
                                            'Controller'
                                        );
        $dataMethods = [];
        $webpackName = strtolower(PwStr::asRouteName($className));
        $methodName = PwStr::formatFunction($methodName);
        $name = ucfirst($controllerClassNameDetails->getShortName());
        $controllerClassName = $controllerClassNameDetails->getFullName();
        $namespace = PwStr::getNamespace($controllerClassName, $type);
        $directory = PwStr::getPath($namespace);
        $pathFile = PwStr::getPathFilename($directory, $name);

        //Get the modele to create the file of the controller
        $model = __DIR__.'/../models/php/class_controller.php.pw';
        $content = file_get_contents($model);
        $isNewFile = PwFileManager::createFile($pathFile, $content, [
            "name" => ucfirst($name),
            "namespace" => $namespace,
        ]);

        //Show controller file path create
        PwFileManager::dumpFile(PwStr::getNamespaceByExt($pathFile), $isNewFile, "controller", $io, true);
        
        $currentMethods = self::getAllMethods($pathFile);

        if (\in_array($methodName, $currentMethods)) {
            throw new \InvalidArgumentException(sprintf('The "%s" method already exists.', $methodName));
        }

        //Entry point name
        $entryPointName = $webpackName."_".PwStr::asRouteName($methodName);

        //Route name
        $className = PwStr::asRouteName($className);
        $route_name = $route_name 
                        ? $route_name : 
                        $type."_".$className."_".PwStr::asRouteName($methodName);

        //Url name
        $route_url = $route_url 
                        ? $route_url
                        : PwStr::asRoutePath($route_name);

        //Twig file
        $twig_path = $twig_path
                        ? $twig_path
                        : PwStr::asRoutePath($route_name).".twig.html";

        //Request type
        $request = $request ? $request : 'POST, GET';
        $request = self::formatRequest($request);
        
        //Get the modele to generate the method $methodName
        $model = __DIR__.'/../models/php/method_page.php.pw';
        $content = file_get_contents($model);
        PwFileManager::createMethod($pathFile, $content, [
                                        'controller' => $name, 
                                        'name' => $methodName,
                                        'route_url' => $route_url,  
                                        'route_name' => $route_name, 
                                        'route_methods' => $request,
                                        'twig' => ltrim($twig_path, "/"),
                                    ]);

        //Get the modele to generate the file of twig layout
        $model = __DIR__.'/../models/twig/layout.twig.pw';
        $content = file_get_contents($model);
        PwFileManager::createLayout($content);

        //Get the modele to generate the file of the twig page
        $model = __DIR__.'/../models/twig/page.twig.pw';
        $content = file_get_contents($model);
        $twig_path = str_replace('/', '\\', $twig_path);
        $twigDirectory = PwStr::getPath($twig_path, 'templates');
        PwFileManager:: createFile($twigDirectory, $content, [
                            "title" => $name,
                            "description" => $name,
                            "entrypoint" => $entryPointName,
                            "layout" => 'layout.twig.html',
                            "webpack_config" => $webpackName,
                        ]);


        /*** Webpack ***/
        //Get the modele to generate the file of the webpack config
        $model = __DIR__.'\..\models\js\webpack_config.js.pw';
        $content = file_get_contents($model);
        $pathWebpack = PwFileManager::getPathWebpack($webpackName);
        PwFileManager::createWebpack($pathWebpack, $content, ["name" => $webpackName]);

        if($isNewFile) {
            /**** Insert to encore ****/
            //Get the modele to generate the file of the webpack config
            $model = __DIR__.'/../models/yaml/webpack_encore.yaml.pw';
            $content = file_get_contents($model);
            $encorePath = PwFileManager::createWebpackConfig($content);

            //Get the modele to generate the file of the webpack build
            $model = __DIR__.'/../models/yaml/webpack_builds.yaml.pw';
            $content = file_get_contents($model);
            PwFileManager::createWebpackBuild($encorePath, $content, ["name" => $webpackName]);

            //Get the modele to generate webpack package
            $model = __DIR__.'/../models/yaml/webpack_packages.yaml.pw';
            $content = file_get_contents($model);
            PwFileManager::createWebpackPackage($encorePath, $content, ["name" => $webpackName]);

            /**** Insert to webpack ****/
            //Get the modele to generate the file of the webpack
            $model = __DIR__.'\..\models\js\webpack_webpack.js.pw';
            $content = file_get_contents($model);
            $webpackPath = PwFileManager::createWebpackWebpack($content, ["name" => $webpackName]);

            //Get the modele to generate the file of the webpack require
            $model = __DIR__.'\..\models\js\webpack_require.js.pw';
            $content = file_get_contents($model);
            PwFileManager::createWebpackRequire($webpackPath, $content, ["name" => $webpackName]);

            //Get the modele to generate the file of the webpack module
            $model = __DIR__.'\..\models\js\webpack_module.js.pw';
            $content = file_get_contents($model);
            PwFileManager::createWebpackModule($webpackPath, $content, ["name" => $webpackName]);
        }

        /**** Generate asset folder ***/
        //Get the modele to generate the file index in assets modules directiory
        $model = __DIR__.'\..\models\js\assets_index.js.pw';
        $content = file_get_contents($model);
        $pathIndex = PwFileManager::createAssetsIndex($content, $className, $methodName);

        //Get the modele to generate the file main in assets modules directiory
        $model = __DIR__.'\..\models\js\assets_main.js.pw';
        $content = file_get_contents($model);
        $pathMain = PwFileManager::createAssetsMain($content, $className, $methodName);

        //Get the modele to generate the file config in assets modules directiory
        $model = __DIR__.'\..\models\js\assets_config.js.pw';
        $content = file_get_contents($model);
        $pathConfig = PwFileManager::createAssetsConfig($content, $className, $methodName);

        //Get the modele to generate the file jsx in assets components directiory
        $model = __DIR__.'\..\models\jsx\assets_component.jsx.pw';
        $content = file_get_contents($model);
        $pathJsx = PwFileManager::createAssetsJsx($content, $className, $methodName);

        //Get the modele to generate the file scss in assets components directiory
        $model = __DIR__.'\..\models\scss\assets_component.scss.pw';
        $content = file_get_contents($model);
        $pathScss = PwFileManager::createAssetsScss($content, $className, $methodName);

        //Get the modele to add the Entrypoint in the webpack file
        $model = __DIR__.'\..\models\js\assets_entrypoint.js.pw';
        $content = file_get_contents($model);
        PwFileManager::addEntrypoint($pathWebpack, $content , [
            "name" => $entryPointName,
            "path" => "$pathIndex"
        ]);

        PwFileManager::dumpOtherFile($twig_path, "twig", $io);
        
    }

    /**
     * 
     * Return the list of methods in the controller by file path
     * 
     * @param string $filePath
     * @return array
    */
    public static function getAllMethods(string $filePath) : array {

        // Get the contents of the controller file
        $fileContents = file_get_contents($filePath);

        // Search for function definitions
        preg_match_all('/function\s+(\w+)\s*\(/', $fileContents, $matches);

        // Return the function names
        return $matches[1];
        
    }

    /**
     * 
     * @param string $request
     * @return string
    */
    public static function formatRequest(string $request): string
    {
        $route_methods = "";
        $requests = explode(",", $request);
        foreach ($requests as $request) {
            $request = strtoupper(trim($request));
            $route_methods .= "'$request', ";
        }
        $route_methods = substr($route_methods, 0, strlen($route_methods) - 1);
        $route_methods = substr($route_methods, 0, strlen($route_methods) - 1);

        return $route_methods;
    } 
}
