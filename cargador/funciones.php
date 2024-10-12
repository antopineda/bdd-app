<?php
require "utils.php";

// Parte 1: Abrir archivo 1
$ruta_estudiantes = "data/estudiantes.csv";
$ruta_asignaturas = "data/asignaturas.csv";
$ruta_planes = "data/planes.csv";
$ruta_prerrequisitos = "data/prerequisitos.csv";
$ruta_notas = "data/notas.csv";

function abrir_archivo($ruta) {
    $archivo_datos_1 = fopen($ruta, "r"); // Abrir archivo en modo lectura
    $array_datos_1 = [];
    while (!feof($archivo_datos_1)) {
        $linea = fgets($archivo_datos_1);
        $linea = rtrim($linea); // Elimina el salto de línea y espacios en blanco al final
        if (!empty($linea)) { // Verifica que la línea no esté vacía
            $array_datos_1[] = explode(";", $linea);
        }
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
            echo "Error: rut inválido\n";
            $es_valido = false;
        }

        // Validar Cohorte (en formato YYYY-01 o YYYY-02)
        if (isset($linea[2]) && !empty($linea[2]) && !preg_match('/^\d{4}-(01|02)$/', $linea[2])) {
            echo "Error: Cohorte inválido\n", $linea[2];
            $es_valido = false;
        }

        // Validar Número de Estudiante (6 dígitos)
        if (isset($linea[3]) && !empty($linea[3]) && !preg_match('/^\d{6}$/', $linea[3])) {
            echo "Error: num inválido\n";
            $es_valido = false;
        }

        // Validar Bloqueo (S o N)
        if (isset($linea[4]) && !empty($linea[4]) && !preg_match('/^[NS]$/', $linea[4])) {
            echo "Error: bloqueo inválido\n";
            $es_valido = false;
        }

        // Validar DV (un solo dígito o 'K')
        if (isset($linea[7]) && !empty($linea[7]) && !preg_match('/^[0-9K]$/', $linea[7])) {
            echo "Error: dv inválido\n";
            $es_valido = false;
        }

        // Validar y corregir Nombres (solo letras y espacios, no nulo)
        if (!isset($linea[8]) || empty($linea[8]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[8])) {
            echo "Error: nombre1 inválido\n";
            $es_valido = false;
        }
        // Validar y corregir Nombres (solo letras y espacios, no nulo)
        if (!isset($linea[9]) || empty($linea[9]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[9])) {
            echo "Error: nombre 2 inválido\n";
            $es_valido = false;
        }

        // Validar y corregir Apellido Paterno (solo letras y espacios, no nulo)
        if (!isset($linea[10]) || empty($linea[10]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', $linea[10])) {
            echo "Error: paterno inválido\n";
            $es_valido = false;
        }

        // Validar Apellido Materno (solo letras y espacios, puede ser nulo)
        if (!isset($linea[11]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', $linea[11])) {
            echo "Error: materno inválido\n";
            $es_valido = false;
        }

        // Validar Fecha Último Logro (formato YYYY-MM-DD)
        if (!isset($linea[13]) || empty($linea[13]) || !preg_match('/^\d{4}-(01|02)$/', $linea[13])) {
            echo "Error: logro inválido\n";
            $es_valido = false;
        }

        // Validar Fecha Última Carga (formato YYYY-MM)
        if (!isset($linea[14]) || empty($linea[14]) || !preg_match('/^\d{4}-(01|02)$/', $linea[14])) {
            echo "Error: carga inválido\n", $linea[14];
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

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', trim($linea[10]))) {
                $linea_corregida[10] = 'x';
            } else {
                $linea_corregida[10] = trim($linea[10]);
            }

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', trim($linea[11]))) {
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

function validar_y_corregir_datos_asignaturas($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos) {
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];

    foreach ($array_datos as $linea) {
        $es_valido = true;

        // Plan debe ser dos letras seguidas de uno o más dígitos (ej. 'AA1', 'BB12', etc.)
        if (isset($linea[0]) && !empty($linea[0]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[0])) {
            echo "Error: plan inválido\n", $linea[0];
            $es_valido = false;
        }

        // Asignatura_id debe ser dos letras seguidas de uno o más dígitos (ej. 'AA1', 'BB123', etc.)
        if (isset($linea[1]) && !empty($linea[1]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[1])) {
            echo "Error: id inválido\n", $linea[1];
            $es_valido = false;
        }

        // Nivel puede ser 'B', 'L' o un número del 1 al 10
        if (isset($linea[3]) && !empty($linea[3]) && !preg_match('/^(B|L|[1-9]|10)$/', $linea[3])) {
            echo "Error: nivel inválido\n", $linea[3];
            $es_valido = false;
        }

        // Prerequisito debe ser 'B' o 'L'
        if (isset($linea[4]) && !empty($linea[4]) && !preg_match('/^[BL]$/', $linea[4])) {
            echo "prerequisito: valor inválido\n", $linea[4];
            $es_valido = false;
        }

        // Si no es válido, guardarlo en el archivo de errores y corregir los errores
        if (!$es_valido) {
            $array_errores[] = $linea;

            // Corregir atributos inválidos si es posible
            $linea_corregida = $linea;

            // Corregir plan si no es válido
            if (!isset($linea[0]) || !preg_match('/^[A-Za-z]{2}\d+$/', trim($linea[0]))) {
                $linea_corregida[0] = 'x'; // Valor por defecto para plan
            } else {
                $linea_corregida[0] = trim($linea[0]);
            }

            // Corregir asignatura_id si no es válido
            if (!isset($linea[1]) || !preg_match('/^[A-Za-z]{2}\d+$/', trim($linea[1]))) {
                $linea_corregida[1] = 'x'; // Valor por defecto para asignatura_id
            } else {
                $linea_corregida[1] = trim($linea[1]);
            }

            // Corregir nivel si no es válido
            if (!isset($linea[3]) || !preg_match('/^(B|L|[1-9]|10)$/', trim($linea[3]))) {
                $linea_corregida[3] = 'x'; // Valor por defecto para nivel
            } else {
                $linea_corregida[3] = trim($linea[3]);
            }

            // Corregir prerequisito si no es válido
            if (!isset($linea[4]) || !preg_match('/^[BL]$/', trim($linea[4]))) {
                $linea_corregida[4] = 'x'; // Valor por defecto para prerequisito
            } else {
                $linea_corregida[4] = trim($linea[4]);
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

function validar_y_corregir_datos_planes($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos) {
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];

    foreach ($array_datos as $linea) {
        $es_valido = true;

        // 1. Código de Plan: Debe ser dos letras seguidas de dígitos
        if (!preg_match('/^[A-Za-z]{2}\d+$/', $linea[0])) {
            echo "Error: código de plan inválido\n", $linea[0];
            $es_valido = false;
        }

        // 2. Facultad: Solo puede ser "Facultad de Magia y Hechicería" o "Ministerio de magia"
        if (!in_array(trim($linea[1]), ['Facultad de Magia y Hechicería', 'Ministerio de magia'])) {
            echo "Error: facultad inválida\n";
            $es_valido = false;
        }

        // 3. Plan: Elimina lo que está en paréntesis y "PLAN Año"
        if (isset($linea[3])) {
            $linea[3] = preg_replace('/\(.*?\)|PLAN \d{4}/', '', trim($linea[3]));
            $linea[3] = trim($linea[3]); // Elimina espacios sobrantes
        }

        // 4. Jornada: Debe ser "diurno" o "vespertino", sin espacios
        $linea[4] = preg_replace('/\s+/', '', strtolower($linea[4]));
        if (!in_array($linea[4], ['diurno', 'vespertino'])) {
            echo "Error: jornada inválida\n";
            $es_valido = false;
        }

        // 5. Sede: Debe ser "Uagadou", "Beauxbaton" o "Hogwarts"
        if (!in_array(trim($linea[5]), ['Uagadou', 'Beauxbaton', 'Hogwarts'])) {
            echo "Error: sede inválida\n";
            $es_valido = false;
        }

        // 6. Grado: Debe ser uno de los valores válidos, convertido a mayúsculas
        $linea[6] = strtoupper(trim($linea[6]));
        if (!in_array($linea[6], ['PROGRAMA ESPECIAL', 'PREGRADO', 'POSTGRADO'])) {
            echo "Error: grado inválido\n";
            $es_valido = false;
        }

        // 7. Modalidad: Debe ser "presencial" o "online"
        if (!in_array(trim($linea[7]), ['Presencial', 'OnLine'])) {
            echo "Error: modalidad inválida\n";
            $es_valido = false;
        }

        // 8. Inicio de Vigencia: Formato de fecha dd/mm/aaaa
        if (!preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $linea[8])) {
            echo "Error: fecha de inicio de vigencia inválida\n", $linea[8];
            $es_valido = false;
        }

        // Si la línea tiene errores, la guardamos en los errores y corregimos lo necesario
        if (!$es_valido) {
            $array_errores[] = $linea;
            if (empty(implode('', $linea))) {
                // Si la línea está completamente vacía, se guarda en el array de invalido
                $array_invalido[] = $linea;
                continue; // Omitir el procesamiento de esta línea
            }
            // Corregir los campos con valores por defecto si son inválidos
            if (!preg_match('/^[A-Za-z]{2}\d+$/', trim($linea[0]))) {
                $linea[0] = 'x'; // Valor por defecto para código de plan
            }
            if (!in_array(trim($linea[1]), ['Facultad de Magia y Hechicería', 'Ministerio de magia'])) {
                $linea[1] = 'x'; // Valor por defecto para facultad
            }
            $linea[3] = preg_replace('/\(.*?\)|PLAN \d{4}/', '', trim($linea[3]));
            $linea[3] = preg_replace('/\s+/', '', strtolower($linea[3])); // Deja en minúsculas

            if (!in_array($linea[4], ['diurno', 'vespertino'])) {
                $linea[4] = 'vespertino'; // Valor por defecto para jornada
            }
            if (!in_array(trim($linea[5]), ['Uagadou', 'Beauxbaton', 'Hogwarts'])) {
                $linea[5] = 'Hogwarts'; // Valor por defecto para sede
            }
            if (!in_array($linea[6], ['PROGRAMA ESPECIAL', 'PREGRADO', 'POSTGRADO'])) {
                $linea[6] = 'x'; // Valor por defecto para grado
            }
            if (!in_array(trim($linea[7]), ['Presencial', 'OnLine'])) {
                $linea[7] = 'Presencial'; // Valor por defecto para modalidad
            }
            if (!preg_match('/^\d{2}\/\d{2}\/\d{2}$/', trim($linea[8]))) {
                $linea[8] = 'x'; // Valor por defecto para fecha de vigencia
            }
            // Agregar la línea corregida a los corregidos
            $array_corregidos[] = $linea;
        } else {
            // Si es válida, agregarla a los válidos
            $array_validos[] = $linea;
        }
    }

    // Guardar los errores y los corregidos en archivos CSV
    guardar_csv($array_errores, $nombre_archivo_errores);
    guardar_csv($array_corregidos, $nombre_archivo_corregidos);

    // Retornar los datos válidos
    return $array_validos;
}

// funcion para prerequisitos
function validar_y_corregir_datos_prerrequisitos($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos) {
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];

    foreach ($array_datos as $linea) {
        $es_valido = true;

        // Verificar el nivel
        if (isset($linea[3])) {
            $nivel = trim($linea[3]); // Asegúrate de que el índice sea correcto para el nivel
            if (empty($nivel) || !ctype_digit($nivel)) {
                echo "Error: nivel inválido\n";
                $es_valido = false; // Marcamos como no válido
            }
        }

        // Verificar prerequisitos
        if (isset($linea[4])) {
            $prerequisitos = trim($linea[4]); // Accedemos a los prerequisitos
            
            // Expresión regular para validar los prerequisitos
            $valid_prereq_regex = '/^(ingreso|Ingreso|egreso|B|L|B v L|\d{4}|)$/'; // Permite los valores válidos y un número de 4 dígitos
            
            // Validación del prerequisito
            if (!preg_match($valid_prereq_regex, $prerequisitos)) {
                echo "Error: prerequisito inválido - $prerequisitos\n";
                $es_valido = false; // Marcamos como no válido
            }
        }

        // Si la línea tiene errores, la guardamos en los errores y corregimos lo necesario
        if (!$es_valido) {
            // Agregar la línea a errores

            $array_errores[] = $linea; 

            if (isset($linea[3])) {
                $linea[3] = 'x'; // Corrección: asignar "x" si el nivel no es válido
            }

            if (isset($linea[4]) && $linea[4] === "por fijar") {
                continue; // Saltar a la siguiente iteración
            }
            $array_corregidos[] = $linea;
        } else {
            // Si es válida, agregarla a los válidos
            $array_validos[] = $linea;
        }

        // Siempre agregar la línea corregida a los corregidos
         
    }

    // Guardar los errores y los corregidos en archivos CSV
    guardar_csv($array_errores, $nombre_archivo_errores);
    guardar_csv($array_corregidos, $nombre_archivo_corregidos);

    // Retornar los datos válidos
    return $array_validos;
}

function validar_y_corregir_datos_notas($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos){
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];
    foreach ($array_datos as $linea) {
        $es_valido = true;

        // Plan debe ser dos letras seguidas de uno o más dígitos (ej. 'AA1', 'BB12', etc.)
        if (isset($linea[0]) && !empty($linea[0]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[0])) {
            echo "Error: plan inválido\n", $linea[0];
            $es_valido = false;
        }

        // Validar Cohorte (en formato YYYY-01 o YYYY-02)
        if (isset($linea[2]) && !empty($linea[2]) && !preg_match('/^\d{4}-(01|02)$/', $linea[2])) {
            echo "Error: Cohorte inválido\n", $linea[2];
            $es_valido = false;
        }

        // 5. Sede: Debe ser "Uagadou", "Beauxbaton" o "Hogwarts"
        if (!in_array(trim($linea[3]), ['Uagadou', 'Beauxbaton', 'Hogwarts'])) {
            echo "Error: sede inválida\n";
            $es_valido = false;
        }

        // Verificar el RUT
        if (isset($linea[4])) {
            $rut = trim($linea[4]);
            if (empty($rut) || !ctype_digit($rut) || !(strlen($rut) == 7 || strlen($rut) == 8)) {
                $es_valido = false;
            }}
        
        // Validar DV (un solo dígito o 'K')
        if (isset($linea[5]) && !empty($linea[5]) && !preg_match('/^[0-9K]$/', $linea[5])) {
            echo "Error: dv inválido\n";
            $es_valido = false;
        }

        // Validar y corregir Nombre (solo letras y espacios, no nulo)
        if (!isset($linea[6]) || empty($linea[6]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[6])) {
            echo "Error: nombre 2 inválido\n";
            $es_valido = false;
        }

        // Validar y corregir Apellido Paterno (solo letras y espacios, no nulo)
        if (!isset($linea[7]) || empty($linea[7]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', $linea[7])) {
            echo "Error: paterno inválido\n";
            $es_valido = false;
        }

        // Validar y corregir Apellido materno (solo letras y espacios, no nulo)
        if (!isset($linea[8]) || empty($linea[8]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[8])) {
            echo "Error: materno inválido\n";
            $es_valido = false;
        }
        // Validar Número de Estudiante (6 dígitos)
         if (isset($linea[9]) && !empty($linea[9]) && !preg_match('/^\d{6}$/', $linea[9])) {
            echo "Error: num inválido\n";
            $es_valido = false;
        }

        // Validar periodo_asigantura (en formato YYYY-01 o YYYY-02)
        if (isset($linea[10]) && !empty($linea[10]) && !preg_match('/^\d{4}-(01|02)$/', $linea[10])) {
            echo "Error: periodo inválido\n", $linea[2];
            $es_valido = false;
        }

        // Asignatura_id debe ser dos letras seguidas de uno o más dígitos (ej. 'AA1', 'BB123', etc.)
        if (isset($linea[11]) && !empty($linea[11]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[11])) {
            echo "Error: id inválido\n", $linea[11];
            $es_valido = false;
        }
        
        // convocatria debe ser un mes
        $linea[13] = preg_replace('/\s+/', '', strtolower($linea[13]));
        if (!in_array($linea[13], ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'])) {
            echo "Error: convocatoria inválida\n";
            $es_valido = false;
        }

        # calificaicon
        $linea[14] = preg_replace('/\s+/', '', strtoupper($linea[14]));
        $calificaciones_validas = ['SO', 'MB', 'B', 'SU', 'I', 'M', 'MM', 'NP', 'EX', 'A', 'R'];
        if (!in_array($linea[14], $calificaciones_validas)) {
            echo "Error: calificacion inválida\n";
            $es_valido = false;
        }

        # nota
        if (isset($linea[15])) {
            $nota = trim($linea[15]);
            if (!is_numeric($nota) || floatval($nota) < 1 || floatval($nota) > 7) {
                echo "Error: nota inválida\n";
                $es_valido = false;
            }
        }}


        // Si no es válido, guardarlo en el archivo de errores
        if (!$es_valido) {
            $array_errores[] = $linea;

            // Corregir atributos inválidos si es posible
            $linea_corregida = $linea;

            // Corregir plan si no es válido
            if (!isset($linea[0]) || !preg_match('/^[A-Za-z]{2}\d+$/', trim($linea[0]))) {
                $linea_corregida[0] = 'x'; // Valor por defecto para plan
            } else {
                $linea_corregida[0] = trim($linea[0]);
            }

            // Corregir Cohorte
            if (!isset($linea[2]) || !preg_match('/^\d{4}-(01|02)$/', trim($linea[2]))) {
                $linea_corregida[2] = 'x';
            } else {
                $linea_corregida[2] = trim($linea[2]);
            }

            # sede 
            if (!in_array(trim($linea[3]), ['Uagadou', 'Beauxbaton', 'Hogwarts'])) {
                $linea[3] = 'Hogwarts'; // Valor por defecto para sede
            }

            // Corregir RUT
            if (!isset($rut) || !ctype_digit($rut) || !(strlen($rut) == 7 || strlen($rut) == 8)) {
                $linea_corregida[4] = 'x';
            } else {
                $linea_corregida[4] = trim($rut);
            }

            // Corregir DV
            if (!isset($linea[5]) || !preg_match('/^[0-9K]$/', trim($linea[5]))) {
                $linea_corregida[5] = 'x';
            } else {
                $linea_corregida[5] = trim($linea[5]);
            }


            // Corregir nombres y apellidos
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/u', trim($linea[6]))) {
                $linea_corregida[6] = 'x';
            } else {
                $linea_corregida[6] = trim($linea[6]);
            }
            // Corregir nombres y apellidos
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/u', trim($linea[7]))) {
                $linea_corregida[7] = 'x';
            } else {
                $linea_corregida[7] = trim($linea[7]);
            }

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', trim($linea[8]))) {
                $linea_corregida[8] = 'x';
            } else {
                $linea_corregida[8] = trim($linea[8]);
            }

            // Corregir Número de Estudiante
            if (!isset($linea[9]) || !preg_match('/^\d{6}$/', trim($linea[9]))) {
                $linea_corregida[9] = 'x';
            } else {
                $linea_corregida[9] = trim($linea[9]);
            }

            // Corregir asignatura periodo
            if (!isset($linea[10]) || !preg_match('/^\d{4}-(01|02)$/', trim($linea[10]))) {
                $linea_corregida[10] = 'x';
            } else {
                $linea_corregida[10] = trim($linea[10]);
            }

            // Corregir plan si no es válido
            if (!isset($linea[11]) || !preg_match('/^[A-Za-z]{2}\d+$/', trim($linea[11]))) {
                $linea_corregida[11] = 'x'; // Valor por defecto para plan
            } else {
                $linea_corregida[11] = trim($linea[11]);
            }

            # convoctaria, poner marzo por defecto si es mala
            $linea[13] = preg_replace('/\s+/', '', strtolower($linea[13]));
            if (!in_array($linea[13], ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'])) {
                $linea_corregida[13] = 'mar';
            } else {
                $linea_corregida[13] = $linea[13];
            }

            # si no hay calificaion poner x
            // Corregir calificación
            $linea[14] = preg_replace('/\s+/', '', strtoupper($linea[14]));
            $calificaciones_validas = ['SO', 'MB', 'B', 'SU', 'I', 'M', 'MM', 'NP', 'EX', 'A', 'R'];
            if (!in_array($linea[14], $calificaciones_validas)) {
                $linea_corregida[14] = 'x';
            } else {
                $linea_corregida[14] = $linea[14];
            }

            // # si no hay nota no poner fila de array validos y de corregidos
            // if (!isset($linea[15]) || !is_numeric(trim($linea[15])) || floatval(trim($linea[15])) < 1 || floatval(trim($linea[15])) > 7) {
            //     continue;
            // }
            
            // Agregar la línea corregida al archivo corregido
            $array_corregidos[] = $linea_corregida;

        } else {
            // Si es válido, agregar a la lista de válidos
            $array_validos[] = $linea;
        }
    

    // Guardar los errores y los corregidos en archivos CSV
    guardar_csv($array_errores, $nombre_archivo_errores);
    guardar_csv($array_corregidos, $nombre_archivo_corregidos);

    // Retornar los datos válidos
    return $array_validos;

}

function validar_y_corregir_datos_planeacion($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos){
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];

}
function validar_y_corregir_datos_docentes($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos){
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];

}





//Función para guardar los datos en un archivo CSV
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

// # trabajo los datos de estudiantes, funcinoa ok.
// $array_datos_1 = abrir_archivo($ruta_estudiantes);
// echo "cantidad de datos en array original", count($array_datos_1);
// echo "\n";

// $estudiantes_validos = validar_y_corregir_datos_estudiante($array_datos_1, 6, "estudiantes_invalidos.csv", "estudiantes_corregidos.csv");
// #imprimir_bonito($array_datos_1);
// echo "cantidad de datos en array limpio", count($estudiantes_validos);

// $array_datos_2 = abrir_archivo($ruta_asignaturas);
// echo "cantidad de datos en array original", count($array_datos_2);
// echo "\n";

// $asignaturas_validos = validar_y_corregir_datos_asignaturas($array_datos_2, "asignaturas_invalidos.csv", "asignaturas_corregidos.csv");
// #imprimir_bonito($array_datos_1);
// echo "cantidad de datos en array limpio", count($asignaturas_validos);

// $array_datos_3 = abrir_archivo($ruta_planes);
// echo "cantidad de datos en array original", count($array_datos_3);
// echo "\n";

// $planes_validos = validar_y_corregir_datos_planes($array_datos_3, "planes_invalidos.csv", "planes_corregidos.csv");
// #imprimir_bonito($array_datos_1);
// echo "cantidad de datos en array limpio", count($planes_validos);

// $array_datos_4 = abrir_archivo($ruta_prerrequisitos);
// echo "cantidad de datos en array original", count($array_datos_4);
// echo "\n";

// $prerrequisitos_validos = validar_y_corregir_datos_prerrequisitos($array_datos_4, "prerrequisitos_invalidos.csv", "prerrequisitos_corregidos.csv");
// #imprimir_bonito($prerrequisitos_validos);
// echo "cantidad de datos en array limpio", count($prerrequisitos_validos);

$array_datos_5 = abrir_archivo($ruta_notas);
echo "cantidad de datos en array original", count($array_datos_5);
echo "\n";

$notas_validos = validar_y_corregir_datos_notas($array_datos_5, "notas_invalidos.csv", "notas_corregidos.csv");
#imprimir_bonito($prerrequisitos_validos);
echo "cantidad de datos en array limpio", count($notas_validos);


// // Procesar el archivo y manejar los datos
#procesar_datos_y_insertar("data/estudiantes.csv", 6, "estudiantes_invalidos.csv", "estudiantes_corregidos.csv", $db, $estudiantes);


?>