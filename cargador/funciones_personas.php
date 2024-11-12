<?php
include('../config/conexion.php');

function corregir_run($run) {
    $run = trim($run);
    if (empty($run) || strlen($rut) < 7 || strlen($rut) > 9 || !ctype_digit($run)) {
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
    // Obtener datos de db_profes
    $query = "SELECT run, nombre, apellido, email, telefono FROM profesores";
    $result = pg_query($db_profes, $query);

    $array_limpio = [];
    while ($linea = pg_fetch_assoc($result)) {
        $run = corregir_run($linea['run']);
        $nombre = $linea['nombre'];
        $apellido = $linea['apellido'];
        $email = $linea['email'];
        $telefono = $linea['telefono'];
        $estamento = 'profesor';

        $array_limpio[] = [
            $run, $nombre, $apellido, $email, $telefono, $estamento
        ];
    }

    return $array_limpio;
}

?>