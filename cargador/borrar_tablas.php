<?php
// Array con los nombres de las tablas que quieres eliminar
$tablas = array('asignaturas', 'estudiantes', 'historial', 'planes', 'prerequisitos', 'academico', 'administrativo', 'oferta', 'personas');

try {
    // Conexión a la base de datos (cambia los parámetros según tu configuración)
    $db = new PDO('pgsql:host=localhost;dbname=grupo28', 'grupo28', 'bddgrupo28');
    
    // Desactivar las restricciones de claves foráneas temporalmente
    $db->exec("SET session_replication_role = 'replica';");

    // Eliminar las tablas en orden
    foreach ($tablas as $tabla) {
        $db->exec("DROP TABLE IF EXISTS $tabla CASCADE;");
        echo "Tabla $tabla eliminada correctamente.<br>";
    }

    // Reactivar las restricciones de claves foráneas
    $db->exec("SET session_replication_role = 'origin';");

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
