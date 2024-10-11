<?php
require "utils.php";

// Parte 1: Abrir archivo 1
$ruta = "data/estudiantes.csv";

function abrir_archivo($ruta) {
    $archivo_datos_1 = fopen($ruta, "r"); //Abrir archivo en modo lectura
    $array_datos_1 = [];
    while (!feof($archivo_datos_1)) {
        $linea = fgets($archivo_datos_1);
        $array_datos_1[] = explode(";", $linea);
    }
    fclose($archivo_datos_1); 
    return $array_datos_1;
}

function imprimir_bonito($array_de_arrays) {
    // Obtiene el número de columnas
    $num_columnas = max(array_map('count', $array_de_arrays));

    foreach ($array_de_arrays as $mini_array) {
        // Imprime la línea superior de la tabla
        echo "+";
        for ($i = 0; $i < $num_columnas; $i++) {
            echo str_repeat("-", 12) . "+";
        }
        echo "\n";

        // Imprime cada fila
        echo "|";
        foreach ($mini_array as $elemento) {
            // Alinea el texto a la izquierda y lo ajusta a 12 caracteres
            printf("%-12s|", $elemento);
        }
        echo "\n";

        // Imprime la línea inferior de la tabla
        echo "+";
        for ($i = 0; $i < $num_columnas; $i++) {
            echo str_repeat("-", 12) . "+";
        }
        echo "\n";
    }
}
function validar_y_corregir_datos_estudiante($array_datos, $posicion_rut, $nombre_archivo_errores, $nombre_archivo_corregidos) {
    $array_validos = [];
    $array_errores = [];
    $ruts_vistos = [];
    $array_corregidos = [];

    foreach ($array_datos as $linea) {
        $es_valido = true;

        // Verificar el RUT
        if (isset($linea[$posicion_rut])) {
            $rut = trim($linea[$posicion_rut]);
            if (empty($rut) || !ctype_digit($rut) || !(strlen($rut) == 7 || strlen($rut) == 8) || in_array($rut, $ruts_vistos)) {
                $es_valido = false;
            } else {
                $ruts_vistos[] = $rut; // Guardar el RUT visto
            }
        } else {
            $es_valido = false;
        }

        // Validar Cohorte (en formato YYYY-01 o YYYY-02)
        if (isset($linea[2]) && !empty($linea[2]) && !preg_match('/^\d{4}-(01|02)$/', $linea[2])) {
            $es_valido = false;
        }

        // Validar Número de Estudiante (6 dígitos)
        if (isset($linea[3]) && !empty($linea[3]) && !preg_match('/^\d{6}$/', $linea[3])) {
            $es_valido = false;
        }

        // Validar Bloqueo (S o N)
        if (isset($linea[4]) && !empty($linea[4]) && !preg_match('/^[NS]$/', $linea[4])) {
            $es_valido = false;
        }

        // Validar DV (un solo dígito o 'K')
        if (isset($linea[7]) && !empty($linea[7]) && !preg_match('/^[0-9K]$/', $linea[7])) {
            $es_valido = false;
        }

        // Validar y corregir Nombres (solo letras y espacios, no nulo)
        if (!isset($linea[8]) || empty($linea[8]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[8])) {
            $es_valido = false;
        }
        // Validar y corregir Nombres (solo letras y espacios, no nulo)
        if (!isset($linea[9]) || empty($linea[9]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[9])) {
            $es_valido = false;
        }

        // Validar y corregir Apellido Paterno (solo letras y espacios, no nulo)
        if (!isset($linea[10]) || empty($linea[10]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[10])) {
            $es_valido = false;
        }

        // Validar Apellido Materno (solo letras y espacios, puede ser nulo)
        if (!isset($linea[11]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/u', $linea[11])) {
            $es_valido = false;
        }

        // Validar Fecha Último Logro (formato YYYY-MM-DD)
        if (!isset($linea[13]) || empty($linea[13]) || !preg_match('/^\d{4}-(01|02)$/', $linea[13])) {
            $es_valido = false;
        }

        // Validar Fecha Última Carga (formato YYYY-MM-DD)
        if (!isset($linea[14]) || empty($linea[14]) || !preg_match('/^\d{4}-(01|02)$/', $linea[14])) {
            $es_valido = false;
        }

        // Si no es válido, guardarlo en el archivo de errores
        if (!$es_valido) {
            $array_errores[] = $linea;

            // Corregir atributos inválidos si es posible
            $linea_corregida = $linea;

            // Corregir RUT
            if (!isset($rut) || !ctype_digit($rut) || !(strlen($rut) == 7 || strlen($rut) == 8)) {
                $linea_corregida[$posicion_rut] = 'x';
            } else {
                $linea_corregida[$posicion_rut] = trim($rut);
            }

            // Corregir Cohorte
            if (!isset($linea[2]) || !preg_match('/^\d{4}-(01|02)$/', trim($linea[2]))) {
                $linea_corregida[2] = 'x';
            } else {
                $linea_corregida[2] = trim($linea[2]);
            }

            // Corregir fecha
            if (!isset($linea[13]) || !preg_match('/^\d{4}-(01|02)$/', trim($linea[13]))) {
                $linea_corregida[13] = 'x';
            } else {
                $linea_corregida[13] = trim($linea[13]);
            }

            // Corregir ultimo logro
            if (!isset($linea[14]) || !preg_match('/^\d{4}-(01|02)$/', trim($linea[14]))) {
                $linea_corregida[14] = 'x';
            } else {
                $linea_corregida[14] = trim($linea[14]);
            }

            // Corregir Número de Estudiante
            if (!isset($linea[3]) || !preg_match('/^\d{6}$/', trim($linea[3]))) {
                $linea_corregida[3] = 'x';
            } else {
                $linea_corregida[3] = trim($linea[3]);
            }

            // Corregir Bloqueo
            if (!isset($linea[4]) || !preg_match('/^[NS]$/', trim($linea[4]))) {
                $linea_corregida[4] = 'x';
            } else {
                $linea_corregida[4] = trim($linea[4]);
            }

            // Corregir DV
            if (!isset($linea[7]) || !preg_match('/^[0-9K]$/', trim($linea[7]))) {
                $linea_corregida[7] = 'x';
            } else {
                $linea_corregida[7] = trim($linea[7]);
            }

            // Corregir nombres y apellidos
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/u', trim($linea[8]))) {
                $linea_corregida[8] = 'x';
            } else {
                $linea_corregida[8] = trim($linea[8]);
            }
            // Corregir nombres y apellidos
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/u', trim($linea[9]))) {
                $linea_corregida[9] = 'x';
            } else {
                $linea_corregida[9] = trim($linea[9]);
            }

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/u', trim($linea[10]))) {
                $linea_corregida[10] = 'x';
            } else {
                $linea_corregida[10] = trim($linea[10]);
            }

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]*$/u', trim($linea[11]))) {
                $linea_corregida[11] = 'x';
            } else {
                $linea_corregida[11] = trim($linea[11]);
            }
            // Agregar la línea corregida al archivo corregido
            $array_corregidos[] = $linea_corregida;

        } else {
            // Si es válido, agregar a la lista de válidos
            $array_validos[] = $linea;
        }
    }

    // Guardar los errores y los corregidos en archivos CSV
    guardar_csv($array_errores, $nombre_archivo_errores);
    guardar_csv($array_corregidos, $nombre_archivo_corregidos);

    // Retornar los datos válidos
    return $array_validos;
}


// Función para guardar los datos en un archivo CSV
function guardar_csv($array_datos, $nombre_archivo) {
    $archivo = fopen($nombre_archivo, 'w');
    foreach ($array_datos as $linea) {
        fputcsv($archivo, $linea, ';');
    }
    fclose($archivo);
}

// Función principal, esta es la funcion q juntabtodo y carga los datos en las tablas, la idea es poner todas las funciones de validacion aca adentro cuando esten listas
function procesar_datos_y_insertar($ruta_archivo, $posicion_rut, $nombre_archivo_errores, $nombre_archivo_corregidos, $database, $tabla) {
    // Cargar los datos del archivo
    $array_datos = abrir_archivo($ruta_archivo);
    
    // Validar y separar datos válidos de los errores
    $datos_validos = validar_y_corregir_datos_estudiante($array_datos, $posicion_rut, $nombre_archivo_errores, $nombre_archivo_corregidos);
    ## aca poner las demas funciones, validar asigantura, y otros

    // Insertar los datos válidos en la base de datos
    foreach ($datos_validos as $fila) {
        insertar_en_tabla($database, $tabla, $fila);
    }
}

# trabajo los datos
$array_datos_1 = abrir_archivo($ruta);
echo "cantidad de datos en array original", count($array_datos_1);
echo "\n";

$estudiantes_validos = validar_y_corregir_datos_estudiante($array_datos_1, 6, "estudiantes_invalidos.csv", "estudiantes_corregidos.csv");
#imprimir_bonito($array_datos_1);
echo "cantidad de datos en array limpio", count($estudiantes_validos);

// // Procesar el archivo y manejar los datos
#procesar_datos_y_insertar("data/estudiantes.csv", 6, "estudiantes_invalidos.csv", "estudiantes_corregidos.csv", $db, $estudiantes);



?>