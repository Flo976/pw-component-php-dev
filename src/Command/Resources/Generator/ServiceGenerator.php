<?php
namespace Pw\Command\Resources\Generator;

use Pw\Command\Resources\help\PwStr;
use Pw\Command\Resources\help\PwGenerator;
use Pw\Command\Resources\help\PwValidator;
use Pw\Command\Resources\help\PwFileManager;
use Symfony\Component\Console\Question\Question;

class ServiceGenerator {

    public static function generate($className, $io)
    {   
        $controllerClassNameDetails =   PwGenerator::createClassNameDetails(
                                            $className,
                                            'Service',
                                            'Service'
                                        );
        $dataMethods = [];
        $controllerClassName = $controllerClassNameDetails->getFullName();
        $namespace = PwStr::getNamespace($controllerClassName);
        $directory = PwStr::getPath($controllerClassName);
        $pathFile = PwStr::getPathFilename($directory);
        $name = PwStr::getName($controllerClassName);

        //Get the modele to create the file of the controller
        $model = __DIR__.'/../models/php/class_service.php.pw';
        $content = file_get_contents($model);
        $isNewFile = PwFileManager::createFile($pathFile, $content, [
            "name" => ucfirst($name),
            "namespace" => $namespace,
        ]);

        //Show controller file path create
        PwFileManager::dumpFile(PwStr::getNamespaceByExt($pathFile), $isNewFile, "service", $io);
        
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
            PwFileManager::dumpOtherFile($data, "methode", $io);
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
        
        //Get the modele to generate the method $methodName
        $model = __DIR__.'/../models/php/method_service.php.pw';
        $content = file_get_contents($model);
        PwFileManager::createMethod($pathFile, $content, [
                                        'name' => $methodName
                                    ]);

        return $methodName;
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
}
