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
                $linea[20] = 'x';
            } 
        }

        // Verificar id_depto
        if (isset($linea[3])) {
            $id_depto = trim($linea[3]);
            if (empty($id_depto) || !ctype_digit($id_depto) || strlen($id_depto) > 5) {
                $linea[3] = '0';
            } elseif (is_numeric($id_depto)) {
                $linea[3] = (int) $id_depto;
            }
        }

        // Verificar vacantes
        if (isset($linea[10])) {
            $vacantes = trim($linea[10]);
            if (empty($vacantes) || !ctype_digit($vacantes) || strlen($vacantes) > 3) {
                $linea[10] = '0';
            } elseif (is_numeric($vacantes)) {
                $linea[10] = (int) $vacantes;
            }
        }

        // Verificar inscritos
        if (isset($linea[11])) {
            $inscritos = trim($linea[11]);
            if (empty($inscritos) || !ctype_digit($inscritos) || strlen($inscritos) > 3) {
                $linea[11] = '0';
            } elseif (is_numeric($inscritos)) {
                $linea[11] = (int) $inscritos;
            }
        }

        // Verificar sección
        if (isset($linea[7])) {
            $seccion = trim($linea[7]);
            if (empty($seccion) || !ctype_digit($seccion) || strlen($seccion) > 3) {
                $linea[7] = '0';
            } elseif (is_numeric($seccion)) {
                $linea[7] = (int) $seccion;
            }
        }
        
        // Si no es válido, guardarlo en el archivo de errores
        if (!$es_valido) {
            $array_errores[] = $linea;
            $linea_corregida = $linea;
            if (empty(implode('', $linea))) {
                continue; // Omitir esta línea
            }

            // Verificar cada celda de la línea para comprobar si está vacía
            foreach ($linea as $indice => $celda) {
                if (!isset($celda) || trim($celda) === '') {
                    $linea[$indice] = 'x';  // Marcar la línea como inválida
                    $linea_corregida = $linea;
                }
            }

            // Agregar la línea corregida al archivo corregido
            $array_corregidos[] = $linea_corregida;
            $array_validos[] = $linea_corregida;

        } else {
            // Si es válido, agregar a la lista de válidos
            $array_validos[] = $linea;
        }
    }

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
            $periodo,
            $sede,
            $factultad,
            $codigo_depto,
            $departamento, 
            $codigo_asignatura,
            $nombre_asignatura,
            $seccion,
            $duracion,
            $vacantes,
            $inscritos,
            $dia,
            $hora_inicio,
            $hora_fin,
            $fecha_inicio,
            $fecha_fin, 
            $lugar,
            $edificio,
            $profesor_principal,
            $profesor_run,
        ];
    }

    return $array_limpio;
}

?>