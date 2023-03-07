<?php
namespace Pw\Command\Resources\Generator;

use Pw\Command\Resources\help\PwStr;
use Pw\Command\Resources\help\PwGenerator;
use Pw\Command\Resources\help\PwFileManager;

class ServiceV1Generator {

    public static function generate($className, $methods, $io)
    {   
        $controllerClassNameDetails =   PwGenerator::createClassNameDetails(
                                            $className,
                                            'Service',
                                            'Service'
                                        );
        $dataMethods = [];
        $name = ucfirst($controllerClassNameDetails->getShortName());
        $controllerClassName = $controllerClassNameDetails->getFullName();
        $namespace = PwStr::getNamespace($controllerClassName, null);
        $namespace = rtrim($namespace, "\\");
        $directory = PwStr::getPath($namespace);
        $pathFile = PwStr::getPathFilename($directory, $name);
        $pathFile = str_replace("\\\\", "\\", $pathFile);

        //Get the modele to create the file of the controller
        $model = __DIR__.'/../models/php/class_service.php.pw';
        $content = file_get_contents($model);
        $isNewFile = PwFileManager::createFile($pathFile, $content, [
            "name" => ucfirst($name),
            "namespace" => $namespace,
        ]);

        //Show controller file path create
        PwFileManager::dumpFile(PwStr::getNamespaceByExt($pathFile), $isNewFile, "service", $io, true);
        
       
        $currentMethods = self::getAllMethods($pathFile);

        if(is_string($methods)) {
            $dataMethods = [];
            $methods = explode(",", $methods);
            foreach ($methods as $method) {
                $method = PwStr::formatFunction($method);
                if (\in_array($method, $currentMethods)) {} else {
                    //Get the modele to generate the method $methodName
                    $model = __DIR__.'/../models/php/method_service.php.pw';
                    $content = file_get_contents($model);
                    PwFileManager::createMethod($pathFile, $content, [
                                        'name' => $method
                                    ]);
                    $dataMethods[] = $method;
                }
            }
            foreach ($dataMethods as $data) {
                //Show twig file path create
                PwFileManager::dumpOtherFile($data, "methode", $io);
            }

        } 
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
}
