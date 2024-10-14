<?php
    include('../config/conexion.php');
    require('parametros_tablas.php');
    require('utils.php');
    require('funciones.php');

    try {
        echo "INICIO DE INSERCIÓN DE DATOS\n";
        foreach ($path_tablas as $tabla => $path) {
            $file = fopen($path, 'r');

            if ($file) {
                $header = fgetcsv($file); // Saltar la primera línea
                while (($data = fgetcsv($file, 0, ',')) !== false) { 
                    // Verificar restricciones antes de insertar
                    for ($i = 0; $i < count($data); $i++) {
                        if ($data[$i] == ''){ 
                            $data[$i] = Null; // Convertir campos vacíos en NULL, para evitar insertar datos vacíos
                        }
                    }
                    // Realizar toda corrección necesaria antes de insertar
                    insertar_en_tabla($db, $tabla, $data);
                }
                fclose($file);
            } else {
                echo "Error al abrir el archivo $path\n";
            }    
        } 
    } catch (Exception $e) {
        echo "Error al cargar datos: " . $e->getMessage();
    }

    try {
        echo "INICIO DE INSERCIÓN DE DATOS ESTUDIANTES\n";
        $array_estudiantes = abrir_archivo($path_tablas['estudiantes']);
        $corregidos_est = validar_y_corregir_datos_estudiante($array_estudiantes, 6, 'estudiantes_invalidos.csv', 'estudiantes_corregidos.csv');
        foreach ($corregidos_est as $fila) {
            insertar_en_tabla($db, 'estudiantes', $fila);
    }
    } catch (Exception $e) {
        echo "Error al cargar estudiantes: " . $e->getMessage();
    }

    try {
        echo "INICIO DE INSERCIÓN DE DATOS ASIGNATURAS\n";
        $array_asignaturas = abrir_archivo($path_tablas['asignaturas']);
        $corregidos_asig = validar_y_corregir_datos_asignaturas($array_asignaturas, 'asignaturas_invalidos.csv', 'asignaturas_corregidos.csv');

        foreach ($corregidos_asig as $fila) {
            insertar_en_tabla($db, 'asignaturas', $fila);
    }
    } catch (Exception $e) {
        echo "Error al cargar asignaturas: " . $e->getMessage();
    }

    try {
        echo "INICIO DE INSERCIÓN DE DATOS PLANES\n";
        $array_plan = abrir_archivo($path_tablas['planes']);
        $corregidos_plan = validar_y_corregir_datos_planes($array_plan, 'planes_invalidos.csv', 'planes_corregidos.csv');

        foreach ($corregidos_plan as $fila) {
            insertar_en_tabla($db, 'planes', $fila);
    }
    } catch (Exception $e) {
        echo "Error al cargar planes: " . $e->getMessage();
    }

    try {
        echo "INICIO DE INSERCIÓN DE DATOS PREREQUISITOS\n";
        $array_prereq = abrir_archivo($path_tablas['prerequisitos']);
        $corregidos_prereq = validar_y_corregir_datos_prerrequisitos($array_prereq, 'prerequisitos_invalidos.csv', 'prerequisitos_corregidos.csv');

        foreach ($corregidos_prereq as $fila) {
            insertar_en_tabla($db, 'prerequisitos', $fila);
    }
    } catch (Exception $e) {
        echo "Error al cargar prerequisitos: " . $e->getMessage();
    }

    try {
        echo "INICIO DE INSERCIÓN DE DATOS HISTORIAL\n";
        $array_notas = abrir_archivo($path_tablas['notas']);
        $corregidos_notas = validar_y_corregir_datos_notas($array_notas, 'notas_invalidos.csv', 'notas_corregidos.csv');

        foreach ($corregidos_notas as $fila) {
            insertar_en_tabla($db, 'historial', $fila);
    }
    } catch (Exception $e) {
        echo "Error al cargar historial: " . $e->getMessage();
    }

?> 