<?php include('../templates/header.html'); ?>

<body>
    <?php
    require("../config/conexion.php");

    // Obtener el código de la asignatura desde el formulario
    $codigo = $_POST["sigla"];
    echo "<h2>Porcentaje de aprobación para la asignatura $codigo</h2>";

    // Consulta SQL para obtener el porcentaje de aprobación
    $query = "
        SELECT 
            o.profesor_nombre, 
            o.profesor_apellido, 
            AVG(CASE 
                WHEN h.nota >= 4.0 THEN 1 
                ELSE 0 
            END) * 100 AS porcentaje_aprobacion
        FROM 
            historial h
        JOIN 
            oferta o ON h.codigo_asignatura = o.codigo_asignatura
        WHERE 
            h.codigo_asignatura = :codigo_asignatura
        GROUP BY 
            o.profesor_nombre, o.profesor_apellido;
    ";

    // Preparar y ejecutar la consulta
    $result = $db->prepare($query);
    $result->bindParam(':codigo_asignatura', $codigo, PDO::PARAM_STR);

    // Verificar si la consulta se ejecuta correctamente
    if ($result->execute()) {
        $profesores = $result->fetchAll();

        // Verificar si se encontraron resultados
        if (count($profesores) > 0) {
            echo '<table class="styled-table">
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Porcentaje de Aprobación</th>
                    </tr>';
            foreach ($profesores as $profesor) {
                echo "<tr>
                        <td>{$profesor['profesor_nombre']}</td>
                        <td>{$profesor['profesor_apellido']}</td>
                        <td>" . round($profesor['porcentaje_aprobacion'], 2) . "%</td>
                    </tr>";
            }
            echo '</table>';
        } else {
            echo "<p>No se encontraron datos para la asignatura especificada.</p>";
        }
    } else {
        // Mensaje en caso de que la consulta falle
        echo "<p>Error en la ejecución de la consulta.</p>";
    }
    ?>
</body>

<?php include('../templates/footer.html'); ?>
