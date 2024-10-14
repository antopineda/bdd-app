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


$tablas_iniciales = array(
    'asignaturas' => 'plan VARCHAR(10), asignatura_id VARCHAR(6) PRIMARY KEY, asignatura VARCHAR(50), nivel VARCHAR(3), prerequisitos VARCHAR(20)', 
    'estudiantes' => 'codigo_plan VARCHAR(3), carrera VARCHAR(100), cohorte VARCHAR(7), num_alumno VARCHAR(15), bloqueo VARCHAR(2), causal_bloqueo VARCHAR(200), run VARCHAR(8) PRIMARY KEY, dv VARCHAR(1), nombre_1 VARCHAR(50), nombre_2 VARCHAR(50), apellido_1 VARCHAR(50), apellido_2 VARCHAR(50), logro VARCHAR(50), fecha_logro VARCHAR(7), ultima_carga VARCHAR(7)',
    'historial' => 'run VARCHAR(8) NOT NULL, codigo_plan VARCHAR(3), num_alumno VARCHAR(10), periodo VARCHAR(7), codigo_asignatura VARCHAR(10), convocatoria VARCHAR(3), calificacion VARCHAR(2), nota DECIMAL(4,2), PRIMARY KEY (run, codigo_asignatura, periodo)',
    'planes' => 'codigo_plan VARCHAR(4) PRIMARY KEY, facultad VARCHAR(20), carrera VARCHAR(20), nombre_plan VARCHAR(50), jornada VARCHAR(20), sede VARCHAR(20), grado VARCHAR(20), modalidad VARCHAR(20), inicio_vigencia VARCHAR(10)',
    'prerequisitos' => 'codigo_plan VARCHAR(3), asignatura_id VARCHAR(10) PRIMARY KEY, asignatura VARCHAR (20), nivel VARCHAR(3), prerequisito_1 VARCHAR(10), prerequisito_2 VARCHAR(10)',
    'academico' => 'run VARCHAR(8) NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email_institucional VARCHAR(50), dedicacion VARCHAR(20), contrato VARCHAR(20), grado VARCHAR(20), jerarquia VARCHAR(50) NOT NULL, cargo VARCHAR(50), estamento VARCHAR(50)',
    'administrativo' => 'run VARCHAR(8) NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email_institucional VARCHAR(50), dedicacion VARCHAR(20), contrato VARCHAR(20), grado VARCHAR(20), jerarquia VARCHAR(50), cargo VARCHAR(50) NOT NULL, estamento VARCHAR(50)',
    'oferta' => 'periodo VARCHAR(7), sede VARCHAR(20), facultad VARCHAR(20), id_depto VARCHAR(10), asignatura_id VARCHAR(10), seccion VARCHAR(2), duracion CHAR, vacantes VARCHAR(3), inscritos VARCHAR(3), dia VARCHAR(10), hora_inicio VARCHAR(10), hora_fin VARCHAR(10), fecha_inicio VARCHAR(10), fecha_fin VARCHAR(10), edificio VARCHAR(20), profesor_run VARCHAR(8), profesor_nombre VARCHAR(50), profesor_apellido VARCHAR(50), jerarquizacion VARCHAR(10)',
    'personas' => 'run VARCHAR(8) NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email VARCHAR(50), telefono VARCHAR(9), estamento VARCHAR(50)',
    
);

// Tablas que estan tal cual: asignaturas, estudiantes, planes, prerequisitos

?>