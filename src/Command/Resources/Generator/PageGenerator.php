<?php
namespace Pw\Command\Resources\Generator;

use Pw\Command\Resources\help\PwStr;
use Pw\Command\Resources\help\PwGenerator;
use Pw\Command\Resources\help\PwValidator;
use Pw\Command\Resources\help\PwFileManager;
use Symfony\Component\Console\Question\Question;

class PageGenerator {

    public static function generate($className, $type, $io)
    {   
        $controllerClassNameDetails =   PwGenerator::createClassNameDetails(
                                            $className,
                                            'Controller',
                                            'Controller'
                                        );
        $dataMethods = [];
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
        PwFileManager::dumpFile(PwStr::getNamespaceByExt($pathFile), $isNewFile, "controller", $io);
        
        $isAddMethod = true;
        $currentMethods = self::getAllMethods($pathFile);

        //New methods, do the treatment askForNextMethod
        while ($isAddMethod) { 
            $data = self::askForNextMethod($io, $currentMethods, $pathFile, $name, $isNewFile, $className);
            if(!$data) {
                $isAddMethod = false;
            } else {
                $dataMethods[] = $data;
            }
            $isNewFile = false;
        }

        foreach ($dataMethods as $data) {
            //Show twig file path create
            PwFileManager::dumpOtherFile($data, "twig", $io);
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

    /**
     * 
     * This function is used to request information about the method to be added,
     * such as the method name, URI, name of the twig, request type, etc
     * 
     * @param objet $io
     * @param array $methods
     * @param string $pathFile
     * @param string $controllerName
     * @param string $isFirstMethod
     * @param string $className
     * @return string
    */
    public static function askForNextMethod( 
        $io, 
        array $methods, 
        string $pathFile, 
        string $controllerName, 
        bool $isFirstMethod, 
        string $className
    ){

        $io->writeln('');

        if ($isFirstMethod) {
            $questionText = 'New method name e.g. <fg=yellow>index</> (press <return> to stop adding methods)';
        } else {
            $questionText = 'Add another method? Enter the methode name e.g. <fg=yellow>index</> (or press <return> to stop adding methods)';
        }

        $methodName = $io->ask($questionText, null, function ($name) use ($methods) {
            // allow it to be empty
            if (!$name) {
                return $name;
            }
            
            if (\in_array($name, $methods)) {
                throw new \InvalidArgumentException(sprintf('The "%s" method already exists.', $name));
            }

            return $name;
        });

        if (!$methodName) {
            return null;
        }

        $methodName = PwStr::formatFunction($methodName);
        //Question for route name
        $className = PwStr::asRouteName($className);
        $route_name = "page_".$className."_".PwStr::asRouteName($methodName);
        $questionText = "Route name of the methode <fg=blue>$methodName</>";
        $question = self::question($questionText, $route_name);
        $route_name = $io->askQuestion($question);

        //Question for url name
        $route_url = PwStr::asRoutePath($route_name);
        $questionText = "Route url of the methode <fg=blue>$methodName</>";
        $question = self::question($questionText, $route_url);
        $route_url = $io->askQuestion($question);

        //Question for twig file
        $twig_path = PwStr::asRoutePath($route_name).".twig.html";
        $questionText = "Twig path file of the methode <fg=blue>$methodName</>";
        $question = self::question($questionText, $twig_path);
        $twig_path = $io->askQuestion($question);

        //Question for request type
        $request = 'POST, GET';
        $questionText = "Request of the methode <fg=blue>$methodName</>";
        $question = self::question($questionText, $request);
        $request  =  $io->askQuestion($question);
        $request = self::formatRequest($request);
        
        //Get the modele to generate the method $methodName
        $model = __DIR__.'/../models/php/method_page.php.pw';
        $content = file_get_contents($model);
        PwFileManager::createMethod($pathFile, $content, [
                                        'name' => $methodName,
                                        'route_url' => $route_url,  
                                        'route_name' => $route_name, 
                                        'route_methods' => $request,
                                        'controller' => $controllerName, 
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
                            "title" => $controllerName,
                            "entrypoint" => $route_name,
                            "layout" => 'layout.twig.html',
                            "webpack_config" => $route_name,
                            "description" => $controllerName,
                        ]);


        /*** Webpack ***/
        //Get the modele to generate the file of the webpack config
        $model = __DIR__.'\..\models\js\webpack_config.js.pw';
        $content = file_get_contents($model);
        $pathWebpack = PwFileManager::getPathWebpack($route_name);
        PwFileManager::createWebpack($pathWebpack, $content, ["name" => $route_name]);

        /**** Insert to encore ****/
        //Get the modele to generate the file of the webpack config
        $model = __DIR__.'/../models/yaml/webpack_encore.yaml.pw';
        $content = file_get_contents($model);
        $encorePath = PwFileManager::createWebpackConfig($content);

        //Get the modele to generate the file of the webpack build
        $model = __DIR__.'/../models/yaml/webpack_builds.yaml.pw';
        $content = file_get_contents($model);
        PwFileManager::createWebpackBuild($encorePath, $content, ["name" => $route_name]);

        //Get the modele to generate webpack package
        $model = __DIR__.'/../models/yaml/webpack_packages.yaml.pw';
        $content = file_get_contents($model);
        PwFileManager::createWebpackPackage($encorePath, $content, ["name" => $route_name]);

        /**** Insert to webpack ****/
        //Get the modele to generate the file of the webpack
        $model = __DIR__.'\..\models\js\webpack_webpack.js.pw';
        $content = file_get_contents($model);
        $webpackPath = PwFileManager::createWebpackWebpack($content, ["name" => $route_name]);

        //Get the modele to generate the file of the webpack require
        $model = __DIR__.'\..\models\js\webpack_require.js.pw';
        $content = file_get_contents($model);
        PwFileManager::createWebpackRequire($webpackPath, $content, ["name" => $route_name]);

        //Get the modele to generate the file of the webpack module
        $model = __DIR__.'\..\models\js\webpack_module.js.pw';
        $content = file_get_contents($model);
        PwFileManager::createWebpackModule($webpackPath, $content, ["name" => $route_name]);

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
            "name" => $route_name,
            "path" => "$pathIndex"
        ]);
        
        return ltrim($twig_path, "/");
    }

    /**
     * 
     * $default is the default answer to the question asked
     * 
     * @param string $questionText
     * @param mixed $default
     * @return array
    */
    public static function question(
        string $questionText, mixed $default = null): Question
    {
        $question = new Question($questionText, $default);
        $question->setValidator([PwValidator::class, 'notBlank']);

        return $question;
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
