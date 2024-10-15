<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_curso = $_POST['codigo_curso'];

    // Consulta SQL que obtiene el porcentaje de aprobación por profesor
    $sql = "
        SELECT p.nombre AS profesor,
               COUNT(CASE WHEN h.nota >= 4.0 THEN 1 END) * 100.0 / COUNT(*) AS porcentaje_aprobacion
        FROM historial h
        JOIN cursos_profesores cp ON h.codigo_asignatura = cp.codigo_curso
        JOIN profesores p ON cp.run_profesor = p.run
        WHERE h.codigo_asignatura = :codigo_curso
        GROUP BY p.nombre
        ORDER BY porcentaje_aprobacion DESC
    ";

    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['codigo_curso' => $codigo_curso]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mostrar los resultados
    if ($resultados) {
        echo "<h2>Porcentaje de Aprobación por Profesor</h2>";
        foreach ($resultados as $row) {
            echo "<p>Profesor: " . $row['profesor'] . " | Porcentaje de Aprobación: " . round($row['porcentaje_aprobacion'], 2) . "%</p>";
        }
    } else {
        echo "<p>No se encontraron resultados para el código de curso ingresado.</p>";
    }
}
?>
