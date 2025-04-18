<?php

$db_config = [
    'host' => 'localhost',    
    'user' => 'root',         
    'pass' => '',             
    'name' => 'task'          
];

    $conn = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['name']}", 
        $db_config['user'], 
        $db_config['pass']
    );
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $conn->exec("SET NAMES utf8");
    

?> 