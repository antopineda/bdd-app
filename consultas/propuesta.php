<?php include('../templates/header.html'); ?>

<body>
    <?php
    require("../config/conexion.php");

    // Obtener el número de alumno desde el formulario
    $num_alumno = $_POST["num_alumno"];

    echo "<h2>Propuesta de asignaturas para el próximo semestre</h2>";

    $query = "
        SELECT 
            a.asignatura_id,
            a.asignatura
        FROM 
            asignaturas a
        WHERE 
            a.codigo_plan = (SELECT codigo_plan FROM estudiantes WHERE num_alumno = :num_alumno)
            AND a.asignatura_id NOT IN (
                SELECT h.codigo_asignatura 
                FROM historial h
                WHERE h.num_alumno = :num_alumno 
                AND h.periodo = '2024-2'
            )
            AND a.asignatura_id NOT IN (
                SELECT prereq.asignatura_id 
                FROM asignaturas prereq
                WHERE prereq.codigo_plan = a.codigo_plan
                AND prereq.nivel <= (
                    SELECT MAX(h.nivel) 
                    FROM historial h 
                    WHERE h.num_alumno = :num_alumno 
                    AND h.periodo = '2024-2'
                )
            );
    ";

    $result = $db -> prepare($query);
    $result -> bindParam(':num_alumno', $num_alumno, PDO::PARAM_STR);
    $result -> execute();
    $asignaturas = $result -> fetchAll();

    ?>

    <table class="styled-table">
        <tr>
            <th>Código de Asignatura</th>
            <th>Asignatura</th>
        </tr>
        <?php
        foreach ($asignaturas as $a) {
            echo "<tr><td>{$a['asignatura_id']}</td><td>{$a['asignatura']}</td></tr>";
        }
        ?>
    </table>
</body>

<?php include('../templates/footer.html'); ?>
