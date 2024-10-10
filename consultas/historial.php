<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $num_alumno = $_POST['num_alumno'];

    // Consulta SQL
    $sql = "
        SELECT h.periodo, h.codigo_asignatura, h.nota, h.calificacion, 
               COUNT(CASE WHEN h.nota >= 4.0 THEN 1 END) AS aprobados,
               COUNT(CASE WHEN h.nota < 4.0 THEN 1 END) AS reprobados,
               COUNT(CASE WHEN h.periodo = '2024-2' THEN 1 END) AS vigentes,
               AVG(h.nota) AS PPS
        FROM historial h
        WHERE h.num_alumno = :num_alumno
        GROUP BY h.periodo
        ORDER BY h.periodo ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['num_alumno' => $num_alumno]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mostrar resultados
    if ($resultados) {
        foreach ($resultados as $periodo) {
            echo "<h3>Periodo: " . $periodo['periodo'] . "</h3>";
            echo "<p>Asignatura: " . $periodo['codigo_asignatura'] . "</p>";
            echo "<p>Nota: " . $periodo['nota'] . "</p>";
            echo "<p>Calificación: " . $periodo['calificacion'] . "</p>";
            echo "<p>Aprobados: " . $periodo['aprobados'] . "</p>";
            echo "<p>Reprobados: " . $periodo['reprobados'] . "</p>";
            echo "<p>Vigentes: " . $periodo['vigentes'] . "</p>";
            echo "<p>PPS: " . $periodo['PPS'] . "</p>";
            echo "<hr>";
        }

        // Cálculo de PPA y estado del estudiante
        $sql_resumen = "
            SELECT 
                COUNT(CASE WHEN h.nota >= 4.0 THEN 1 END) AS total_aprobados,
                COUNT(CASE WHEN h.nota < 4.0 THEN 1 END) AS total_reprobados,
                COUNT(CASE WHEN h.calificacion = 'V' THEN 1 END) AS total_vigentes,
                AVG(h.nota) AS PPA
            FROM historial h
            WHERE h.num_alumno = :num_alumno
        ";
        
        $stmt_resumen = $pdo->prepare($sql_resumen);
        $stmt_resumen->execute(['num_alumno' => $num_alumno]);
        $resumen = $stmt_resumen->fetch(PDO::FETCH_ASSOC);

        echo "<h3>Resumen Total:</h3>";
        echo "<p>Aprobados: " . $resumen['total_aprobados'] . "</p>";
        echo "<p>Reprobados: " . $resumen['total_reprobados'] . "</p>";
        echo "<p>Vigentes: " . $resumen['total_vigentes'] . "</p>";
        echo "<p>PPA: " . $resumen['PPA'] . "</p>";

        // Verificar estado del estudiante
        $estado_estudiante = ($resumen['total_vigentes'] > 0) ? 'Vigente' : 'No vigente';
        echo "<p>Estado del estudiante: " . $estado_estudiante . "</p>";
    } else {
        echo "No se encontraron resultados para el número de alumno ingresado.";
    }
}
?>
