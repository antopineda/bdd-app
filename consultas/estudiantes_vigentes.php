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
    $query = "
    SELECT 
        CASE 
            WHEN 
                (
                    (EXTRACT(YEAR FROM to_date(e.cohorte, 'YYYY-MM')) - 2024) * -2 + 
                    CASE 
                        WHEN SUBSTRING(e.cohorte, 6, 2) = '01' THEN 1 
                        ELSE 0 
                    END
                ) = 
                CASE 
                    WHEN POSITION(' ' IN e.logro) > 0 AND SUBSTRING(e.logro, 1, POSITION(' ' IN e.logro) - 1) ~ '^[0-9]+$'
                    THEN CAST(SUBSTRING(e.logro, 1, POSITION(' ' IN e.logro) - 1) AS INT)
                    ELSE NULL
                END
            THEN 'Dentro de nivel'
            ELSE 'Fuera de nivel'
        END AS estado_nivel,
        COUNT(*) AS cantidad
    FROM estudiantes e
    JOIN historial h ON e.run = h.run
    WHERE h.periodo = '2024-02'
    AND (POSITION(' ' IN e.logro) > 0 AND SUBSTRING(e.logro, 1, POSITION(' ' IN e.logro) - 1) ~ '^[0-9]+$')
    GROUP BY estado_nivel
    WITH ROLLUP;  -- Esta línea añade una fila adicional que resume los totales, si es útil.
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
    <title>Reporte de Estudiantes Vigentes</title>
</head>
<body>

<h1>Reporte de Estudiantes Vigentes</h1>

<?php
// Verificar si el reporte tiene datos
if (empty($reporte)) {
    echo "<p>No se encontraron resultados para la consulta.</p>";
} else {
    // Mostrar el contenido del array $reporte
    foreach ($reporte as $fila) {
        echo "<p>Estado: " . htmlspecialchars($fila['estado_nivel']) . " - Cantidad: " . htmlspecialchars($fila['cantidad']) . "</p>";
    }
}
?>

</body>
</html>