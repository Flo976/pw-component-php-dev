<?php

namespace Pw\Command\Resources\help;

use Symfony\Component\DependencyInjection\Container;

class PwStr
{
    const PSR4 = "App";

    /**
     * Looks for suffixes in strings in a case-insensitive way.
     */
    public static function hasSuffix(string $value, string $suffix): bool
    {
        return 0 === strcasecmp($suffix, substr($value, -\strlen($suffix)));
    }

    /**
     * Ensures that the given string ends with the given suffix. If the string
     * already contains the suffix, it's not added twice. It's case-insensitive
     * (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'FooCommand').
     */
    public static function addSuffix(string $value, string $suffix): string
    {
        return self::removeSuffix($value, $suffix).$suffix;
    }

    /**
     * Ensures that the given string doesn't end with the given suffix. If the
     * string contains the suffix multiple times, only the last one is removed.
     * It's case-insensitive (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'Foo'.
     */
    public static function removeSuffix(string $value, string $suffix): string
    {
        return self::hasSuffix($value, $suffix) ? substr($value, 0, -\strlen($suffix)) : $value;
    }

    /**
     * Transforms the given string into the format commonly used by PHP classes,
     * (e.g. `app:do_this-and_that` -> `AppDoThisAndThat`) but it doesn't check
     * the validity of the class name.
     */
    public static function asClassName(string $value, string $suffix = ''): string
    {
        $value = trim($value);
        $value = str_replace(['-', '_', '.', ':'], ' ', $value);
        $value = ucwords($value);
        $value = str_replace(' ', '', $value);
        $value = ucfirst($value);
        $value = self::addSuffix($value, $suffix);

        return $value;
    }

    /**
     * Transforms the given string into the format commonly used by Twig variables
     * (e.g. `BlogPostType` -> `blog_post_type`).
     */
    public static function asTwigVariable(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[^a-zA-Z0-9_]/', '_', $value);
        $value = preg_replace('/(?<=\\w)([A-Z])/', '_$1', $value);
        $value = preg_replace('/_{2,}/', '_', $value);
        $value = strtolower($value);

        return $value;
    }

    public static function asLowerCamelCase(string $str): string
    {
        return lcfirst(self::asCamelCase($str));
    }

    public static function asCamelCase(string $str): string
    {
        return strtr(ucwords(strtr($str, ['_' => ' ', '.' => ' ', '\\' => ' '])), [' ' => '']);
    }

    public static function asRoutePath(string $value): string
    {
        return '/'.str_replace('_', '/', self::asTwigVariable($value));
    }

    public static function asRouteName(string $value): string
    {
        return self::asTwigVariable($value);
    }

    public static function asSnakeCase(string $value): string
    {
        return self::asTwigVariable($value);
    }

    public static function asCommand(string $value): string
    {
        return str_replace('_', '-', self::asTwigVariable($value));
    }

    public static function asEventMethod(string $eventName): string
    {
        return sprintf('on%s', self::asClassName($eventName));
    }

    public static function getShortClassName(string $fullClassName): string
    {
        if (empty(self::getNamespace($fullClassName))) {
            return $fullClassName;
        }

        return substr($fullClassName, strrpos($fullClassName, '\\') + 1);
    }

    public static function getNamespace(string $fullClassName): string
    {
        return "App$fullClassName";
    }
    public static function getName(string $fullClassName): string
    {
        $key = "\\";
        $array = explode($key, $fullClassName);
        return end($array);
    }

    public static function getPath($fullClassName, $folder='src'): string
    {
        $key = "\\";
        $pathToCreate='';
        $project_dir = getcwd();
        $fullClassName = '\\' . ucfirst(ltrim($fullClassName, '\\'));
        $path = "$project_dir\\$folder$fullClassName";
        $array = explode($key, $path);
        for ($i=0; $i < count($array)-1 ; $i++) { 
            $pathToCreate = $pathToCreate."/".$array[$i];
        }
        $pathToCreate = ltrim($pathToCreate, '/');

        if(!file_exists($pathToCreate)){
            mkdir($pathToCreate, 0777, true);
        }
        
        return $path;
    }

    public static function getPathFilename($fullClassName, $extension=false){
        if(!is_int(strpos($fullClassName, ".php"))){
            $fullClassName = "$fullClassName.php";
        }

        return $fullClassName;
    }

    public static function asFilePath(string $value): string
    {
        $value = Container::underscore(trim($value));
        $value = str_replace('\\', '/', $value);

        return $value;
    }

    public static function singularCamelCaseToPluralCamelCase(string $camelCase): string
    {
        $snake = self::asSnakeCase($camelCase);
        $words = explode('_', $snake);
        $words[\count($words) - 1] = self::pluralize($words[\count($words) - 1]);
        $reSnaked = implode('_', $words);

        return self::asLowerCamelCase($reSnaked);
    }

    public static function pluralCamelCaseToSingular(string $camelCase): string
    {
        $snake = self::asSnakeCase($camelCase);
        $words = explode('_', $snake);
        $words[\count($words) - 1] = self::singularize($words[\count($words) - 1]);
        $reSnaked = implode('_', $words);

        return self::asLowerCamelCase($reSnaked);
    }

    public static function getRandomType(): string
    {
        $types = [
            'api',
            'page',
            'service',
        ];

        return sprintf('%s', $types[array_rand($types)]);
    }
    public static function getRandomTerm(): string
    {
        $adjectives = [
            'fossa',
            'vanga',
            'ankoay',
            'ayeaye',
            'baobab',
            'palmier',
            'ravinala',
            'lemurien',
            'cameleon',
            'cossypha',
        ];

        return sprintf('%s', $adjectives[array_rand($adjectives)]);
    }

    /**
     * Checks if the given name is a valid PHP variable name.
     *
     * @see http://php.net/manual/en/language.variables.basics.php
     *
     * @param $name string
     *
     * @return bool
     */
    public static function isValidPhpVariableName($name)
    {
        return (bool) preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name, $matches);
    }

    public static function areClassesAlphabetical(string $class1, string $class2)
    {
        $arr1 = [$class1, $class2];
        $arr2 = [$class1, $class2];
        sort($arr2);

        return $arr1[0] == $arr2[0];
    }

    public static function asHumanWords(string $variableName): string
    {
        return str_replace('  ', ' ', ucfirst(trim(implode(' ', preg_split('/(?=[A-Z])/', $variableName)))));
    }
    
}
