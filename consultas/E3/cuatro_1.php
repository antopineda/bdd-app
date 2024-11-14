<?php
include('../../config/conexion.php');

function nullify($value) {
    return $value == '' ? null : $value;
}

try {
    // Iniciar la transacción
    $db->beginTransaction();

    // Crear la tabla temporal 'acta'
    $createTableQuery = "
        CREATE TEMP TABLE acta (
            numero_alumno VARCHAR(50),
            run VARCHAR(40),
            curso VARCHAR(50),
            periodo VARCHAR(10),
            seccion VARCHAR(100),
            nota_final VARCHAR(50)
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
            }
        } elseif ($nota1 == "NP" && !is_null($nota2)) {
            $nota_final = $nota2;
        } else {
            // Si la nota1 es aprobatoria, la nota final es nota1 y no debe haber nota2
            $nota_final = $nota1;
        }

        // Preparar e insertar el registro en la tabla temporal 'acta'
        $insertQuery = "INSERT INTO acta (numero_alumno, run, curso, periodo, seccion, nota_final) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insertQuery);
        $stmt->execute([$numero_alumno, $run, $curso, $periodo, $seccion, $nota_final]);

        $contador++;
    }
    echo "4";
    // Confirmar la transacción si todo está bien
    $db->commit();
    fclose($csvFile);

    echo "Importación completada exitosamente.";

} catch (Exception $e) {
    // Rollback en caso de error y mostrar mensaje
    $db->rollBack();
    if (isset($csvFile)) fclose($csvFile);
    echo $e->getMessage();
}
?>
