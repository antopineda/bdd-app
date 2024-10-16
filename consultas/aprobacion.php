<?php include('../templates/header.html'); ?>

<body>
    <?php
    require("../config/conexion.php");

    // Obtener el periodo desde el formulario
    $periodo = $_POST["periodo"];
    echo "<h2>Porcentaje de aprobación por curso para el periodo $periodo</h2>";

    // Consulta SQL para obtener el porcentaje de aprobación por curso
    $query = "
        SELECT 
            o.codigo_asignatura,
            o.nombre_asignatura,
            o.profesor_nombre,
            o.profesor_apellido,
            COUNT(CASE WHEN h.nota >= 4.0 THEN 1 END) * 100.0 / NULLIF(COUNT(h.nota), 0) AS porcentaje_aprobacion
        FROM 
            oferta o
        LEFT JOIN 
            historial h ON o.codigo_asignatura = h.codigo_asignatura AND h.periodo = :periodo
        GROUP BY 
            o.codigo_asignatura, o.nombre_asignatura, o.profesor_nombre, o.profesor_apellido
        ORDER BY 
            o.codigo_asignatura;
    ";

    // Preparar y ejecutar la consulta
    $result = $db->prepare($query);
    $result->bindParam(':periodo', $periodo, PDO::PARAM_STR);
    
    if ($result->execute()) {
        $cursos = $result->fetchAll();

        // Mostrar resultados
        if (count($cursos) > 0) {
            echo '<table class="styled-table">
                    <tr>
                        <th>Código de Curso</th>
                        <th>Nombre de Curso</th>
                        <th>Profesor</th>
                        <th>Porcentaje de Aprobación</th>
                    </tr>';
            foreach ($cursos as $curso) {
                echo "<tr>
                        <td>{$curso['codigo_asignatura']}</td>
                        <td>{$curso['nombre_asignatura']}</td>
                        <td>{$curso['profesor_nombre']} {$curso['profesor_apellido']}</td>
                        <td>" . round($curso['porcentaje_aprobacion'], 2) . "%</td>
                    </tr>";
            }
            echo '</table>';
        } else {
            echo "<p>No se encontraron cursos para el periodo especificado.</p>";
        }
    } else {
        echo "<p>Error en la ejecución de la consulta.</p>";
    }
    ?>
</body>

<?php include('../templates/footer.html'); ?>
