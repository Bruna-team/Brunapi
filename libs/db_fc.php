<?php
    $dbhost = process.env.dbhost ||'localhost';
    $dbuser = process.env.dbuser ||'root';
    $dbpass = process.env.dbpass ||'';
    $dbname = process.env.dbname ||'bruna';

    $db = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    $db->set_charset("utf8");

    if($db->connect_errno > 0){
        die('No se pudo conectar a la base de datos [' . $db->connect_error . ']');
    }
?>