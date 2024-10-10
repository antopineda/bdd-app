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
    'asignaturas' => 'plan VARCHAR(10), asignatura_id VARCHAR(6) PRIMARY KEY, asignatura VARCHAR(50), nivel INT',
    'estudiantes' => 'codigo_plan VARCHAR(3), carrera VARCHAR(20), cohorte VARCHAR(7), num_alumno VARCHAR(15), bloqueo CHAR, causal_bloqueo VARCHAR(50), run VARCHAR(10) PRIMARY KEY, nombres VARCHAR(50), apellido VARCHAR(50), plan VARCHAR(10), logro VARCHAR(50), fecha_logro VARCHAR(7), ultima_carga VARCHAR(7)',
    'historial' => 'run INT NOT NULL PRIMARY KEY, num_alumno VARCHAR(10), periodo VARCHAR(7), codigo_asignatura VARCHAR(10), convocatoria VARCHAR(3), calificacion VARCHAR(2), nota DECIMAL(4,2)',
    'planes' => 'codigo_plan VARCHAR(4) PRIMARY KEY, facultad VARCHAR(20), carrera VARCHAR(20), nombre_plan VARCHAR(50), jornada VARCHAR(20), sede VARCHAR(20), grado VARCHAR(20), modalidad VARCHAR(20), inicio_vigencia VARCHAR(10)',
    'prerequisitos' => 'asignatura_id VARCHAR(10) PRIMARY KEY, prerequisito_1 VARCHAR(10), prerequisito_2 VARCHAR(10)',
    'academico' => 'run INT NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email_institucional VARCHAR(50), dedicacion VARCHAR(20), contrato VARCHAR(20), grado VARCHAR(20), jerarquia VARCHAR(50) NOT NULL, cargo VARCHAR(50), estamento VARCHAR(50)',
    'administrativo' => 'run INT NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email_institucional VARCHAR(50), dedicacion VARCHAR(20), contrato VARCHAR(20), grado VARCHAR(20), jerarquia VARCHAR(50), cargo VARCHAR(50) NOT NULL, estamento VARCHAR(50)',
    'oferta' => 'periodo VARCHAR(7), sede VARCHAR(20), facultad VARCHAR(20), id_depto INT, asignatura_id VARCHAR(10), seccion INT, duracion CHAR, vacantes INT, inscritos INT, dia VARCHAR(10), hora_inicio TIME, hora_fin TIME, fecha_fin DATE, edificio VARCHAR(10), profesor_run INT, profesor_nombre VARCHAR(50), profesor_apellido VARCHAR(50), jerarquizacion VARCHAR(10)',
    'aprobacion' => 'codigo_curso VARCHAR(10), nombre_curso VARCHAR(50), nombre_profesor VARCHAR(50), porcentaje_aprobacion DECIMAL(4,2)',
    'personas' => 'run INT NOT NULL PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email VARCHAR(50), telefono INT, estamento VARCHAR(50)',
    
);

?>