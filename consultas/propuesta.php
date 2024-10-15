<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $num_alumno = $_POST['num_alumno'];

    // Verificar si el estudiante está vigente en 2024-2
    $sql_vigente = "
        SELECT COUNT(*) as vigente 
        FROM historial 
        WHERE num_alumno = :num_alumno 
        AND periodo = '2024-2'
        AND calificacion = 'V'
    ";
    $stmt_vigente = $pdo->prepare($sql_vigente);
    $stmt_vigente->execute(['num_alumno' => $num_alumno]);
    $vigente = $stmt_vigente->fetch(PDO::FETCH_ASSOC)['vigente'];

    if ($vigente == 0) {
        echo "<p>El estudiante no está vigente en el periodo 2024-2. </p>";
        exit();
    }

    // Consulta para obtener los cursos vigentes aprobados por el estudiante en 2024-2
    $sql_cursos_aprobados = "
        SELECT h.codigo_asignatura
        FROM historial h
        WHERE h.num_alumno = :num_alumno
        AND h.periodo = '2024-2'
        AND h.calificacion = 'V'
        AND h.nota >= 4.0
    ";
    $stmt_cursos_aprobados = $pdo->prepare($sql_cursos_aprobados);
    $stmt_cursos_aprobados->execute(['num_alumno' => $num_alumno]);
    $cursos_aprobados = $stmt_cursos_aprobados->fetchAll(PDO::FETCH_COLUMN);

    // Consulta para obtener los cursos recomendados para 2025-1
    $sql_propuesta = "
        SELECT a.asignatura_id, a.asignatura
        FROM asignaturas a
        LEFT JOIN prerequisitos p ON a.asignatura_id = p.asignatura_id
        WHERE a.codigo_plan = (SELECT codigo_plan FROM estudiantes WHERE num_alumno = :num_alumno)
        AND NOT EXISTS (
            SELECT 1 
            FROM historial h
            WHERE h.num_alumno = :num_alumno
            AND h.codigo_asignatura = a.asignatura_id
        )
        AND (
            p.prerequisito_1 IS NULL OR p.prerequisito_1 IN (" . implode(',', array_map(function($v){ return "'$v'"; }, $cursos_aprobados)) . ")
        )
        AND (
            p.prerequisito_2 IS NULL OR p.prerequisito_2 IN (" . implode(',', array_map(function($v){ return "'$v'"; }, $cursos_aprobados)) . ")
        )
    ";
    
    // Preparar y ejecutar la consulta
    $stmt_propuesta = $pdo->prepare($sql_propuesta);
    $stmt_propuesta->execute(['num_alumno' => $num_alumno]);
    $propuesta_ramos = $stmt_propuesta->fetchAll(PDO::FETCH_ASSOC);

    // Mostrar la propuesta de toma de ramos
    if ($propuesta_ramos) {
        echo "<h2>Propuesta de Toma de Ramos para 2025-1</h2>";
        foreach ($propuesta_ramos as $ramo) {
            echo "<p>Asignatura: " . $ramo['asignatura'] . " (ID: " . $ramo['asignatura_id'] . ")</p>";
        }
    } else {
        echo "<p>No hay cursos recomendados para 2025-1. </p>";
    }
}
?>
