<?php

namespace Pw\Command\Resources\help;
use Symfony\Component\Console\Exception\ExceptionInterface;

class PwFileManager
{

    public static function setIO(SymfonyStyle $io): void
    {
        $this->io = $io;
    }

    public static function parseTemplate(string $templatePath, array $parameters): string
    {
        ob_start();
        extract($parameters, \EXTR_SKIP);
        include $templatePath;

        return ob_get_clean();
    }

    public static function dumpFile(string $filename, bool  $isNewFile, string $type, $io): void
    {
        $comment = $isNewFile ? '<fg=blue>'.$type.' generated! Now let\'s add some methods!</>' : '<fg=yellow>your '.$type.' already exists! So let\'s add some new methods!</>';
        
        if ($io) {
            $io->comment(sprintf(
                '%s: %s',
                $filename,
                $comment
            ));
        }
    }

     public static function dumpOtherFile(string $filename, $type ,$io): void
    {
        $comment = "<fg=blue>$type generated!</>";
        if ($io) {
            $io->comment(sprintf(
                '%s: %s',
                $filename,
                $comment
            ));
        }
    }

    //create a file if not exist and return a boolen if newfile or no
    public static function createFile($path, $content='', $params=[]){
        $isNewFile = false;
        if (!file_exists($path)) {
            // create the file
            $content = self::applyParams($content , $params);
            file_put_contents($path, $content);
            $isNewFile = true;
        }

        return $isNewFile;
    }

    public static function createLayout($content=''){
        $project_dir = getcwd();
        $templates = '\templates';
        $layout = '\layout.twig.html';
        $pathToCreate = "$project_dir$templates";
        
        if(!file_exists($pathToCreate)){
            mkdir($pathToCreate, 0777, true);
        }

        $path = "$pathToCreate$layout";

        if (!file_exists($path)) {
            // create the file
            $content = self::applyParams($content , []);
            file_put_contents($path, $content);
        }
    }

    public static function getPathWebpack($name){
        $project_dir = getcwd();
        $pathToCreate = "$project_dir/webpack";
        if(!file_exists($pathToCreate)){
            mkdir($pathToCreate, 0777, true);
        }
        $path = "$pathToCreate/$name"."_config.js";

        return $path;
    }


    public static function createWebpack($path, $content='', $params=[]){
        if (!file_exists($path)) {
            // create the file
            $content = self::applyParams($content , $params);
            file_put_contents($path, $content);
        }
    }

    public static function createWebpackConfig($content=''){
        $project_dir = getcwd();
        $pathToCreate = "$project_dir/config/packages";
        if(!file_exists($pathToCreate)){
            mkdir($pathToCreate, 0777, true);
        }
        $path = "$pathToCreate/webpack_encore.yaml";

        if (!file_exists($path)) {
            // create the file
            file_put_contents($path, $content);
        }

        return $path;
    }

    public static function createWebpackWebpack($content='', $params=[]){

        $project_dir = getcwd();
        $webpackConfig = '\webpack.config.js';

        $path = "$project_dir$webpackConfig";

        if (!file_exists($path)) {
            // create the file
            $content = self::applyParams($content , $params);
            file_put_contents($path, $content);
        }

        return $path;
    }

    public static function createWebpackBuild($encorePath, $content='', $params=[]){
        $content = self::applyParams($content , $params);
        self::insert($encorePath, $content, "builds");
    }

    public static function createWebpackPackage($encorePath, $content='', $params=[]){
        $content = self::applyParams($content , $params);
        self::insert($encorePath, $content, "packages");
    }
    
    public static function createWebpackRequire($webpackPath, $content='', $params=[]){
        $content = self::applyParams($content , $params);
        self::insert($webpackPath, $content, "require");
    }

    public static function createWebpackModule($webpackPath, $content='', $params=[]){
        $content = self::applyParams($content , $params);
        self::insert($webpackPath, $content, "module");
    }

    public static function addEntrypoint($pathIndex, $content='', $params= []){
        $content = self::applyParams($content , $params);
        self::insert($pathIndex, $content, "entrypoint");
    }

    public static function getDirComponents()
    {
        $project_dir = getcwd();
        return "$project_dir/assets/vue/components/modules";
    }

    public static function getDirModules()
    {
        return "/assets/modules";
    }

    public static function createAssetsIndex($content, $className, $methodName){
        $project_dir = getcwd();
        $className = strtolower($className);
        $methodName = strtolower($methodName);
        $project_modules = self::getDirModules();
        $pathToCreate = "$project_dir$project_modules/$className/$methodName";
        if(!file_exists($pathToCreate)){
            mkdir($pathToCreate, 0777, true);
        }
        $path = "$pathToCreate/$methodName"."_index.js";
        if (!file_exists($path)) {
            // create the file
            $params = [
                "main_filename" =>$methodName."_main.js"
            ]; 
            $content = self::applyParams($content , $params);
            file_put_contents($path, $content);
        }
        
        return ".$project_modules/$className/$methodName/$methodName"."_index.js";;
    }

    public static function createAssetsMain($content, $className, $methodName){
        $project_dir = getcwd();
        $className = strtolower($className);
        $methodName = strtolower($methodName);
        $project_modules = self::getDirModules();
        $pathToCreate = "$project_dir$project_modules/$className/$methodName";
        if(!file_exists($pathToCreate)){
            mkdir($pathToCreate, 0777, true);
        }
        $path = "$pathToCreate/$methodName"."_main.js";
        if (!file_exists($path)) {
            // create the file
            $componentName = ucfirst($methodName);
            $project_components = 'vue/components/modules';
            $componentPath = "$project_components/$className/$componentName/$componentName".".jsx";
            $configFilename = "$methodName"."_config.js";
            $params = [
                "component_name" =>$componentName,
                "component_path" =>$componentPath,
                "config_filename" =>$configFilename,
            ]; 
            $content = self::applyParams($content , $params);
            file_put_contents($path, $content);
        }
        
        return $path;
    }

    public static function createAssetsConfig($content, $className, $methodName){
        $project_dir = getcwd();
        $className = strtolower($className);
        $methodName = strtolower($methodName);
        $project_modules = self::getDirModules();
        $pathToCreate = "$project_dir$project_modules/$className/$methodName";
        if(!file_exists($pathToCreate)){
            mkdir($pathToCreate, 0777, true);
        }
        $path = "$pathToCreate/$methodName"."_config.js";
        if (!file_exists($path)) {
            file_put_contents($path, $content);
        }
        
        return $path;
    }

    public static function createAssetsJsx($content, $className, $methodName){
        $className = strtolower($className);
        $methodName = strtolower($methodName);
        $project_dir = self::getDirComponents();
        $componentName = ucfirst($methodName);
        $pathToCreate = "$project_dir/$className/$componentName";
        if(!file_exists($pathToCreate)){
            mkdir($pathToCreate, 0777, true);
        }
        $path = "$pathToCreate/$componentName".".jsx";
        if (!file_exists($path)) {
            // create the file
            $configPath = "modules/$className/$methodName/$methodName"."_config.js";
            $params = [
                "config_path" =>$configPath,
                "name" =>$componentName,
            ]; 
            $content = self::applyParams($content , $params);
            file_put_contents($path, $content);
        }
        
        return $path;
    }

    public static function createAssetsScss($content, $className, $methodName){
        $className = strtolower($className);
        $methodName = strtolower($methodName);
        $project_dir = self::getDirComponents();
        $componentName = ucfirst($methodName);
        $pathToCreate = "$project_dir/$className/$componentName";
        if(!file_exists($pathToCreate)){
            mkdir($pathToCreate, 0777, true);
        }
        $path = "$pathToCreate/$componentName".".scss";
        if (!file_exists($path)) {
            // create the file
            file_put_contents($path, $content);
        }
        
        return $path;
    }


    public static function createMethod($path, $content='', $params=[]){
        if (file_exists($path)) {
            // create the file
            $currentContent = file_get_contents($path);
            $lastBracePos = strrpos($currentContent, '}');
            $content = self::applyParams($content , $params);
            $content = substr_replace($currentContent, "$content\n", $lastBracePos, 0);
            file_put_contents($path, $content);
        }
        return $params;
    }

    public static function applyParams($content, $params){
        if(!$content || !is_array($params)){
            return $content;
        }
      
        foreach($params as $key => $value){
            $value = $value ? $value : "";
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }
        

        while(is_int($left = strrpos($content, '{{'))){
            $len = strlen($content);

            $right = strrpos($content, '}}');
            $right = ($right > 0) ? $right+2 : $left+2;

            $safe = substr($content, 0, $left);
            $safe = $safe.substr($content, $right, $len - $right);

            $content = $safe;
        }

        $content = str_replace('[[:brace]]', '{{', $content);
        $content = str_replace('[[brace:]]', '}}', $content);
       
        return $content;
    }

    public static function insert($path, $content, $type){
        if(!is_file($path)){
            throw new \InvalidArgumentException(sprintf('"%s" file not found.', $path));
        }

        $lines = file($path);
        $length = count($lines);

        for($i=$length-1; 0<=$i; $i--){
            $line_of_interest = self::getLineOfInterest(
                $lines[$i], 
                $i, 
                $type
            );

            if(is_int($line_of_interest)){
                array_splice($lines, $line_of_interest, 0, [$content]);
                break;
            }
        }

        file_put_contents($path, join("", $lines));

        return true;
    }

    public static function getLineOfInterest($line, $index, $type){
        if(($type == "php") && is_int(strpos($line, "}"))){
            return $index;
        }
        else if(($type == "entrypoint") && is_int(strpos($line, "addEntry"))){
            return $index + 1;
        }
        else if(($type == "require") && is_int(strpos($line, "require"))){
            return $index + 1;
        }
        else if(($type == "module") && is_int(strpos($line, "]"))){
            return $index;
        }
        else if(is_int(strpos($line, $type))){
            return $index + 1;
        }

        return null;
    }
    
}
