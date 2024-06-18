<?php

namespace Vendor\Schoolarsystem;

class loadEnv{

    public static function cargar(){
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }

}

?>