<?php include('../templates/header.html'); ?>

<body>
    <?php
    require("../config/conexion.php");

    // Obtener el código de curso ingresado como parámetro desde el formulario
    $codigo_curso = $_POST["codigo_curso"];

    echo "<h2>Porcentaje de aprobación histórico por curso para el curso $codigo_curso</h2>";

    // Consulta SQL para obtener el promedio del porcentaje de aprobación histórico agrupado por profesor
    $query = "
        SELECT 
            o.codigo_asignatura, 
            o.nombre_asignatura, 
            o.profesor_nombre, 
            o.profesor_apellido, 
            AVG(CASE WHEN h.nota >= 4.0 THEN 1 ELSE 0 END) * 100 AS porcentaje_aprobacion
        FROM 
            oferta o
        LEFT JOIN 
            historial h ON o.codigo_asignatura = h.codigo_asignatura
        WHERE 
            o.codigo_asignatura = :codigo_curso
        GROUP BY 
            o.codigo_asignatura, o.nombre_asignatura, o.profesor_nombre, o.profesor_apellido
        ORDER BY 
            o.codigo_asignatura;
    ";

    // Preparar y ejecutar la consulta
    $result = $db->prepare($query);
    $result->bindParam(':codigo_curso', $codigo_curso, PDO::PARAM_STR);
    $result->execute();
    $cursos = $result->fetchAll();

    // Verificar si se encontraron resultados y mostrar la tabla
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
        // Si no se encuentran resultados
        echo "<p>No se encontraron registros de aprobación para el curso ingresado.</p>";
    }
    ?>
</body>

<?php include('../templates/footer.html'); ?>
