<?php
  try {
    #Pide las variables para conectarse a la base de datos.
    require('data.php'); 
    # Se crea la instancia de PDO
    $db = new PDO("pgsql:dbname=$databaseName;host=localhost;port=5432;user=$user;password=$password");
    $db_profes = new PDO("pgsql:host=localhost;port=5432;dbname=e3profesores;user=$user;password=grupo28");

  } catch (Exception $e) {
    echo "No se pudo conectar a la base de datos: $e";
  }

?>