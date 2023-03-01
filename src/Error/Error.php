<?php

namespace Pw\Error;

class Error
{
    public function createErrorFiles($params){
        $result = "Error pages for the application have been successfully generated.";
        $dirName = 'EventListener'; 
        
        if(
            !isset($params["src_dir"]) ||
            !isset($params["template_dir"])
        ){
            $result = "Missing parameters to generate error pages.";
        }

        $srcDir = $params["src_dir"];
        $templateDir = $params["template_dir"];

        if (!is_dir($srcDir . '/'.$dirName)) {
            mkdir($srcDir . '/' . $dirName, 777);
        }

        $filePath = $srcDir . '/'.$dirName. '/'."NotFoundHttpExceptionListener.php";
        if (
            is_dir($srcDir . '/'.$dirName) && 
            !file_exists($filePath)
        ) {
            
            $lines = file("tools/EventListener/NotFoundHttpExceptionListener.txt", true);

            file_put_contents($srcDir . '/'.$dirName. '/NotFoundHttpExceptionListener.php', implode('', $lines));
          
        }

        $dirName = "error";
        if (!is_dir($templateDir . '/'.$dirName)) {
            mkdir($templateDir . '/' . $dirName, 777);
        }

        $filePath = $templateDir . '/'.$dirName. '/'."error.html.twig";
        if (
            is_dir($templateDir . '/'.$dirName) && 
            !file_exists($filePath)
        ) {
            $lines = file("tools/error/error.txt", true);
            file_put_contents($templateDir . '/'.$dirName. '/error.html.twig', implode('', $lines));
        }
        
        $filePath = $templateDir . '/'.$dirName. '/'."error404.html.twig";
        if (
            is_dir($templateDir . '/'.$dirName) && 
            !file_exists($filePath)
        ) {
            $lines = file("tools/error/error404.txt", true);
            file_put_contents($templateDir . '/'.$dirName. '/error404.html.twig', implode('', $lines));
        }

        return $result;
    }
}