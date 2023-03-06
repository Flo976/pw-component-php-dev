<?php

namespace Pw\Command\Resources\help;

use Pw\Command\Resources\help\PwStr;
use Pw\Command\Resources\help\PwClassNameDetails;


class PwGenerator
{
    
    private $namespacePrefix;

    /**
     *
     * Examples:
     *      // App\Service\PwService
     *      $gen->createClassNameDetails('pw', 'Service', 'Service');
     * 
     *       // App\Service\Admin\FooService
     *      $gen->createClassNameDetails('Pw\\Admin', 'Service', 'Service');
     * 
     *      // App\Controller\PwController
     *      $gen->createClassNameDetails('pw', 'Controller', 'Controller');
     *
     *      // App\Controller\Admin\PwController
     *      $gen->createClassNameDetails('Pw\\Admin', 'Controller', 'Controller');
     *
     *
     * @param string $name            The short "name" that will be turned into the class name
     * @param string $namespacePrefix Recommended namespace where this class should live, but *without* the "App\\" part
     * @param string $suffix          Optional suffix to guarantee is on the end of the class
     */
    public function createClassNameDetails(string $name, string $namespacePrefix, string $suffix = '', string $validationErrorMessage = ''): PwClassNameDetails
    {
        
        $fullNamespacePrefix = $this->namespacePrefix.'\\'.$namespacePrefix;
        if ('\\' === $name[0]) {
            // class is already "absolute" - leave it alone (but strip opening \)
            $className = substr($name, 1);
        } else {
            $className = rtrim($fullNamespacePrefix, '\\').'\\'.PwStr::asClassName($name, $suffix);
        }

        PwValidator::validateClassName($className, $validationErrorMessage);

        return new PwClassNameDetails($className, $fullNamespacePrefix, $suffix);
    }

    public function generateController(string $controllerClassName, string $controllerTemplatePath, array $parameters = []): string
    {
        return $this->generateClass(
            $controllerClassName,
            $controllerTemplatePath,
            $parameters +
            [
                'generator' => $this->templateComponentGenerator,
                'parent_class_name' => static::getControllerBaseClass()->getShortName(),
            ]
        );
    }
}
