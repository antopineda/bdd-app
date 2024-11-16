<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Panel - Acta de Notas</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h1>Panel de Usuario</h1>

    <?php


    include('../../config/conexion.php');
    include('trigger.php');

    // Inicia sesión
    session_start();

    // Verifica si el usuario tiene acceso
    if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'user') {
        header("Location: ../index.php");
        exit();
    }

    function nullify($value) {
        return $value == '' ? null : $value;
    }

    try {
        // STORED PROCEDURE
        $createProcedureQuery = "
            CREATE OR REPLACE FUNCTION generar_acta_notas()
            RETURNS void AS $$
            BEGIN
                -- Validar que el curso exista
                IF NOT EXISTS (SELECT 1 FROM asignaturas WHERE asignatura_id = (SELECT curso FROM acta LIMIT 1)) THEN
                    RAISE EXCEPTION 'El curso especificado no existe.';
                END IF;

                -- Validar que el profesor exista
                IF NOT EXISTS (SELECT 1 FROM personas WHERE run = '46423089') THEN
                    RAISE EXCEPTION 'El profesor con RUN 46423089 no existe.';
                END IF;

                -- Validar que cada estudiante exista
                IF EXISTS (
                    SELECT 1
                    FROM acta a
                    WHERE NOT EXISTS (SELECT 1 FROM personas p WHERE p.run = a.run)
                ) THEN
                    RAISE EXCEPTION 'Existen estudiantes en el acta con RUN no válidos.';
                END IF;
                
                -- Validar que las notas estén en el rango correcto
                IF EXISTS (
                    SELECT 1
                    FROM acta
                    WHERE nota_final::NUMERIC < 1.0 OR nota_final::NUMERIC > 7.0
                ) THEN
                    RAISE EXCEPTION 'Existen notas fuera del rango permitido (1.0 a 7.0) en el acta.';
                END IF;
                

                -- Crear la vista para el acta de notas
                CREATE OR REPLACE VIEW vista_acta_notas AS
                SELECT 
                    a.numero_alumno,
                    a.curso,
                    a.periodo,
                    p.nombre || ' ' || p.apellido AS nombre_estudiante,
                    (SELECT nombre || ' ' || apellido FROM personas WHERE run = '46423089') AS nombre_profesor,
                    a.nota_final
                FROM acta a
                JOIN personas p ON a.run = p.run;
            END;
            $$ LANGUAGE plpgsql;
        ";
        $db->exec($createProcedureQuery);

        // Iniciar la transacción
        $db->beginTransaction();

        // Crear la TABLA TEMPORAL 'acta'
        $createTableQuery = "
            CREATE TEMP TABLE acta (
                numero_alumno VARCHAR(50),
                run VARCHAR(40),
                curso VARCHAR(50),
                periodo VARCHAR(10),
                seccion VARCHAR(100),
                nota_final VARCHAR(50),
                convocatoria VARCHAR(20),
                codigo_plan VARCHAR(3),
                calificacion VARCHAR(5)
            )
        ";
        $db->exec($createTableQuery);

        // Cargar el archivo CSV
        $csvFile = fopen('../../data/adivinacion.csv', 'r');
        if ($csvFile === false) {
            throw new Exception("No se pudo abrir el archivo CSV.");
        }

        // Saltar el encabezado
        fgets($csvFile);

        // Leer y procesar cada fila del archivo CSV
        $contador = 0;
        while (($row = fgets($csvFile)) !== false && $contador < 38) {
            $row = rtrim($row);
            $row = explode(';', $row);
            $numero_alumno = $row[0];
            $run = $row[1];
            $curso = $row[2];
            $seccion = $row[3];
            $periodo = $row[4];
            $nota1 = nullify($row[5]);
            $nota2 = nullify($row[6]);
            $convocatoria = 'dic';
            $codigo_plan = 'GH1';
            $calificacion = null;

            // Validar regla de negocio específica: si nota1 es aprobatoria, no debería existir nota2
            if ($nota1 !== "NP" && $nota1 !== "P") {
                if ($nota1 >= 4.0 && !is_null($nota2)) {
                    throw new Exception("Error: Estudiante $numero_alumno aprobó en la primera oportunidad con nota $nota1 y aún así rindió una segunda vez, con nota $nota2. Corríjalo en el archivo.");
                }
            }

            // Determinación de la nota final
            if ($nota1 !== "NP" && $nota1 !== "P") {
                if ($nota1 < 4.0) {
                    // Si la nota1 es reprobatoria y hay una nota2, se toma la nota2 como final
                    $nota_final = !is_null($nota2) ? $nota2 : $nota1;
                    $convocatoria = 'mar';
                }
            } elseif ($nota1 == "NP" && !is_null($nota2)) {
                $nota_final = $nota2;
            } else {
                // Si la nota1 es aprobatoria, la nota final es nota1 y no debe haber nota2
                $nota_final = $nota1;
            }

            // Preparar e insertar el registro en la tabla temporal 'acta'
            $insertQuery = "INSERT INTO acta (numero_alumno, run, curso, periodo, seccion, nota_final, convocatoria, codigo_plan, calificacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($insertQuery);
            $stmt->execute([$numero_alumno, $run, $curso, $periodo, $seccion, $nota_final, $convocatoria, $codigo_plan, $calificacion]);

            $contador++;
        }

        echo "ANTES DE GENERAR ACTA NOTAS.";

        // Ejecutar el Stored Procedure para generar la vista
        $callProcedureQuery = "SELECT generar_acta_notas();"; 
        $db->exec($callProcedureQuery);

        echo "DESPUES DE GENERAR ACTA NOTAS Y ANTES DE GUARDAR LA VISTA.";

        // Guardar la vista (tabla) generada (4.2)
        $selectViewQuery = "SELECT * FROM vista_acta_notas";
        $result = $db->query($selectViewQuery);

        echo "DESPUES DE GUARDAR LA VISTA EN RESULT.";

        // Mostrar los datos en una tabla HTML
        echo '<table>';
        echo '<tr>';
        echo '<th>Número Alumno</th>';
        echo '<th>Curso</th>';
        echo '<th>Periodo</th>';
        echo '<th>Nombre Estudiante</th>';
        echo '<th>Nombre Profesor</th>';
        echo '<th>Nota Final</th>';
        echo '</tr>';

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['numero_alumno']) . '</td>';
            echo '<td>' . htmlspecialchars($row['curso']) . '</td>';
            echo '<td>' . htmlspecialchars($row['periodo']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_estudiante']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_profesor']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nota_final']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo "ANTES DEL TRIGGER.";

        // Crear el trigger si no existe ya (opcional, pero útil si no quieres duplicar triggers)
        createTrigger($db); # la funcion hace el db exec 

        echo "DESPUES DEL TRIGGER.";

        // Verificar si el trigger está definido, dsp saquemosla
        // $checkTriggerQuery = "SELECT 1 FROM pg_trigger WHERE tgname = 'trigger_calcular_calificacion_after_insert' AND tgrelid = 'historial'::regclass::oid";
        // $checkTrigger = $db->query($checkTriggerQuery);
        
        // if (!$checkTrigger->fetchColumn()) {
        //     throw new Exception("El trigger 'trigger_calcular_calificacion_after_insert' no está definido o no está habilitado para la tabla 'historial'.");
        // }

        // cargar las notas de la tabla temporal "acta" a la tabla de notas general "historial"
        $insertIntoHistorialQuery = "
            INSERT INTO historial (run, codigo_plan, num_alumno, periodo, codigo_asignatura, convocatoria, calificacion, nota)
            SELECT run, codigo_plan, numero_alumno, periodo, curso, convocatoria, calificacion, nota_final
            FROM acta
        ";
        $db->exec($insertIntoHistorialQuery);

        // // Ejecutar el Stored Procedure para generar la vista
        // $callProcedureQuery = "SELECT generar_acta_notas();"; 
        // $db->exec($callProcedureQuery);


        // // Guardar la vista (tabla) generada (4.2)
        // $selectViewQuery = "SELECT * FROM vista_acta_notas";
        // $result = $db->query($selectViewQuery);

        // Confirmar la transacción si todo está bien
        $db->commit();
        fclose($csvFile);

        echo "Importación completada exitosamente.";


        // El trigger se ejecutará automáticamente al insertar en la tabla historial

    } catch (Exception $e) {
        // Rollback en caso de error y mostrar mensaje
        $db->rollBack();
        if (isset($csvFile)) fclose($csvFile);
        echo $e->getMessage();
    }
    ?>

</body>
</html>
