<?php
    $dbhost = process.env.MYSQLHOST ||'localhost';
    $dbuser = process.env.MYSQLUSER ||'root';
    $dbpass = process.env.MYSQLPASSWORD ||'';
    $dbname = process.env.MYSQL_DATABASE ||'bruna';

    $db = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    $db->set_charset("utf8");

    if($db->connect_errno > 0){
        die('No se pudo conectar a la base de datos [' . $db->connect_error . ']');
    }
?>