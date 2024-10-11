<?php

include '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $periodo = $_POST['periodo'];
    $sql = "SELECT codigo_curso, nombre_profesor, porcentaje_aprobacion 
            FROM aprobacion 
            WHERE periodo = ?";
 
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $periodo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<div class='user'>";
            echo "<h2 class='title'>Reporte: Porcentaje de Aprobación</h2>";
            echo "<table class='styled-table'>";
            echo "<thead>";
            echo "<tr><th>Código Curso</th><th>Nombre del Profesor</th><th>Porcentaje de Aprobación</th></tr>";
            echo "</thead>";
            echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['codigo_curso']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nombre_profesor']) . "</td>";
                echo "<td>" . htmlspecialchars($row['porcentaje_aprobacion']) . "%</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>"; 
        } else {
            echo "<div class='user'><p>No se encontraron resultados para el periodo ingresado.</p></div>";
        }

        $stmt->close();
    } else {
        echo "<div class='user'><p>Error en la consulta: " . $conn->error . "</p></div>";
    }
}

// Cerrar la conexión
$conn->close();
?>