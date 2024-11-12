<?php 
// 
// Configuración de base de datos 

// $conn = new mysqli("localhost", "user", "password", "database"); 
$conn = pg_connect("dbname=grupoXe3; host=localhost user=grupoXe3 password=<su contraseña de grupo>"); 

// Inicia la transacción 

$conn->begin_transaction(); 


// Cargar el archivo CSV 

$csvFile = fopen('notas adivinacion I.csv', 'r'); 

 

// Saltar encabezado 

fgetcsv($csvFile); 

 

// Leer cada fila del archivo 

while (($row = fgetcsv($csvFile)) !== false) { 

    $numero_alumno = $row[0]; 

    $curso = $row[1]; 

    $periodo = $row[2]; 

    $nota1 = $row[3]; 

    $nota2 = $row[4];

 

    // Validar nota 

    if ($nota1 > 4.0 && $nota2 !== null) { 

        echo "Error: nota de $numero_alumno contiene un valor erróneo. Corríjalo manualmente en el archivo de origen y vuelva a cargar."; 

        $conn->rollback(); // Abortar transacción 

        exit; 

    } 

 

    // Insertar en la tabla temporal 

    $sql = "INSERT INTO acta (numero_alumno, curso, periodo, nota) VALUES (?, ?, ?, ?)"; 

    $stmt = $conn->prepare($sql); 

    $stmt->bind_param("issd", $numero_alumno, $curso, $periodo, $nota); 

    $stmt->execute(); 

} 

 

// Commit si todo está bien 

$conn->commit(); 

fclose($csvFile); 


// Para tabla temporal en sql

// CREATE TEMPORARY TABLE acta (
//     numero_alumno INT,
//     curso VARCHAR(50),
//     periodo VARCHAR(10),
//     nombre_estudiante VARCHAR(100),
//     nombre_profesor VARCHAR(100),
//     nota_oportunidad1 DECIMAL(3, 2),
//     nota_oportunidad2 DECIMAL(3, 2)
// );

?> 


 

 

 
