<?php include('../templates/header.html'); ?>

<body>
    <?php
    require("../config/conexion.php");

    // Obtiene el periodo de la entrada del usuario
    $periodo = $_POST['periodo'];

    echo "<h2>Porcentaje de aprobación por periodo</h2>";

    // Consulta SQL
    $query = "
        SELECT 
            o.asignatura_id AS codigo_curso, 
            o.profesor_nombre, 
            o.profesor_apellido,
            COUNT(CASE WHEN h.nota >= 4.0 THEN 1 END) * 100.0 / NULLIF(COUNT(h.num_alumno), 0) AS porcentaje_aprobacion
        FROM oferta o
        LEFT JOIN historial h ON o.asignatura_id = h.codigo_asignatura AND h.periodo = :periodo
        WHERE o.periodo = :periodo
        GROUP BY o.asignatura_id, o.profesor_nombre, o.profesor_apellido
        ORDER BY o.asignatura_id;
    ";

    $result = $db->prepare($query);
    $result->execute(['periodo' => $periodo]);
    $aprobacion = $result->fetchAll();

    ?>

    <table class="styled-table">
        <tr>
            <th>Código de Curso</th>
            <th>Nombre del Profesor</th>
            <th>Porcentaje de Aprobación</th>
        </tr>
        <?php
        foreach ($aprobacion as $a) {
            echo "<tr>
                    <td>$a[codigo_curso]</td>
                    <td>$a[profesor_nombre] $a[profesor_apellido]</td>
                    <td>" . round($a['porcentaje_aprobacion'], 2) . "%</td>
                  </tr>";
        }
        ?>
    </table>
</body>

<?php include('../templates/footer.html'); ?>
