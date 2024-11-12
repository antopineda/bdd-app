<?php
    include('../config/conexion.php');
    require('parametros_tablas.php');
    require('utils.php');
    require('funciones.php');
    require('funciones_planeacion.php');
    require('funciones_personas.php');

    // try {
    //     echo "INICIO DE INSERCIÓN DE DATOS\n";
    //     foreach ($path_tablas as $tabla => $path) {
    //         $file = fopen($path, 'r');

    //         if ($file) {
    //             $header = fgetcsv($file); // Saltar la primera línea
    //             while (($data = fgetcsv($file, 0, ',')) !== false) { 
    //                 // Verificar restricciones antes de insertar
    //                 for ($i = 0; $i < count($data); $i++) {
    //                     if ($data[$i] == ''){ 
    //                         $data[$i] = Null; // Convertir campos vacíos en NULL, para evitar insertar datos vacíos
    //                     }
    //                 }
    //                 // Realizar toda corrección necesaria antes de insertar
    //                 insertar_en_tabla($db, $tabla, $data);
    //             }
    //             fclose($file);
    //         } else {
    //             echo "Error al abrir el archivo $path\n";
    //         }    
    //     } 
    // } catch (Exception $e) {
    //     echo "Error al cargar datos: " . $e->getMessage();
    // }
    try {
        echo "INICIO DE INSERCIÓN DE DATOS PLANEACIÓN\n";
        $array_planeacion = abrir_archivo($path_tablas['planeacion']);
        $corregidos_planeacion = validar_y_corregir_datos_planeacion($array_planeacion, 'planeacion_invalidos.csv', 'planeacion_corregidos.csv');
        $oferta = crear_array_planeacion($corregidos_planeacion);
        foreach ($oferta as $fila) {
            insertar_en_tabla($db, 'planeacion', $fila);
        
        # Liberar memoria
        unset($array_planeacion);
        unset($corregidos_planeacion);
        unset($oferta);
    }
    } catch (Exception $e) {
        echo "Error al cargar planeación: " . $e->getMessage();
    }

    try {
        echo "INICIO DE INSERCIÓN DE DATOS PERSONAS\n";
        $array_personas = abrir_archivo($path_tablas['estudiantes']);
        $estudiantes_personas = personas_from_estudiante($array_personas);
        foreach ($estudiantes_personas as $fila) {
            insertar_en_tabla($db, 'personas', $fila);
        }

        $profes_personas = personas_from_profesor($db_profes);
        foreach ($profes_personas as $fila) {
            insertar_en_tabla($db, 'personas', $fila);
        }
        
    } catch (Exception $e) {
        echo "Error al cargar personas: " . $e->getMessage();
    }
    
    // try {
    //     echo "INICIO DE INSERCIÓN DE DATOS USUARIOS\n";
        
    //     $file = fopen('../data/usuarios.csv', 'r');
        
    //     if ($file) {
    //         $header = fgetcsv($file); // Saltar la primera línea (encabezados)
    //         // Leer la única línea de datos
    //         $data = fgetcsv($file, 0, ',');
    //         $hashedPassword = password_hash($data[1], PASSWORD_DEFAULT);
    //         $data[1] = $hashedPassword;
    //         insertar_en_tabla($db, 'usuarios', $data);
    //         fclose($file);
    //     } else {
    //         echo "Error al abrir el archivo usuarios.csv\n";
    //     }
    // } catch (Exception $e) {
    //     echo "Error al cargar usuarios: " . $e->getMessage();
    // }

    // try {
    //     echo "INICIO DE INSERCIÓN DE DATOS ESTUDIANTES\n";
    //     $array_estudiantes = abrir_archivo($path_tablas['estudiantes']);
    //     $corregidos_est = validar_y_corregir_datos_estudiante($array_estudiantes, 6, 'estudiantes_invalidos.csv', 'estudiantes_corregidos.csv');
    //     $est = crear_array_estudiantes($corregidos_est);
    //     foreach ($est as $fila) {
    //         insertar_en_tabla($db, 'estudiantes', $fila);
    //     # Liberar memoria
    //     unset($array_estudiantes);
    //     unset($corregidos_est);
    //     unset($est);
    // }
    // } catch (Exception $e) {
    //     echo "Error al cargar estudiantes: " . $e->getMessage();
    // }


    // try {
    //     echo "INICIO DE INSERCIÓN DE DATOS ASIGNATURAS\n";
    //     $array_asignaturas = abrir_archivo($path_tablas['asignaturas']);
    //     $corregidos_asig = validar_y_corregir_datos_asignaturas($array_asignaturas, 'asignaturas_invalidos.csv', 'asignaturas_corregidos.csv');
    //     $asi = crear_array_asinaturas($corregidos_asig);
    //     foreach ($asi as $fila) {
    //         insertar_en_tabla($db, 'asignaturas', $fila);
    //     # Liberar memoria
    //     unset($array_asignaturas);
    //     unset($corregidos_asig);
    //     unset($asi);
    // }
    // } catch (Exception $e) {
    //     echo "Error al cargar asignaturas: " . $e->getMessage();
    // }

    // try {
    //     echo "INICIO DE INSERCIÓN DE DATOS PLANES\n";
    //     $array_plan = abrir_archivo($path_tablas['planes']);
    //     $corregidos_plan = validar_y_corregir_datos_planes($array_plan, 'planes_invalidos.csv', 'planes_corregidos.csv');
    //     $planes = crear_array_planes($corregidos_plan);
    //     foreach ($planes as $fila) {
    //         insertar_en_tabla($db, 'planes', $fila);
    //     # Liberar memoria
    //     unset($array_plan);
    //     unset($corregidos_plan);
    //     unset($planes);
    // }
    // } catch (Exception $e) {
    //     echo "Error al cargar planes: " . $e->getMessage();
    // }

    // try {
    //     echo "INICIO DE INSERCIÓN DE DATOS PREREQUISITOS\n";
    //     $array_prereq = abrir_archivo($path_tablas['prerequisitos']);
    //     $corregidos_prereq = validar_y_corregir_datos_prerrequisitos($array_prereq, 'prerequisitos_invalidos.csv', 'prerequisitos_corregidos.csv');
    //     $pre = crear_array_prerrequisitos($corregidos_prereq);
    //     foreach ($pre as $fila) {
    //         insertar_en_tabla($db, 'prerequisitos', $fila);
    //     # Liberar memoria
    //     unset($array_prereq);
    //     unset($corregidos_prereq);
    //     unset($pre);
    // }
    // } catch (Exception $e) {
    //     echo "Error al cargar prerequisitos: " . $e->getMessage();
    // }

    // try {
    //     echo "INICIO DE INSERCIÓN DE DATOS ACADÉMICOS/ADMIN\n";
    //     $array_docentes = abrir_archivo($path_tablas['docentes_planificados']);
    //     $corregidos_docente = validar_y_corregir_datos_docentes($array_docentes, 'docentes_invalidos.csv', 'docentes_corregidos.csv');
    //     $array_academico = crear_array_academicos($corregidos_docente["academicos"]);
    //     $array_administrativo = crear_array_administrativos($corregidos_docente["administrativos"]);

    //     foreach ($array_academico as $fila) {
    //         insertar_en_tabla($db, 'academico', $fila);
    //     }

    //     foreach ($array_administrativo as $fila) {
    //         insertar_en_tabla($db, 'administrativo', $fila);
    //     }
    //     # Liberar memoria
    //     unset($array_docentes);
    //     unset($corregidos_docente);
    //     unset($array_academico);
    //     unset($array_administrativo);
    // } catch (Exception $e) {
    //     echo "Error al cargar academicos/admin: " . $e->getMessage();
    // }

    // try {
    //     echo "INICIO DE INSERCIÓN DE DATOS HISTORIAL\n";
    //     // Total de iteraciones a realizar
    //     $total_iteraciones = 604; 
    //     // Tamaño del lote a leer
    //     $tamaño_lote = 1000; 
    //     // Iterar 604 veces
    //     for ($indice_inicio = 0; $indice_inicio < $total_iteraciones; $indice_inicio++) {
    //         // Calcular el índice de inicio para la lectura
    //         $indice_inicio_lectura = $indice_inicio * $tamaño_lote;
    //         // Leer los datos desde el archivo en lotes de 1000
    //         $array_notas = abrir_archivo_notas($path_tablas['notas'], $indice_inicio_lectura);
            
    //         // Validar y corregir los datos
    //         $corregidos_notas = validar_y_corregir_datos_notas($array_notas, 'notas_invalidos.csv', 'notas_corregidos.csv');
    //         $historial = crear_array_historial($corregidos_notas);
    
    //         // Insertar cada fila en la tabla 'historial'
    //         foreach ($historial as $fila) {
    //             insertar_en_tabla($db, 'historial', $fila);
    //         }
    //     }
        
    // } catch (Exception $e) {
    //     echo "Error al cargar historial poblar : " . $e->getMessage();
    // }
    

?> 