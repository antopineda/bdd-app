<?php
include('../config/conexion.php');

function corregir_run($run) {
    $run = trim($run);
    if (empty($run) || strlen($run) < 7 || strlen($run) > 9 || !is_numeric($run)) {
        return NULL;
    }
    return $run;
}

function personas_from_estudiante($array) {
    $array_limpio = [];

    foreach ($array as $linea) {
        $run = corregir_run($linea[6]);
        $nombre = $linea[8];
        $apellido = $linea[10];
        $email = NULL;
        $telefono = NULL;
        $estamento = 'estudiante';

        $array_limpio[] = [
            $run, $nombre, $apellido,
            $email, $telefono, $estamento
        ];
    }

    return $array_limpio;
}

function personas_from_profesor($db_profes) {
    // Consulta para obtener datos de profesores
    $query = "SELECT run, nombre, apellido1, email_institucional, telefono FROM profesores";
    
    // Ejecutar la consulta
    $stmt = $db_profes->query($query);
    
    $array_limpio = [];
    
    while ($linea = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Aplicar la función corregir_run para el campo 'run'
        $run = corregir_run($linea['run']);
        $nombre = $linea['nombre'];
        $apellido = $linea['apellido1'];
        $email = $linea['email_institucional'];
        $telefono = $linea['telefono'];
        $estamento = 'profesor';

        $array_limpio[] = [
            $run, $nombre, $apellido, $email, $telefono, $estamento
        ];
    }

    return $array_limpio;
}


?>