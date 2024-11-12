<!-- <?php
// Para la Interfaz:
$conn = new mysqli("localhost", "user", "password", "database");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $atributos = $conn->real_escape_string($_POST['atributos']);
    $tablas = $conn->real_escape_string($_POST['tablas']);
    $condicion = $conn->real_escape_string($_POST['condicion']);

    // Lista de tablas y columnas válidas (ajusta según tu base de datos)
    $tablas_validas = ['tabla1', 'tabla2']; // Ejemplo
    $atributos_validos = ['columna1', 'columna2']; // Ejemplo

    // Validar entrada
    if (in_array($tablas, $tablas_validas) && in_array($atributos, $atributos_validos)) {
        $query = "SELECT $atributos FROM $tablas";
        if (!empty($condicion)) {
            $query .= " WHERE $condicion";
        }

        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo implode(", ", $row) . "<br>";
            }
        } else {
            echo "No se encontraron resultados.";
        }
    } else {
        echo "Error: atributos o tablas no válidos.";
    }
}
?> -->