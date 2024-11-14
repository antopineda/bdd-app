<?php
include('../../config/conexion.php');
include('../../cargador/parametros_tablas.php');
include('../../templates/header.html');

$tablas_validas = ['administrativo', 'asignaturas', 'estudiantes', 'historial', 'planeacion', 'planes', 'prerequisitos', 'personas', 'usuarios'];
$atributos_validos = [
    "*", "codigo_plan", "asignatura_id", "asignatura", "nivel", "carrera", "cohorte", "num_alumno",
    "bloqueo", "causal_bloqueo", "run", "dv", "nombre_1", "nombre_2", "apellido_1", "apellido_2",
    "logro", "fecha_logro", "ultima_carga", "periodo", "codigo_asignatura", "convocatoria",
    "calificacion", "nota", "facultad", "nombre_plan", "jornada", "sede", "grado", "modalidad",
    "inicio_vigencia", "prerequisito_1", "prerequisito_2", "nombre", "apellido", "email_institucional",
    "dedicacion", "contrato", "jerarquia", "cargo", "estamento", "departamento", "id_depto",
    "nombre_asignatura", "seccion", "duracion", "vacantes", "inscritos", "dia", "hora_inicio",
    "hora_fin", "fecha_inicio", "fecha_fin", "lugar", "edificio", "profesor_principal",
    "profesor_run", "email", "telefono", "numero_alumno", "curso", "nota_final", "password", "role"
];

echo "<h2>Resultados Consulta A-T-C</h2>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y validar atributos, tabla y condición
    $atributos = explode(',', str_replace(' ', '', $_POST['atributos']));
    $tabla = $_POST['tabla'];
    $condicion = $_POST['condicion'];

    // Validar tabla
    if (!in_array($tabla, $tablas_validas)) {
        echo "Error: tabla no válida. Recuerde que debe colocar solo una. Las tablas válidas son: " . implode(", ", $tablas_validas);
        exit;
    }

    // Validar atributos
    foreach ($atributos as $atributo) {
        if (!in_array($atributo, $atributos_validos)) {
            echo "Error: uno o más atributos no son válidos. Recuerde separarlos por ',' Los atributos válidos son: " . implode(", ", $atributos_validos);
            exit;
        }
    }

    // Construir la consulta SQL
    $atributos_sql = implode(', ', $atributos);
    $query = "SELECT $atributos_sql FROM $tabla";
    if (!empty($condicion)) {
        $query .= " WHERE $condicion";
    }

    // Ejecutar la consulta de forma segura
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            foreach ($result as $row) {
                echo implode(", ", $row) . "<br>";
            }
        } else {
            echo "No se encontraron resultados.";
        }
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
    }
}
include('../templates/footer.html'); 
?>

