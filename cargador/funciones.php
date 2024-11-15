<?php
ini_set('memory_limit', '1G');  

function abrir_archivo($ruta) {
    $archivo_datos_1 = fopen($ruta, "r"); // Abrir archivo en modo lectura
    $array_datos_1 = [];
    fgets($archivo_datos_1); // Leer y descartar la primera línea
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

function abrir_archivo_notas($ruta, $indice_inicio) {
    // Abrir el archivo en modo lectura
    $archivo = fopen($ruta, 'r');

    // Verificar si el archivo se abrió correctamente
    if ($archivo === false) {
        throw new Exception("No se pudo abrir el archivo: $ruta");
    }

    $contador = 0; // Contador para avanzar hasta el índice de inicio
    $lote = [];    // Array que almacenará el lote de notas
    $tamaño_lote = 1000; // Tamaño del lote a leer

    // Saltar la primera línea (encabezado) si la tiene
    if ($indice_inicio === 0){
        fgets($archivo);
    }

    // Saltar hasta el índice de inicio
    while ($contador < $indice_inicio && !feof($archivo)) {
        fgets($archivo); // Leer y descartar hasta llegar al índice de inicio
        $contador++;
    }

    // Leer el archivo desde el índice especificado y separar por ";"
    while (!feof($archivo) && count($lote) < $tamaño_lote) {
        $linea = fgets($archivo);
        $linea = rtrim($linea); // Eliminar el salto de línea y espacios al final

        if (!empty($linea)) { // Verificar que la línea no esté vacía
            $lote[] = explode(";", $linea); // Separar por ';' y añadir al lote
        }
    }

    // Cerrar el archivo
    fclose($archivo);

    // Retornar el lote de datos
    return $lote;
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
            if (empty($rut) || !ctype_digit($rut) || !(strlen($rut) == 7 || strlen($rut) == 8)) {
                $es_valido = false;
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
        if (!isset($linea[10]) || empty($linea[10]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', $linea[10])) {
            $es_valido = false;
        }

        // Validar Apellido Materno (solo letras y espacios, puede ser nulo)
        if (!isset($linea[11]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', $linea[11])) {
            $es_valido = false;
        }

        ## si el logro no contiene la palabra semestre, ingreso o licenciatura, ES INVALIDO
        // Validar el logro (debe contener las palabras "semestre", "ingreso", o "licenciatura")
        // if (!isset($linea[12]) || empty($linea[12]) || !preg_match('/(semestre|ingreso|licenciatura)/i', $linea[12])) {
        //     $es_valido = false;
        // }

        $logro = $linea[12];  // Ejemplo: "QUINTO AÑO"

        // Crear un mapa para convertir los nombres ordinales a números
        $ordenes = [
            'PRIMER' => 1,
            'SEGUNDO' => 2,
            'TERCER' => 3,
            'CUARTO' => 4,
            'QUINTO' => 5,
            'SEXTO' => 6,
            'SÉPTIMO' => 7,  
            'SEPTIMO' => 7,  
            'OCTAVO' => 8,
            'NOVENO' => 9,
            'DÉCIMO' => 10,
            'DECIMO' => 10,
        ];

        // Buscar coincidencias con el formato ordinal (en cualquier parte del texto)
        if (preg_match('/(PRIMER|SEGUNDO|TERCER|CUARTO|QUINTO)\s*AÑO/i', $logro, $matches)) {
            $año_ordinal = $matches[1];  // Extraer la palabra ordinal
            // Convertir el ordinal a un número usando el mapa
            if (isset($ordenes[strtoupper($año_ordinal)])) {
                $año = $ordenes[strtoupper($año_ordinal)];
                $semestre_corregido = $año * 2;  // Convertir años a semestres
                $linea[12] = $semestre_corregido . " SEMESTRE";
                
            }
        }
        else if (preg_match('/(PRIMER|SEGUNDO|TERCER|CUARTO|QUINTO|SEXTO|S[EÉ]PTIMO|OCTAVO|NOVENO|D[ÉE]CIMO)\s*SEMESTRE/i', $logro, $matches)) {
            $semestre_ordinal = $matches[1];  // Extraer la palabra ordinal
            // Convertir el ordinal a un número usando el mapa
            if (isset($ordenes[strtoupper($semestre_ordinal)])) {
                $semestre = $ordenes[strtoupper($semestre_ordinal)];
                $linea[12] = $semestre . " SEMESTRE";  // Ya es un semestre, no hay que multiplicar
            }
        }
        // Buscar coincidencias con números seguidos de "AÑO" (como "1 AÑO", "2 AÑO", "5 AÑO")
        else if (preg_match('/(\d+)\s*AÑO/i', $logro, $matches)) {
            $año_numero = (int)$matches[1];  // Convertir el número a entero
            $semestre_corregido = $año_numero * 2;  // Convertir años a semestres
            $linea[12] = $semestre_corregido . " SEMESTRE";  // Cambiar a formato semestre
        }


        // Validar Fecha Último Logro (formato YYYY-MM-DD)
        if (!isset($linea[13]) || empty($linea[13]) || !preg_match('/^\d{4}-(01|02)$/', $linea[13])) {
            $es_valido = false;
        }

        // Validar Fecha Última Carga (formato YYYY-MM)
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
            // if (!isset($linea[14]) || !preg_match('/^\d{4}-(01|02)$/', trim($linea[14]))) {
            //     $linea_corregida[14] = 'x';
            // } else {
            //     $linea_corregida[14] = trim($linea[14]);
            // }


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
            $array_validos[] = $linea_corregida;

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
        $linea[0] = trim($linea[0]);
        if (isset($linea[0]) && !empty($linea[0]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[0])) {
            $es_valido = false;
            $linea[0] = 'x'; // Valor por defecto para plan
        }

        // Asignatura_id debe ser dos letras seguidas de uno o más dígitos (ej. 'AA1', 'BB123', etc.)
        $linea[1] = trim($linea[1]);
        if (isset($linea[1]) && !empty($linea[1]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[1])) {
            $es_valido = false;
            $linea[1] = 'x'; // Valor por defecto para asignatura_id
        }

        // Nivel puede ser 'B', 'L' o un número del 1 al 10
        $linea[3] = trim($linea[3]);
        if (isset($linea[3]) && !empty($linea[3]) && !preg_match('/^(B|L|[1-9]|10)$/', $linea[3])) {
            $es_valido = false;
            $linea[3] = 'x'; // Valor por defecto para nivel
        }

        // Prerequisito debe ser 'B' o 'L'
        $linea[4] = trim($linea[4]);
        if (isset($linea[4]) && !empty($linea[4]) && !preg_match('/^[BL]$/', $linea[4])) {
            $es_valido = false;
            $linea[4] = 'x'; // Valor por defecto para prerequisito
        }

        // Si no es válido, guardarlo en el archivo de errores y corregir los errores
        if (!$es_valido) {
            $array_errores[] = $linea;
            $array_corregidos[] = $linea;
            $array_validos[] = $linea;
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
            $es_valido = false;
            $linea[0] = 'x'; // Valor por defecto para código de plan
        }

        // 2. Facultad: Solo puede ser "Facultad de Magia y Hechicería" o "Ministerio de magia"
        if (!in_array(trim($linea[1]), ['Facultad de Magia y Hechicería', 'Ministerio de magia'])) {
            $es_valido = false;
            $linea[1] = 'x'; // Valor por defecto para facultad
        }

        // 3. Plan: Elimina lo que está en paréntesis y "PLAN Año"
        $linea[3] = preg_replace('/\(.*?\)|PLAN \d{4}/', '', trim($linea[3]));
        $linea[3] = preg_replace('/\s+/', '', strtolower($linea[3])); // Deja en minúsculas

        // 4. Jornada: Debe ser "diurno" o "vespertino", sin espacios
        $linea[4] = preg_replace('/\s+/', '', strtolower($linea[4]));
        if (!in_array($linea[4], ['diurno', 'vespertino'])) {
            $es_valido = false;
            $linea[4] = 'vespertino'; // Valor por defecto para jornada
        }

        // 5. Sede: Debe ser "Uagadou", "Beauxbaton" o "Hogwarts"
        if (!in_array(trim($linea[5]), ['Uagadou', 'Beauxbaton', 'Hogwarts'])) {
            $es_valido = false;
            $linea[5] = 'Hogwarts'; // Valor por defecto para sede
        }

        // 6. Grado: Debe ser uno de los valores válidos, convertido a mayúsculas
        $linea[6] = strtoupper(trim($linea[6]));
        if (!in_array($linea[6], ['PROGRAMA ESPECIAL', 'PREGRADO', 'POSTGRADO'])) {
            $es_valido = false;
            $linea[6] = 'x'; // Valor por defecto para grado
        }

        // 7. Modalidad: Debe ser "presencial" o "online"
        if (!in_array(trim($linea[7]), ['Presencial', 'OnLine'])) {
            $es_valido = false;
            $linea[7] = 'Presencial'; // Valor por defecto para modalidad
        }

        // 8. Inicio de Vigencia: Formato de fecha dd/mm/aaaa
        if (!preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $linea[8])) {
            $es_valido = false;
            $linea[8] = 'x'; // Valor por defecto para fecha de vigencia
        }

        // Si la línea tiene errores, la guardamos en los errores y corregimos lo necesario
        if (!$es_valido) {
            $array_errores[] = $linea;
            if (empty(implode('', $linea))) {
                // Si la línea está completamente vacía, se guarda en el array de invalido
                $array_invalido[] = $linea;
                continue; // Omitir el procesamiento de esta línea
            }

            // Agregar la línea corregida a los corregidos
            $array_corregidos[] = $linea;
            $array_validos[] = $linea;
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
                $es_valido = false; // Marcamos como no válido
                $linea[3] = 'x'; // Corrección: asignar "x" si el nivel no es válido
            }
        }

        // Verificar prerequisitos
        if (isset($linea[4])) {
            $prerequisitos = trim($linea[4]); // Accedemos a los prerequisitos
            
            // Expresión regular para validar los prerequisitos
            $valid_prereq_regex = '/^(ingreso|Ingreso|egreso|B|L|B v L|\d{4}|)$/'; // Permite los valores válidos y un número de 4 dígitos
            
            // Validación del prerequisito
            if (!preg_match($valid_prereq_regex, $prerequisitos)) {
                $es_valido = false; // Marcamos como no válido
            }
        }

        // Si la línea tiene errores, la guardamos en los errores y corregimos lo necesario
        if (!$es_valido) {
            // Agregar la línea a errores
            $array_errores[] = $linea; 
            $array_corregidos[] = $linea;
            $array_validos[] = $linea;
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

function validar_y_corregir_datos_notas($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos){
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];
    
    foreach ($array_datos as $linea) {
        $es_valido = true;

        // Plan debe ser dos letras seguidas de uno o más dígitos (ej. 'AA1', 'BB12', etc.)
        $linea[0] = trim($linea[0]);
        if (isset($linea[0]) && !empty($linea[0]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[0])) {
            $es_valido = false;
            $linea[0] = 'x'; // Valor por defecto para plan
        }

        // Validar Cohorte (en formato YYYY-01 o YYYY-02)
        $linea[2] = trim($linea[2]);
        if (isset($linea[2]) && !empty($linea[2]) && !preg_match('/^\d{4}-(01|02)$/', $linea[2])) {
            $es_valido = false;
            $linea[2] = 'x'; // Valor por defecto para cohorte
        }

        // 5. Sede: Debe ser "Uagadou", "Beauxbaton" o "Hogwarts"
        if (!in_array(trim($linea[3]), ['Uagadou', 'Beauxbaton', 'Hogwarts'])) {
            $es_valido = false;
            $linea[3] = 'Hogwarts'; // Valor por defecto para sede
        }

        // Verificar el RUT
        $linea[4] = trim($linea[4]);
        if (isset($linea[4])) {
            $rut = trim($linea[4]);
            if (empty($rut) || !ctype_digit($rut) || !(strlen($rut) == 7 || strlen($rut) == 8)) {
                $es_valido = false;
                $linea[4] = 'x'; // Valor por defecto para RUT
            }}
        
        // Validar DV (un solo dígito o 'K')
        $linea[5] = trim($linea[5]);
        if (isset($linea[5]) && !empty($linea[5]) && !preg_match('/^[0-9K]$/', $linea[5])) {
            $es_valido = false;
            $linea[5] = 'x'; // Valor por defecto para DV
        }

        // Validar y corregir Nombre (solo letras y espacios, no nulo)
        $linea[6] = trim($linea[6]);
        if (!isset($linea[6]) || empty($linea[6]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[6])) {
            $es_valido = false;
            $linea[6] = 'x'; // Valor por defecto para Nombre
        }

        // Validar y corregir Apellido Paterno (solo letras y espacios, no nulo)
        $linea[7] = trim($linea[7]);
        if (!isset($linea[7]) || empty($linea[7]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', $linea[7])) {
            $es_valido = false;
            $linea[7] = 'x'; // Valor por defecto para Apellido Paterno
        }

        // Validar y corregir Apellido materno (solo letras y espacios, no nulo)
        $linea[8] = trim($linea[8]);
        if (!isset($linea[8]) || empty($linea[8]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[8])) {
            $es_valido = false;
            $linea[8] = 'x'; // Valor por defecto para Apellido Materno
        }
        // Validar Número de Estudiante (6 dígitos)
        $linea[9] = trim($linea[9]);
        if (isset($linea[9]) && !empty($linea[9]) && !preg_match('/^\d{6}$/', $linea[9])) {
            $es_valido = false;
            $linea[9] = 'x'; // Valor por defecto para Número de Estudiante
        }

        // Validar periodo_asigantura (en formato YYYY-01 o YYYY-02)
        $linea[10] = trim($linea[10]);
        if (isset($linea[10]) && !empty($linea[10]) && !preg_match('/^\d{4}-(01|02)$/', $linea[10])) {
            $es_valido = false;
            $linea[10] = 'x'; // Valor por defecto para periodo_asignatura
        }

        // Asignatura_id debe ser dos letras seguidas de uno o más dígitos (ej. 'AA1', 'BB123', etc.)
        $linea[11] = trim($linea[11]);
        if (isset($linea[11]) && !empty($linea[11]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[11])) {
            $es_valido = false;
            $linea[11] = 'x'; // Valor por defecto para asignatura_id
        }
        
        // convocatria debe ser un mes
        $linea[13] = preg_replace('/\s+/', '', strtolower($linea[13]));
        if (!in_array($linea[13], ['mar', 'jul', 'ago', 'dic'])) {
            $es_valido = false;
            $linea[13] = 'mar'; // Valor por defecto para convocatoria
        }

        # calificaicon
        $linea[14] = preg_replace('/\s+/', '', strtoupper($linea[14]));
        $calificaciones_validas = ['SO', 'MB', 'B', 'SU', 'I', 'M', 'MM', 'NP', 'EX', 'A', 'R'];
        if (!in_array($linea[14], $calificaciones_validas)) {
            $es_valido = false;
            $linea[14] = NULL; // Valor por defecto para calificación
        }

        # nota
        $nota = str_replace(',', '.', trim($linea[15])); 
        
        if (isset($linea[15])) {
            // Intentar convertir a decimal
            if (is_numeric($nota)) {
                $linea[15] = floatval($nota);  // Convertir el valor a tipo float si es numérico
            }
        }

        if (!isset($linea[15])|| !is_numeric($nota) || floatval($nota) < 1 || floatval($nota) > 7 || is_null($nota) || $nota == '') {
            $es_valido = false;
            $linea[15] = NULL; // Valor por defecto para nota
        }

        // Si no es válido, guardarlo en el archivo de errores
        if (!$es_valido) {
            $array_errores[] = $linea;
            
            // Agregar la línea corregida al archivo corregido
            $array_validos[] = $linea;

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

//Función para guardar los datos en un archivo CSV
function guardar_csv($array_datos, $nombre_archivo) {
    $archivo = fopen($nombre_archivo, 'w');
    foreach ($array_datos as $linea) {
        fputcsv($archivo, $linea, ';');
    }
    fclose($archivo);
}

## funcion para hacer los array
function crear_array_administrativos($array){
    $array_limpio = [];

    foreach ($array as $linea) {
        $rut = $linea[0];            
        $nombre = $linea[1];             
        $apellido = $linea[2];       
        $email_institucional = $linea[5];  
        $dedicacion = $linea[6]; 
        $contrato = $linea[7];
        $grado = $linea[12]; 
        $jerarquia = $linea[13];      
        $cargo = $linea[14];  
        $estamento = $linea[15];    

        $array_limpio[] = [
            $rut, 
            $nombre, 
            $apellido,
            $email_institucional,
            $dedicacion,
            $contrato,
            $grado,
            $jerarquia,
            $cargo,
            $estamento
    ];     
    }

    return $array_limpio;

}

function crear_array_asinaturas($array){
    $array_limpio = [];

    foreach ($array as $linea) {
        $plan = $linea[0];            
        $codigo = $linea[1];             
        $asignatura = $linea[2];       
        $nivel = $linea[3];  

        $array_limpio[] = [
            $plan, 
            $codigo, 
            $asignatura,
            $nivel,
            
    ];     
    }

    return $array_limpio;
}

function crear_array_estudiantes($array){
    return $array;
}

function crear_array_planes($array){
    return $array;
}

function crear_array_prerrequisitos($array){
    return $array;
}

function crear_array_historial($array){
    $array_limpio = [];
    
    foreach ($array as $linea) {
        $rut = $linea[4];    
        $codigo_plan = $linea[0];        
        $num_alumno = $linea[9];             
        $periodo = $linea[10];       
        $codigo_asignatura = $linea[11];        
        $convocatoria = $linea[13];  
        $calificacion = $linea[14]; 
        $nota = $linea[15];


        $array_limpio[] = [
            $rut, 
            $codigo_plan, 
            $num_alumno,
            $periodo,
            $codigo_asignatura,
            $convocatoria,
            $calificacion,
            $nota,
    ];
    }

    return $array_limpio;

}

?>