<?php
function insertar_en_tabla($database, $tabla, $fila){
    try {
        $fila = array_map(function($value) {
            $value = trim($value, '"');
            return $value === '' ? null : $value; // Convertir cadenas vacías en NULL
        }, $fila);

        $valores = array_values($fila);

        $database->beginTransaction();

        $placeholders = implode(',', array_fill(0, count($valores), '?')); 

        $stmt = $database->prepare("INSERT INTO $tabla VALUES ($placeholders);");
        
        $stmt->execute($valores);

        $database->commit();

    } catch (PDOException $e) { // Capturar específicamente excepciones de PDO
        // Revertir la transacción en caso de error
        $database->rollBack();

        // Obtener el mensaje de error
        $errorMessage = $e->getMessage();
        // echo "Error al insertar en la tabla $tabla: ";
        // print_r($fila);
        // echo "\nMensaje de error: $errorMessage\n";

        // Manejo de errores específicos, como clave primaria duplicada
        // if (strpos($errorMessage, 'Duplicate entry') !== false || strpos($errorMessage, 'Unique violation') !== false) {
        //     // Detecta si el error es por una clave primaria o única duplicada
        //     echo "Advertencia: La fila contiene una tupla duplicada y no fue insertada.\n";
        // } 
        // elseif (strpos($errorMessage, 'Data too long') !== false) {
        //     guardar_error_en_archivo('errores_valores_largos.csv', $fila);
        // } 
        
        // else {
        //     // Otros errores
        //     echo "Error general al insertar en la tabla $tabla: " . $e->getMessage() . "\n";
        // }
    } 


}
?>
