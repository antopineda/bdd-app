<?php include('../templates/header.html'); ?>

<body>
    <?php
    require("../config/conexion.php");

    // Obtener el número de alumno desde el formulario
    $num_alumno = $_POST["num_alumno"];

    // Verificar si el estudiante está vigente
    $check_vigente_query = "SELECT COUNT(*) FROM historial WHERE num_alumno = '$num_alumno' AND periodo = '2024-02';";
    $check_vigente_result = $db->query($check_vigente_query);
    $vigente = $check_vigente_result->fetchColumn();

    if ($vigente == 0) {
        echo "<h2>Error: El estudiante no está vigente para el periodo 2024-02</h2>";
    } else {
        echo "<h2>Propuesta de asignaturas para el próximo semestre</h2>";
        echo "<p>Estudiante: $num_alumno</p>";

        // Consultar el plan
        $plan_query = "SELECT codigo_plan FROM historial WHERE num_alumno = '$num_alumno' LIMIT 1;";
        $plan_result = $db->query($plan_query);
        $plan = $plan_result->fetchColumn();
        echo "<p>Plan del estudiante: $plan</p>";

        // Consulta de las asignaturas a recomendar
        $query = "
            SELECT a.asignatura_id, a.asignatura
            FROM asignaturas a
            WHERE a.codigo_plan = (
                SELECT codigo_plan FROM historial WHERE num_alumno = '$num_alumno' LIMIT 1
            )
            AND a.asignatura_id NOT IN (
                SELECT h.codigo_asignatura 
                FROM historial h
                WHERE h.num_alumno = '$num_alumno' 
                AND h.nota >= 4.0 -- Ya aprobados
            )
            AND NOT EXISTS (
                SELECT 1 
                FROM prerequisitos p
                WHERE p.asignatura_id = a.asignatura_id
                AND (
                    p.prerequisito_1 NOT IN (
                        SELECT h.codigo_asignatura
                        FROM historial h
                        WHERE h.num_alumno = '$num_alumno'
                        AND h.nota >= 4.0
                    ) OR
                    p.prerequisito_2 NOT IN (
                        SELECT h.codigo_asignatura
                        FROM historial h
                        WHERE h.num_alumno = '$num_alumno'
                        AND h.nota >= 4.0
                    )
                )
            );
        ";

        $result = $db->query($query);
        $asignaturas = $result->fetchAll();

        // Mostrar resultados
        if ($asignaturas) {
            // Mostrar primeras 5 asignaturas
            $asignaturas_5 = array_slice($asignaturas, 0, 5);
            echo "<h3>Asignaturas recomendadas para el próximo semestre</h3>";
            echo "<table class='styled-table'>
                    <tr>
                        <th>Código de Asignatura</th>
                        <th>Asignatura</th>
                    </tr>";
            foreach ($asignaturas_5 as $a) {
                echo "<tr><td>{$a['asignatura_id']}</td><td>{$a['asignatura']}</td></tr>";
            }
            echo "</table>";

            // Mostrar el resto de las asignaturas
            echo "<h3>Todas las asignaturas que podría tomar</h3>";

            echo "<table class='styled-table'>
                    <tr>
                        <th>Código de Asignatura</th>
                        <th>Asignatura</th>
                    </tr>";
            foreach ($asignaturas as $a) {
                echo "<tr><td>{$a['asignatura_id']}</td><td>{$a['asignatura']}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay asignaturas recomendadas para este estudiante.</p>";
        }
    }
    ?>

</body>

<?php include('../templates/footer.html'); ?>
