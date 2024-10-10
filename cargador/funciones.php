<?php
require "utils.php";
require "poblar_tablas.php";

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

// Validar los RUTs y guardar los errores en un archivo CSV
function validar_y_guardar_rut($array_datos, $posicion_rut, $nombre_archivo_errores) {
    $array_valido = [];
    $array_errores = [];
    $ruts_vistos = [];

    foreach ($array_datos as $linea) {
        if (isset($linea[6])) {
            $rut = trim($linea[$posicion_rut]);
        
            // Condiciones de validación del RUT
            if (empty($rut) || !ctype_digit($rut) || !(strlen($rut) == 7 || strlen($rut) == 8) || in_array($rut, $ruts_vistos)) {
                $array_errores[] = $linea; // Guardar en el archivo de errores
            } else {
                $ruts_vistos[] = $rut; // Agregar a la lista de válidos
                $array_valido[] = $linea;
            }
        }
    }
    // Guardar los errores en un archivo CSV
    guardar_errores_csv($array_errores, $nombre_archivo_errores);

    // Retornar los datos válidos
    return $array_valido;
}

// Función para guardar los errores en un archivo CSV
function guardar_errores_csv($array_errores, $nombre_archivo) {
    $archivo_errores = fopen($nombre_archivo, 'w');
    foreach ($array_errores as $linea_error) {
        fputcsv($archivo_errores, $linea_error);
    }
    fclose($archivo_errores);
}

// Función principal, esta es la funcion q juntabtodo y carga los datos en las tablas, la idea es poner todas las funciones de validacion aca adentro cuando esten listas
function procesar_datos_y_insertar($ruta_archivo, $posicion_rut, $nombre_archivo_errores, $database, $tabla) {
    // Cargar los datos del archivo
    $array_datos = abrir_archivo($ruta_archivo);
    
    // Validar y separar datos válidos de los errores
    $datos_validos = validar_y_guardar_rut($array_datos, $posicion_rut, $nombre_archivo_errores);
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

$estudiantes_validos = validar_y_guardar_rut($array_datos_1, 6, "estudiantes_invalidos.csv");
#imprimir_bonito($array_datos_1);
echo "cantidad de datos en array limpio", count($estudiantes_validos);

// // Procesar el archivo y manejar los datos
procesar_datos_y_insertar("data/estudiantes.csv", 6, "estudiantes_invalidos.csv", $db, $estudiantes);



?>