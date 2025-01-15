<?php

spl_autoload_register(function($classname){
    $path = strtolower($classname).".php";
    
    if(file_exists($path)){
        require_once $path;
    }
    else{
        echo "File $path is not found";
    }
});