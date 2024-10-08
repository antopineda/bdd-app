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
    'notas' => 'RUN INT NOT NULL, num_alumno VARCHAR(10), periodo VARCHAR(7), codigo VARCHAR(10), convocatoria VARCHAR(3), calificacion VARCHAR(2), nota DECIMAL(4,2)',
    'estudiantes' => 'codigo_plan VARCHAR(3), carrera VARCHAR(20), cohorte VARCHAR(7), bloqueo CHAR, causal_bloqueo VARCHAR(50), RUN VARCHAR(10) PRIMARY KEY, nombre VARCHAR(50), apellido VARCHAR(50), email VARCHAR(50), plan VARCHAR(10), logro VARCHAR(50), fecha_logro VARCHAR(7), ultima_carga VARCHAR(7)',
);

?>