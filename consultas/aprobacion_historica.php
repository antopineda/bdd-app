<?php include('../templates/header.html'); ?>

<body>
    <?php
    require("../config/conexion.php");

    $codigo = $_POST["codigo"];
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

    $result = $db->prepare($query);
    $result->bindParam(':codigo_asignatura', $codigo);
    $result->execute();
    $profesores = $result->fetchAll();
    ?>

    <table class="styled-table">
        <tr>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Porcentaje de Aprobación</th>
        </tr>
        <?php
        foreach ($profesores as $profesor) {
            echo "<tr><td>$profesor[0]</td><td>$profesor[1]</td><td>$profesor[2]%</td></tr>";
        }
        ?>
    </table>
</body>

<?php include('../templates/footer.html'); ?>
