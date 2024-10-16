<?php include('../templates/header.html'); ?>

<body>
    <?php
    require("../config/conexion.php");

    // Verificar si se ha enviado el formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Obtener el número de alumno desde el formulario
        $num_alumno = $_POST['num_alumno'];

        echo "<h2>Historial del estudiante</h2>";

        // Consulta SQL
        $query = "
            SELECT h.periodo, h.codigo_asignatura, h.nota, h.calificacion,
                   COUNT(CASE WHEN h.nota >= 4.0 THEN 1 END) OVER (PARTITION BY h.periodo) AS aprobados,
                   COUNT(CASE WHEN h.nota < 4.0 THEN 1 END) OVER (PARTITION BY h.periodo) AS reprobados,
                   COUNT(CASE WHEN h.periodo = '2024-02' THEN 1 END) OVER (PARTITION BY h.periodo) AS vigentes,
                   AVG(CASE WHEN h.nota IS NOT NULL THEN h.nota END) OVER (PARTITION BY h.periodo) AS pps
            FROM historial h
            WHERE h.num_alumno = :num_alumno
            ORDER BY h.periodo ASC;
        ";

        $result = $db->prepare($query);
        $result->bindParam(':num_alumno', $num_alumno, PDO::PARAM_STR);
        $result->execute();
        $historial = $result->fetchAll();

        // Calcular resumen total
        $total_aprobados = 0;
        $total_reprobados = 0;
        $total_vigentes = 0;
        $total_nota = 0;
        $total_periodos_validos = 0; // Contar solo los periodos donde PPS es distinto de 0
        ?>

        <table class="styled-table">
            <tr>
                <th>Periodo</th>
                <th>Asignatura</th>
                <th>Nota</th>
                <th>Calificación</th>
                <th>Aprobados</th>
                <th>Reprobados</th>
                <th>Vigentes</th>
                <th>PPS</th>
            </tr>

            <?php
            foreach ($historial as $registro) {
                $pps_formatted = number_format($registro['pps'], 2);

                // Mostrar la fila en la tabla
                echo "<tr>
                        <td>{$registro['periodo']}</td>
                        <td>{$registro['codigo_asignatura']}</td>
                        <td>{$registro['nota']}</td>
                        <td>{$registro['calificacion']}</td>
                        <td>{$registro['aprobados']}</td>
                        <td>{$registro['reprobados']}</td>
                        <td>{$registro['vigentes']}</td>
                        <td>{$pps_formatted}</td>
                      </tr>";

                // Sumar totales para el resumen solo si PPS es distinto de 0
                if ($registro['pps'] != 0) {
                    $total_nota += $registro['pps'];
                    $total_periodos_validos++;
                }

                $total_aprobados += $registro['aprobados'];
                $total_reprobados += $registro['reprobados'];
                $total_vigentes += $registro['vigentes'];
            }

            // Calcular el PPA solo con periodos válidos (PPS > 0)
            $PPA = $total_periodos_validos > 0 ? $total_nota / $total_periodos_validos : 0;

            // Verificar estado del estudiante
            $estado_estudiante = $total_vigentes > 0 ? 'Vigente' : 'No vigente';
            ?>
        </table>

        <h3>Resumen Total:</h3>
        <p>Aprobados: <?php echo $total_aprobados; ?></p>
        <p>Reprobados: <?php echo $total_reprobados; ?></p>
        <p>Vigentes: <?php echo $total_vigentes; ?></p>
        <p>PPA: <?php echo number_format($PPA, 2); ?></p>
        <p>Estado del estudiante: <?php echo $estado_estudiante; ?></p>

    <?php } ?>
</body>

<?php include('../templates/footer.html'); ?>
