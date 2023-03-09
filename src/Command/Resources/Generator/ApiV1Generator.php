<?php
namespace Pw\Command\Resources\Generator;

use Pw\Command\Resources\help\PwStr;
use Pw\Command\Resources\help\PwGenerator;
use Pw\Command\Resources\help\PwFileManager;

class ApiV1Generator {

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

        //Route name
        $className = PwStr::asRouteName($className);
        $route_name = $route_name 
                        ? $route_name : 
                        $type."_".$className."_".PwStr::asRouteName($methodName);

        //Url name
        $route_url = $route_url 
                        ? $route_url
                        : PwStr::asRoutePath($route_name);
        //Request type
        $request = $request ? $request : 'POST, GET';
        $request = self::formatRequest($request);
        
        //Get the modele to generate the method $methodName
        $model = __DIR__.'/../models/php/method_api.php.pw';
        $content = file_get_contents($model);
        PwFileManager::createMethod($pathFile, $content, [
                                        'controller' => $name, 
                                        'name' => $methodName,
                                        'route_url' => $route_url,  
                                        'route_name' => $route_name, 
                                        'route_methods' => $request,
                                        'twig' => ltrim($twig_path, "/"),
                                    ]);
        PwFileManager::dumpOtherFile($methodName, "methode", $io);
        
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
