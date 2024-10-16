<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario tiene acceso
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// Conectar a la base de datos
require('../config/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_curso = $_POST['codigo_curso'];

    // Consulta SQL que obtiene el porcentaje de aprobación por profesor
    $sql = "
        SELECT o.profesor_nombre AS profesor,
               COUNT(CASE WHEN h.nota >= 4.0 THEN 1 END) * 100.0 / COUNT(*) AS porcentaje_aprobacion
        FROM historial h
        JOIN oferta o ON h.codigo_asignatura = o.codigo_asignatura
        WHERE h.codigo_asignatura = :codigo_curso
        GROUP BY o.profesor_nombre
        ORDER BY porcentaje_aprobacion DESC
    ";

    // Preparar y ejecutar la consulta
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':codigo_curso', $codigo_curso, PDO::PARAM_STR);
    $stmt->execute();
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
