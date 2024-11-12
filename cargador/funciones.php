<?php
ini_set('memory_limit', '1G');  

// $base_path = __DIR__ . '/../data/';

// // Construir las rutas de los archivos de manera universal
// $ruta_estudiantes = $base_path . "estudiantes.csv";
// $ruta_asignaturas = $base_path . "asignaturas.csv";
// $ruta_planes = $base_path . "planes.csv";
// $ruta_prerrequisitos = $base_path . "prerequisitos.csv";
// $ruta_notas = $base_path . "notas.csv";
// $ruta_planeacion = $base_path . "planeacion.csv";
// $ruta_docentes = $base_path . "docentes_planificados.csv";


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
        if (isset($linea[0]) && !empty($linea[0]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[0])) {
            $es_valido = false;
        }

        // Asignatura_id debe ser dos letras seguidas de uno o más dígitos (ej. 'AA1', 'BB123', etc.)
        if (isset($linea[1]) && !empty($linea[1]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[1])) {
            $es_valido = false;
        }

        // Nivel puede ser 'B', 'L' o un número del 1 al 10
        if (isset($linea[3]) && !empty($linea[3]) && !preg_match('/^(B|L|[1-9]|10)$/', $linea[3])) {
            $es_valido = false;
        }

        // Prerequisito debe ser 'B' o 'L'
        if (isset($linea[4]) && !empty($linea[4]) && !preg_match('/^[BL]$/', $linea[4])) {
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

function validar_y_corregir_datos_planes($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos) {
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];

    foreach ($array_datos as $linea) {
        $es_valido = true;

        // 1. Código de Plan: Debe ser dos letras seguidas de dígitos
        if (!preg_match('/^[A-Za-z]{2}\d+$/', $linea[0])) {
            $es_valido = false;
        }

        // 2. Facultad: Solo puede ser "Facultad de Magia y Hechicería" o "Ministerio de magia"
        if (!in_array(trim($linea[1]), ['Facultad de Magia y Hechicería', 'Ministerio de magia'])) {
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
            $es_valido = false;
        }

        // 5. Sede: Debe ser "Uagadou", "Beauxbaton" o "Hogwarts"
        if (!in_array(trim($linea[5]), ['Uagadou', 'Beauxbaton', 'Hogwarts'])) {
            $es_valido = false;
        }

        // 6. Grado: Debe ser uno de los valores válidos, convertido a mayúsculas
        $linea[6] = strtoupper(trim($linea[6]));
        if (!in_array($linea[6], ['PROGRAMA ESPECIAL', 'PREGRADO', 'POSTGRADO'])) {
            $es_valido = false;
        }

        // 7. Modalidad: Debe ser "presencial" o "online"
        if (!in_array(trim($linea[7]), ['Presencial', 'OnLine'])) {
            $es_valido = false;
        }

        // 8. Inicio de Vigencia: Formato de fecha dd/mm/aaaa
        if (!preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $linea[8])) {
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

            if (isset($linea[3])) {
                $linea[3] = 'x'; // Corrección: asignar "x" si el nivel no es válido
            }

            if (isset($linea[4]) && $linea[4] === "por fijar") {
                continue; // Saltar a la siguiente iteración
            }
            $array_corregidos[] = $linea;
            $array_validos[] = $linea;
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
            $es_valido = false;
        }

        // Validar Cohorte (en formato YYYY-01 o YYYY-02)
        if (isset($linea[2]) && !empty($linea[2]) && !preg_match('/^\d{4}-(01|02)$/', $linea[2])) {
            $es_valido = false;
        }

        // 5. Sede: Debe ser "Uagadou", "Beauxbaton" o "Hogwarts"
        if (!in_array(trim($linea[3]), ['Uagadou', 'Beauxbaton', 'Hogwarts'])) {
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
            $es_valido = false;
        }

        // Validar y corregir Nombre (solo letras y espacios, no nulo)
        if (!isset($linea[6]) || empty($linea[6]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[6])) {
            $es_valido = false;
        }

        // Validar y corregir Apellido Paterno (solo letras y espacios, no nulo)
        if (!isset($linea[7]) || empty($linea[7]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', $linea[7])) {
            $es_valido = false;
        }

        // Validar y corregir Apellido materno (solo letras y espacios, no nulo)
        if (!isset($linea[8]) || empty($linea[8]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[8])) {
            $es_valido = false;
        }
        // Validar Número de Estudiante (6 dígitos)
         if (isset($linea[9]) && !empty($linea[9]) && !preg_match('/^\d{6}$/', $linea[9])) {
            $es_valido = false;
        }

        // Validar periodo_asigantura (en formato YYYY-01 o YYYY-02)
        if (isset($linea[10]) && !empty($linea[10]) && !preg_match('/^\d{4}-(01|02)$/', $linea[10])) {
            $es_valido = false;
        }

        // Asignatura_id debe ser dos letras seguidas de uno o más dígitos (ej. 'AA1', 'BB123', etc.)
        if (isset($linea[11]) && !empty($linea[11]) && !preg_match('/^[A-Za-z]{2}\d+$/', $linea[11])) {
            $es_valido = false;
        }
        
        // convocatria debe ser un mes
        $linea[13] = preg_replace('/\s+/', '', strtolower($linea[13]));
        if (!in_array($linea[13], ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'])) {
            $es_valido = false;
        }

        # calificaicon
        $linea[14] = preg_replace('/\s+/', '', strtoupper($linea[14]));
        $calificaciones_validas = ['SO', 'MB', 'B', 'SU', 'I', 'M', 'MM', 'NP', 'EX', 'A', 'R'];
        if (!in_array($linea[14], $calificaciones_validas)) {
            $es_valido = false;
        }

        # nota
        $nota = trim($linea[15]); 
        
        if (isset($linea[15])) {
            // Intentar convertir a decimal
            if (is_numeric($nota)) {
                $linea[15] = floatval($nota);  // Convertir el valor a tipo float si es numérico
            }
        }

        if (!isset($linea[15])|| !is_numeric($nota) || floatval($nota) < 1 || floatval($nota) > 7 || is_null($nota) || $nota == '') {
            $es_valido = false;
        }

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
                $linea_corregida[3] = 'Hogwarts'; // Valor por defecto para sede
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
            // Verificar si el valor es una cadena que contiene una coma como separador decimal
            $valor = $linea[15];

            if (is_null($valor) || $valor == '') {
                $linea_corregida[15] = null;  // Mantener el valor nulo
            }

            if (is_string($valor) && strpos($valor, ',') !== false) {
                // Reemplazar la coma con un punto
                $$linea_corregida[15] = str_replace(',', '.', $valor);
            }
            
            // Intentar convertir a decimal
            if (is_numeric($valor)) {
                $linea_corregida[15] = floatval($valor);  // Convertir el valor a tipo float si es numérico
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

// function validar_y_corregir_datos_planeacion($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos){
//     $array_validos = [];
//     $array_errores = [];
//     $array_corregidos = [];
    
//     foreach ($array_datos as $linea) {
//         $es_valido = true;

//         // Verificar cada celda de la línea para comprobar si está vacía
//         foreach ($linea as $indice => $celda) {
//             if (!isset($celda) || trim($celda) === '') {
//                 $es_valido = false;  // Marcar la línea como inválida
//             }
//         }

//         // Verificar el RUT
//         if (isset($linea[20])) {
//             $rut = trim($linea[20]);
//             if (empty($rut) || !ctype_digit($rut) || strlen($rut) > 8) {
//                 $es_valido = false;
//             } 
//         } else {
//             $es_valido = false;
//         }

//         // Validar y corregir Nombre (solo letras y espacios, no nulo)
//         if (!isset($linea[23]) || empty($linea[23]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[23])) {
//             $es_valido = false;
//         }
//         // Validar y corregir Nombre (solo letras y espacios, no nulo)
//         if (!isset($linea[22]) || empty($linea[22]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $linea[22])) {
//             $es_valido = false;
//         }
        
//         // Si no es válido, guardarlo en el archivo de errores
//         if (!$es_valido) {
//             $array_errores[] = $linea;
//             $linea_corregida = $linea;
//             if (empty(implode('', $linea))) {
//                 // Si la línea está completamente vacía, se guarda en el array de invalido
//                 $array_invalido[] = $linea;
//                 continue; // Omitir el procesamiento de esta línea
//             }

//             // Corregir RUT
//             $rut = trim($linea[20]);
//             if (!isset($rut) || !ctype_digit($rut) || strlen($rut) > 8) {
//                 $linea_corregida[20] = 'x';
//             } else {
//                 $linea_corregida[20] = trim($rut);
//             }

//             // Verificar cada celda de la línea para comprobar si está vacía
//             foreach ($linea as $indice => $celda) {
//                 if (!isset($celda) || trim($celda) === '') {
//                     $linea[$indice] = 'x';  // Marcar la línea como inválida
//                     $linea_corregida = $linea;
//                 }
//             }

//             // Corregir atributos inválidos si es posible
            

//             // Corregir nombres y apellidos
//             if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/u', trim($linea[23]))) {
//                 $linea_corregida[21] = 'x';
//             } else {
//                 $linea_corregida[21] = trim($linea[21]);
//             }
//             // Corregir nombres y apellidos
//             if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/u', trim($linea[22]))) {
//                 $linea_corregida[22] = 'x';
//             } else {
//                 $linea_corregida[22] = trim($linea[22]);
//             }

//             // Agregar la línea corregida al archivo corregido
//             $array_corregidos[] = $linea_corregida;
//             $array_validos[] = $linea_corregida;

//         } else {
//             // Si es válido, agregar a la lista de válidos
//             $array_validos[] = $linea;
//         }
//     }

//     // Guardar los errores y los corregidos en archivos CSV
//     guardar_csv($array_errores, $nombre_archivo_errores);
//     guardar_csv($array_corregidos, $nombre_archivo_corregidos);

//     // Retornar los datos válidos
//     return $array_validos;
// }

function validar_y_corregir_datos_docentes($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos){
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];
    $array_academicos = [];
    $array_administrativos = [];

    foreach ($array_datos as $linea) {
        $es_valido = true;

        // Verificar el RUT
        if (isset($linea[0])) {
            $rut = trim($linea[0]);
            if (empty($rut) || !ctype_digit($rut) || !(strlen($rut) <= 8)) {
                $es_valido = false;
            } 
        } else {
            $es_valido = false;
        }

        // Limpiar nombres y apellidos (solo letras y espacios)
        if (!isset($linea[1]) || empty($linea[1]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $linea[1])) {
            $es_valido = false;
           
        }
        if (!isset($linea[2]) || empty($linea[2]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/u', $linea[2])) {
            $es_valido = false;
            
        }
        
        # grado academico
        if (!isset($linea[12]) || empty($linea[12]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/u', $linea[12])) {
            $es_valido = false;
            
        }
        
        // Limpiar correo personal (eliminar espacios y validar formato básico)
        $linea[4] = str_replace(' ', '', $linea[4]);
        if (!isset($linea[4]) || empty($linea[4]) || !filter_var($linea[4], FILTER_VALIDATE_EMAIL)) {
            $es_valido = false;
        }

        // Verificar y limpiar correo institucional (debe terminar en @lamejor.cl)
        $linea[5] = str_replace(' ', '', $linea[5]);
        if (!isset($linea[5]) || empty($linea[5]) || !preg_match('/@lamejor\.(cl|com)$/', $linea[5])) {
            $es_valido = false;
        }

        // Limpiar teléfono (solo 9 dígitos)
        if (!isset($linea[3]) || empty($linea[3]) || !preg_match('/^\d{9}$/', $linea[3])) {
            $es_valido = false;
        }

        // Dedicación: Solo números
        if (!isset($linea[6]) || empty($linea[6]) || !preg_match('/^\d+$/', $linea[6]) || $linea[6] > 40 || $linea[6] < 0) {
            $es_valido = false;
        }

        // Grado académico: Solo letras y espacios
        if (!isset($linea[12]) || empty($linea[12]) || !preg_match('/^[a-zA-Z\s]*$/', $linea[12])) {
            $es_valido = false;
        }

        // Validar el estamento (debe contener las palabras "administrativo", "academico"")
        if (!isset($linea[15]) || empty($linea[15]) || !preg_match('/(administrativo|academico|académico)/i', $linea[15])) {
            $es_valido = false;
        }

        // Si no es válido, guardarlo en el archivo de errores
        if (!$es_valido) {
            $array_errores[] = $linea;
            if (empty(implode('', $linea))) {
                // Si la línea está completamente vacía, se guarda en el array de invalido
                $array_invalido[] = $linea;
                continue; // Omitir el procesamiento de esta línea
            }
            // Verificar cada celda de la línea para comprobar si está vacía
            foreach ($linea as $indice => $celda) {
                if (!isset($celda) || trim($celda) === '') {
                    $linea[$indice] = 'x';  // Marcar la línea como inválida
                }
            }

            // Corregir atributos inválidos si es posible
            $linea_corregida = $linea;
            // Limpiar nombres y apellidos (solo letras y espacios)
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $linea[1])) {
                $linea_corregida[1] = 'x';
            }
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/u', $linea[2])) {
                $linea_corregida[2] = 'x';
            }
            
            // Limpiar correo personal (eliminar espacios y validar formato básico)
            $linea[4] = str_replace(' ', '', $linea[4]);
            if ($linea[4] !== '' && !filter_var($linea[4], FILTER_VALIDATE_EMAIL)) {
                $linea_corregida[4] = 'x';
            }

            // Verificar y limpiar correo institucional (debe terminar en @lamejor.cl)
            $linea[5] = str_replace(' ', '', $linea[5]);
            if ($linea[5] !== '' && !preg_match('/@lamejor\.(cl|com)$/', $linea[5])) {
                $linea_corregida[5] = 'x';
            }

            if ($linea[6] > 40) {
                $linea_corregida[6] = 40;
                
            } elseif ($linea[6] < 0) {
                $linea_corregida[6] = 0;
            }

            // Limpiar teléfono (solo 9 dígitos)
            if ($linea[7] !== '' && !preg_match('/^\d{9}$/', $linea[7])) {
                $linea_corregida[3] = 'x';
            }

            // Dedicación: Solo números
            if ($linea[6] !== '' && !preg_match('/^\d+$/', $linea[6])) {
                $linea_corregida[6] = 0;
            }

            # grado academico
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/u', $linea[12])) {
                $linea_corregida[12] = "x";
                
            }

            # ponemos academico o administrrativo si tiene jerarquia o cargo respectivamente
            if ($linea[13] !== 'x') {
                $linea_corregida[15] = "academico";
            }

            if ($linea[14] !== 'x') {
                $linea_corregida[15] = "administrativo";
            }

            if ($linea[14] !== 'x' && $linea[13] !== 'x'){
                $linea_corregida[15] = "academico y administrativo";

            }

            // Asegurarse de no agregar la línea corregida si el RUT es "x"
            if ($linea[0] !== 'x') {
                $array_corregidos[] = $linea_corregida;
                $array_validos[] = $linea_corregida;
                if (preg_match('/\b(academico|académico)\b/i', $linea_corregida[15])) {
                    $array_academicos[] = $linea_corregida;
                    echo "Xenofilus";
                }
                // Verificamos si la posición 15 contiene "administrativo"
                if (preg_match('/\b(administrativo)\b/i', $linea_corregida[15])) {
                    $array_administrativos[] = $linea_corregida;
                }

                if ($linea[14] !== 'x' && $linea[13] !== 'x'){
                    $array_administrativos[] = $linea_corregida;
                    $array_academicos[] = $linea_corregida;
    
                }

            }

        } else {

            $linea_corregida = $linea;

            // Si es válido, agregar a la lista de válidos

            # ponemos academico o administrrativo si tiene jerarquia o cargo respectivamente
            if (trim($linea[13]) !== '') {
                $linea_corregida[15] = "academico";
            }

            if (trim($linea[14]) !== '') {
                $linea_corregida[15] = "administrativo";
            }

            if (trim($linea[14]) !== '' && trim($linea[13]) !== ''){
                $linea_corregida[15] = "academico y administrativo";
            }

            if (preg_match('/\b(academico|académico)\b/i', $linea_corregida[15])) {
                $array_academicos[] = $linea_corregida;
            }
            // Verificamos si la posición 15 contiene "administrativo"
            if (preg_match('/\b(administrativo)\b/i', $linea_corregida[15])) {
                $array_administrativos[] = $linea_corregida;
            }

            $array_validos[] = $linea_corregida;

            // Clasificar en académicos y administrativos
            
        }
    }

    // Guardar los errores y los corregidos en archivos CSV
    guardar_csv($array_errores, $nombre_archivo_errores);
    guardar_csv($array_corregidos, $nombre_archivo_corregidos);

    // Retornar los datos válidos
    return [
        'validos' => $array_validos,
        'academicos' => $array_academicos,
        'administrativos' => $array_administrativos
    ];
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
function crear_array_academicos($array) {
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

// function crear_array_oferta($array) {
//     $array_limpio = [];

//     foreach ($array as $linea) {
//         // Extraer los valores de las columnas que necesitas
//         $periodo = $linea[0];    
//         $sede = $linea[1];        
//         $factultad = $linea[2];             
//         $codigo_depto = $linea[3];       
//         $codigo_asignatura = $linea[5];  
//         $nombre_asignatura = $linea[6];      
//         $seccion = $linea[7];  
//         $duracion = $linea[8]; 
//         $vacantes = $linea[10];
//         $inscritos = $linea[11]; 
//         $dia = $linea[12];
//         $hora_inicio = $linea[13]; 
//         $hora_fin = $linea[14];
//         $fecha_inicio = $linea[15]; 
//         $fecha_fin = $linea[16];
//         $edificio = $linea[18];
//         $profe_run = $linea[20];
//         $profe_nombre = $linea[21];
//         $profe_apellido = $linea[22];
//         $jerarquia = $linea[24];

//         // Añadir una nueva línea al array limpio con los valores requeridos
//         $array_limpio[] = [
//             $periodo,            // 0
//             $sede,               // 1
//             $factultad,          // 2
//             $codigo_depto,       // 3
//             $codigo_asignatura,  // 6
//             $nombre_asignatura,  // 7
//             $seccion,            // 8
//             $duracion,           // 9
//             $vacantes,           // 11
//             $inscritos,          // 12
//             $dia,                // 12
//             $hora_inicio,        // 13
//             $hora_fin,           // 14
//             $fecha_inicio,       // 15
//             $fecha_fin,          // 16
//             $edificio,           // 18
//             $profe_run,          // 20
//             $profe_nombre,       // 21
//             $profe_apellido,     // 22
//             $jerarquia           // 24
//         ];
//     }

//     return $array_limpio;
// }


// # trabajo los datos de estudiantes, funcinoa ok.
// $array_datos_1 = abrir_archivo($ruta_estudiantes);
// echo "cantidad de datos en array original", count($array_datos_1);
// echo "\n";

// $estudiantes_validos = validar_y_corregir_datos_estudiante($array_datos_1, 6, "estudiantes_invalidos.csv", "estudiantes_corregidos.csv");
// #imprimir_bonito($estudiantes_validos);
// echo "cantidad de datos en array limpio", count($estudiantes_validos);


// $array_datos_2 = abrir_archivo($ruta_asignaturas);
// echo "cantidad de datos en array origina asinaturas", count($array_datos_2);
// echo "\n";

// $asignaturas_validos = validar_y_corregir_datos_asignaturas($array_datos_2, "asignaturas_invalidos.csv", "asignaturas_corregidos.csv");
// #imprimir_bonito($array_datos_1);
// echo "cantidad de datos en array limpio asinaturas", count($asignaturas_validos);

// $array_datos_3 = abrir_archivo($ruta_planes);
// echo "cantidad de datos en array original PLANES", count($array_datos_3);
// echo "\n";

// $planes_validos = validar_y_corregir_datos_planes($array_datos_3, "planes_invalidos.csv", "planes_corregidos.csv");
// #imprimir_bonito($array_datos_1);
// echo "cantidad de datos en array limpio PLANES", count($planes_validos);

// $array_datos_4 = abrir_archivo($ruta_prerrequisitos);
// echo "cantidad de datos en array original", count($array_datos_4);
// echo "\n";

// $prerrequisitos_validos = validar_y_corregir_datos_prerrequisitos($array_datos_4, "prerrequisitos_invalidos.csv", "prerrequisitos_corregidos.csv");
// #imprimir_bonito($prerrequisitos_validos);
// echo "cantidad de datos en array limpio", count($prerrequisitos_validos);

// $array_datos_5 = abrir_archivo($ruta_notas);
// echo "\ncantidad de datos en array original NOTAS ", count($array_datos_5);
// echo "\n";

// $notas_validos = validar_y_corregir_datos_notas($array_datos_5, "notas_invalidos.csv", "notas_corregidos.csv");
// #imprimir_bonito($notas_validos);
// echo "\ncantidad de datos en array limpio NOTAS ", count($notas_validos);

// $historial = crear_array_historial($notas_validos);
// echo "\ncantidad de datos en array limpio HISTORIAL ", count($historial);


// $array_datos_6 = abrir_archivo($ruta_planeacion);
// echo "\ncantidad de datos en array original", count($array_datos_6);
// echo "\n";

// $planeacion_validos = validar_y_corregir_datos_planeacion($array_datos_6, "planeacion_invalidos.csv", "planeacion_corregidos.csv");
// // imprimir_bonito($planeacion_validos);
// // echo "cantidad de datos en array limpio", count($planeacion_validos);

// $oferta = crear_array_oferta($planeacion_validos);
// imprimir_bonito($oferta);
// echo "cantidad de datos en array limpio", count($oferta);


// $array_datos_7 = abrir_archivo($ruta_docentes);
// echo "cantidad de datos en array original", count($array_datos_7);
// echo "\n";

// $docentes_validos = validar_y_corregir_datos_docentes($array_datos_7, "docentes_invalidos.csv", "docentes_corregidos.csv");

// imprimir_bonito($docentes_validos["administrativos"]);
// echo "cantidad de datos en array limpio", count($docentes_validos["administrativos"]);

// $array_administrativo = crear_array_administrativos($corregidos_docente["administrativos"]);

?>