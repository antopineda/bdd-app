<¿<?php
// Inicia sesión
session_start();

// Verifica si el usuario tiene acceso
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// Conecta a la base de datos
require('../config/conexion.php');
?>
