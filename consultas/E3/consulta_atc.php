<?php
include('../../config/conexion.php');
include('../../templates/header.html');

// Función para verificar si una tabla existe en la base de datos
function validarTabla($db, $tabla) {
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.tables 
            WHERE table_name = :tabla
        ");
        $stmt->bindParam(':tabla', strtolower($tabla), PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Función para obtener los atributos válidos de una tabla
function obtenerAtributosValidos($db, $tabla) {
    try {
        $stmt = $db->prepare("
            SELECT column_name
            FROM information_schema.columns
            WHERE table_name = :tabla
        ");
        $stmt->bindParam(':tabla', strtolower($tabla), PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

// Función para validar la sintaxis básica de la condición
function validarCondicion($condicion) {
    // Comprobación muy básica; puedes usar un parser de SQL para algo más avanzado
    return preg_match('/^[a-zA-Z0-9_\.]+(\s*=\s*[^\s]+)?(\s+(AND|OR)\s+[a-zA-Z0-9_\.]+\s*=\s*[^\s]+)*$/i', $condicion);
}

// Función para manejar errores
function mostrarError($mensaje) {
    echo "<div class='alert alert-danger'>$mensaje</div>";
    include('../templates/footer.html');
    exit;
}

echo "<h2 style='text-align: center;'>Resultados Consulta A-T-C</h2>";
echo "<div class='container'>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $atributos = explode(',', str_replace(' ', '', $_POST['atributos']));
    $tablas = explode(',', str_replace(' ', '', $_POST['tabla']));
    $condicion = trim($_POST['condicion']);

    // Validar tablas
    foreach ($tablas as $tabla) {
        if (!validarTabla($db, $tabla)) {
            mostrarError("Error: La tabla '$tabla' no existe en la base de datos.");
        }
    }

    // Validar atributos
    foreach ($tablas as $tabla) {
        $atributos_validos = obtenerAtributosValidos($db, $tabla);
        if (empty($atributos_validos)) {
            mostrarError("Error: No se pudieron obtener los atributos de la tabla '$tabla'.");
        }
        foreach ($atributos as $atributo) {
            if (count(explode('.', $atributo)) > 1) {
                list($tabla_alias, $atributo_sin_alias) = explode('.', $atributo);
                $atributos_validos = obtenerAtributosValidos($db, $tabla_alias);
                if ($atributo_sin_alias == "password") {
                    mostrarError("Error: No se pueden seleccionar atributos de tipo 'password'.");
                }
                if (!in_array($tabla_alias, $tablas)) {
                    mostrarError("Error: La tabla alias '$tabla_alias' no es válida.");
                }
                if ($atributo_sin_alias !== "*" && !in_array($atributo_sin_alias, $atributos_validos)) {
                    mostrarError("Error: El atributo '$atributo_sin_alias' no es válido en la tabla '$tabla_alias'.");
                }
            } else {
                $atributo_valido = false;
                foreach ($tablas as $tabla_validar) {
                    $atributos_validos = obtenerAtributosValidos($db, $tabla_validar);
                    if (in_array($atributo, $atributos_validos)) {
                        $atributo_valido = true;
                        break;
                    }
                }
                if ($atributo !== "*" && !$atributo_valido) {
                    mostrarError("Error: El atributo '$atributo' no es válido en ninguna de las tablas especificadas.");
                } elseif ($atributo == "password") {
                    mostrarError("Error: No se pueden seleccionar atributos de tipo 'password'.");
                }
            }
        }
    }

    // Validar condición
    if (!empty($condicion) && !validarCondicion($condicion)) {
        mostrarError("Error: La condición '$condicion' tiene una sintaxis inválida.");
    }

    // Construir la consulta SQL
    $atributos_sql = implode(', ', $atributos);
    $tablas_sql = implode(', ', $tablas);
    $query = "SELECT $atributos_sql FROM $tablas_sql";
    if (!empty($condicion)) {
        $query .= " WHERE $condicion";
    }

    // Ejecutar la consulta
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            echo "<table class='table table-striped'>";
            echo "<thead><tr>";
            foreach (array_keys($result[0]) as $columna) {
                echo "<th>$columna</th>";
            }
            echo "</tr></thead><tbody>";
            foreach ($result as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>$value</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<div class='alert alert-info'>No se encontraron resultados.</div>";
        }
    } catch (PDOException $e) {
        mostrarError("Error en la consulta: " . htmlspecialchars($e->getMessage()));
    }
}
echo "</div>";
include('../templates/footer.html');
?>
