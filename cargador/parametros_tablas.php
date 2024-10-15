<?php

$path_tablas = array(
    'asignaturas' => '../data/asignaturas.csv',
    'notas' => '../data/notas.csv',
    'estudiantes' => '../data/estudiantes.csv',
    'planes' => '../data/planes.csv',
    'prerequisitos' => '../data/prerequisitos.csv',
    'planeacion' => '../data/planeacion.csv',
    'docentes_planificados' => '../data/docentes_planificados.csv',
);


// $tablas_iniciales = array(
//     'asignaturas' => 'plan VARCHAR(10), asignatura_id VARCHAR(6) PRIMARY KEY, asignatura VARCHAR(50), nivel VARCHAR(3), prerequisitos VARCHAR(20)', 
//     'estudiantes' => 'codigo_plan VARCHAR(3), carrera VARCHAR(100), cohorte VARCHAR(7), num_alumno VARCHAR(15), bloqueo VARCHAR(2), causal_bloqueo VARCHAR(200), run VARCHAR(8) PRIMARY KEY, dv VARCHAR(1), nombre_1 VARCHAR(50), nombre_2 VARCHAR(50), apellido_1 VARCHAR(50), apellido_2 VARCHAR(50), logro VARCHAR(50), fecha_logro VARCHAR(7), ultima_carga VARCHAR(7)',
//     'historial' => 'run VARCHAR(8) NOT NULL, codigo_plan VARCHAR(3), num_alumno VARCHAR(10), periodo VARCHAR(7), codigo_asignatura VARCHAR(10), convocatoria VARCHAR(3), calificacion VARCHAR(2), nota DECIMAL(4,2), PRIMARY KEY (run, codigo_asignatura, periodo)',
//     'planes' => 'codigo_plan VARCHAR(4) PRIMARY KEY, facultad VARCHAR(20), carrera VARCHAR(20), nombre_plan VARCHAR(50), jornada VARCHAR(20), sede VARCHAR(20), grado VARCHAR(20), modalidad VARCHAR(20), inicio_vigencia VARCHAR(10)',
//     'prerequisitos' => 'codigo_plan VARCHAR(3), asignatura_id VARCHAR(10) PRIMARY KEY, asignatura VARCHAR (20), nivel VARCHAR(3), prerequisito_1 VARCHAR(10), prerequisito_2 VARCHAR(10)',
//     'academico' => 'run VARCHAR(8) NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email_institucional VARCHAR(50), dedicacion VARCHAR(20), contrato VARCHAR(20), grado VARCHAR(20), jerarquia VARCHAR(50) NOT NULL, cargo VARCHAR(50), estamento VARCHAR(50)',
//     'administrativo' => 'run VARCHAR(8) NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email_institucional VARCHAR(50), dedicacion VARCHAR(20), contrato VARCHAR(20), grado VARCHAR(20), jerarquia VARCHAR(50), cargo VARCHAR(50) NOT NULL, estamento VARCHAR(50)',
//     'oferta' => 'periodo VARCHAR(7), sede VARCHAR(20), facultad VARCHAR(20), id_depto VARCHAR(10), asignatura_id VARCHAR(10), seccion VARCHAR(2), duracion CHAR, vacantes VARCHAR(3), inscritos VARCHAR(3), dia VARCHAR(10), hora_inicio VARCHAR(10), hora_fin VARCHAR(10), fecha_inicio VARCHAR(10), fecha_fin VARCHAR(10), edificio VARCHAR(20), profesor_run VARCHAR(8), profesor_nombre VARCHAR(50), profesor_apellido VARCHAR(50), jerarquizacion VARCHAR(10)',
//     'personas' => 'run VARCHAR(8) NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email VARCHAR(50), telefono VARCHAR(9), estamento VARCHAR(50)',
    
// );

$tablas_iniciales = array(
    'asignaturas' => 'codigo_plan VARCHAR(20), asignatura_id VARCHAR(20) PRIMARY KEY, asignatura VARCHAR(100), nivel VARCHAR(10), prerequisitos VARCHAR(20)', 
    'estudiantes' => 'codigo_plan VARCHAR(4), carrera VARCHAR(100), cohorte VARCHAR(7), num_alumno VARCHAR(15), bloqueo VARCHAR(2), causal_bloqueo VARCHAR(200), run VARCHAR(8) PRIMARY KEY, dv VARCHAR(2), nombre_1 VARCHAR(51), nombre_2 VARCHAR(52), apellido_1 VARCHAR(53), apellido_2 VARCHAR(54), logro VARCHAR(100), fecha_logro VARCHAR(20), ultima_carga VARCHAR(20)',
    'historial' => 'run VARCHAR(8) NOT NULL, codigo_plan VARCHAR(4), num_alumno VARCHAR(15), periodo VARCHAR(7), codigo_asignatura VARCHAR(20), convocatoria VARCHAR(10), calificacion VARCHAR(10), nota DECIMAL(4,2), PRIMARY KEY (run, codigo_asignatura, periodo)',
    'planes' => 'codigo_plan VARCHAR(4) PRIMARY KEY, facultad VARCHAR(100), carrera VARCHAR(100), nombre_plan VARCHAR(100), jornada VARCHAR(100), sede VARCHAR(200), grado VARCHAR(200), modalidad VARCHAR(100), inicio_vigencia VARCHAR(100)',
    'prerequisitos' => 'codigo_plan VARCHAR(4), asignatura_id VARCHAR(20) PRIMARY KEY, asignatura VARCHAR (20), nivel VARCHAR(10), prerequisito_1 VARCHAR(20), prerequisito_2 VARCHAR(20)',
    'academico' => 'run VARCHAR(8) NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email_institucional VARCHAR(50), dedicacion VARCHAR(50), contrato VARCHAR(50), grado VARCHAR(50), jerarquia VARCHAR(50) NOT NULL, cargo VARCHAR(50), estamento VARCHAR(50)',
    'administrativo' => 'run VARCHAR(8) NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email_institucional VARCHAR(50), dedicacion VARCHAR(50), contrato VARCHAR(50), grado VARCHAR(50), jerarquia VARCHAR(50), cargo VARCHAR(50) NOT NULL, estamento VARCHAR(50)',
    'oferta' => 'id SERIAL PRIMARY KEY, periodo VARCHAR(7), sede VARCHAR(200), facultad VARCHAR(100), id_depto VARCHAR(10), asignatura_id VARCHAR(10), seccion VARCHAR(4), duracion VARCHAR(20), vacantes VARCHAR(10), inscritos VARCHAR(10), dia VARCHAR(10), hora_inicio VARCHAR(10), hora_fin VARCHAR(10), fecha_inicio VARCHAR(10), fecha_fin VARCHAR(10), edificio VARCHAR(100), profesor_run VARCHAR(8), profesor_nombre VARCHAR(50), profesor_apellido VARCHAR(50), jerarquizacion VARCHAR(50)',
    'personas' => 'run VARCHAR(8) NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email VARCHAR(50), telefono VARCHAR(9), estamento VARCHAR(50)',
    'usuarios' => 'email VARCHAR(50) PRIMARY KEY, password VARCHAR(255), role VARCHAR(30)'

);

// Tablas que estan tal cual: asignaturas, estudiantes, planes, prerequisitos

?>