<?php

include('../config/conexion.php');

$email = $_POST['email'];
$password = $_POST['password']; // Obtener la contraseña
$role = $_POST['role'];

// Hashear la contraseña
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Verificar si ya está en la tabla usuarios
$query = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
$query->bindParam(':email', $email);
$query->execute();

// Verificar si está en la tabla academico o administrativo
$queryAcademico = $db->prepare("SELECT * FROM academico WHERE email_institucional = :email");
$queryAcademico->bindParam(':email', $email);
$queryAcademico->execute();

$queryAdministrativo = $db->prepare("SELECT * FROM administrativo WHERE email_institucional = :email");
$queryAdministrativo->bindParam(':email', $email);
$queryAdministrativo->execute();

// Verificar si el usuario ya está registrado o si no pertenece a academico o administrativo
if ($query->rowCount() > 0) {
    echo "<p align='center'>El usuario ya está registrado en el sistema.</p>";
} elseif ($queryAcademico->rowCount() == 0 && $queryAdministrativo->rowCount() == 0) {
    echo "<p align='center'>El correo no pertenece a un académico o administrativo autorizado.</p>";
} else {
    // Si el correo pertenece a un académico o administrativo, insertar en la tabla usuarios
    $query = $db->prepare("INSERT INTO usuarios (email, password, role) VALUES (:email, :password, :role)");
    $query->bindParam(':email', $email);
    $query->bindParam(':password', $hashedPassword); // Usar la contraseña hasheada
    $query->bindParam(':role', $role);
    
    if ($query->execute()) {
        echo "<p align='center'>Usuario registrado con éxito.</p>";
    } else {
        echo "<p align='center'>Hubo un error al registrar el usuario. Intente nuevamente.</p>";
    }
}

?>

<?php include('../templates/footer_admin.html'); ?>
