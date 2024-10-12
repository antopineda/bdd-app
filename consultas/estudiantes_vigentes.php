<?php
// Inicia sesión
session_start();

// Verifica si el usuario tiene acceso
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// Conecta a la base de datos
require('../config/conexion.php');

try {
    // Consulta
    $query = "
        SELECT 
            CASE 
                WHEN (EXTRACT(YEAR FROM CURRENT_DATE) - EXTRACT(YEAR FROM to_date(e.cohorte, 'YYYY-MM'))) * 2 +
                    CASE 
                        WHEN SUBSTRING(e.cohorte, 6, 1) = '1' THEN 1 
                        ELSE 0 
                    END 
                    = CAST(SUBSTRING(e.logro, 1, POSITION(' ' IN e.logro) - 1) AS INT) 
                THEN 'Dentro de nivel'
                ELSE 'Fuera de nivel'
            END AS estado_nivel,
            COUNT(*) AS cantidad
        FROM estudiantes e
        JOIN historial h ON e.run = h.run
        WHERE h.periodo = '2024-2'
        GROUP BY estado_nivel;
    ";

    $result = $db->prepare($query);
    $result->execute();
    $reporte = $result->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {
    echo "Ocurrió un error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Estudiantes Vigentes</title>
    <link rel="stylesheet" href="../styles/mystyle.css">
</head>
<body class="user"> 
    <h1 class="title">Reporte de Estudiantes Vigentes</h1>

    <table class="styled-table"> 
        <thead>
            <tr>
                <th>Estado Nivel</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reporte as $fila): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['estado_nivel']); ?></td>
                    <td><?php echo htmlspecialchars($fila['cantidad']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <a href="../user.php" class="form-button">Volver a consultas</a> 
</body>
</html>