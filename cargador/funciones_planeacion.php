<?php

function validar_y_corregir_datos_planeacion($array_datos, $nombre_archivo_errores, $nombre_archivo_corregidos){
    $array_validos = [];
    $array_errores = [];
    $array_corregidos = [];
    
    foreach ($array_datos as $linea) {
        $es_valido = true;

        // Verificar cada celda de la línea para comprobar si está vacía
        foreach ($linea as $indice => $celda) {
            if (!isset($celda) || trim($celda) === '') {
                $es_valido = false;  // Marcar la línea como inválida
            }
        }

        // Verificar el RUT
        if (isset($linea[20])) {
            $rut = trim($linea[20]);
            if (empty($rut) || !ctype_digit($rut) || strlen($rut) > 9) {
                $es_valido = false;
            } 
        } else {
            $es_valido = false;
        }
        
        // Si no es válido, guardarlo en el archivo de errores
        if (!$es_valido) {
            $array_errores[] = $linea;
            $linea_corregida = $linea;
            if (empty(implode('', $linea))) {
                // Si la línea está completamente vacía, se guarda en el array de invalido
                $array_invalido[] = $linea;
                continue; // Omitir el procesamiento de esta línea
            }

            // Corregir RUT
            $rut = trim($linea[20]);
            if (!isset($rut) || !ctype_digit($rut) || strlen($rut) > 9) {
                $linea_corregida[20] = 'x';
            } else {
                $linea_corregida[20] = trim($rut);
            }

            // Verificar cada celda de la línea para comprobar si está vacía
            foreach ($linea as $indice => $celda) {
                if (!isset($celda) || trim($celda) === '') {
                    $linea[$indice] = 'x';  // Marcar la línea como inválida
                    $linea_corregida = $linea;
                }
            }

            // Corregir atributos inválidos si es posible
            

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

function crear_array_planeacion($array) {
    $array_limpio = [];

    foreach ($array as $linea) {
        // Extraer los valores de las columnas que necesitas
        $periodo = $linea[0];    
        $sede = $linea[1];        
        $factultad = $linea[2];             
        $codigo_depto = $linea[3];     
        $departamento = $linea[4];  
        $codigo_asignatura = $linea[5];  
        $nombre_asignatura = $linea[6];      
        $seccion = $linea[7];  
        $duracion = $linea[8]; 
        $jornada = $linea[9];
        $vacantes = $linea[10];
        $inscritos = $linea[11]; 
        $dia = $linea[12];
        $hora_inicio = $linea[13]; 
        $hora_fin = $linea[14];
        $fecha_inicio = $linea[15]; 
        $fecha_fin = $linea[16];
        $lugar = $linea[17];
        $edificio = $linea[18];
        $profesor_principal = $linea[19];
        $profesor_run = $linea[20];

        // Añadir una nueva línea al array limpio con los valores requeridos
        $array_limpio[] = [
            $periodo,            // 0
            $sede,               // 1
            $factultad,          // 2
            $codigo_depto,       // 3
            $departamento,       // 4
            $codigo_asignatura,  // 5
            $nombre_asignatura,  // 6
            $seccion,            // 7
            $duracion,           // 8
            $vacantes,           // 10
            $inscritos,          // 11
            $dia,                // 12
            $hora_inicio,        // 13
            $hora_fin,           // 14
            $fecha_inicio,       // 15
            $fecha_fin,          // 16
            $lugar,              // 17
            $edificio,           // 18
            $profesor_principal, // 19
            $profesor_run,          // 20
        ];
    }

    return $array_limpio;
}

?>