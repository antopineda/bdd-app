<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles/login.css">
    <!-- Bootstrap(CSS), Jquery (javascripts), etc... -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        "background-color: #F0E9FB;"
    </style>
</head>
<body>
    <div class="login">
    <h2 class="title">Iniciar sesión</h2>
    <form class="login-form" method="POST" action="consultas/consulta_login.php">
        <label class="form-label" for="email">Correo electrónico:</label>
        <input class="form-input" type="email" id="email" name="email" required>
        
        <label class="form-label" for="password">Contraseña:</label>
        <input class="form-input" type="password" id="password" name="password" required>
        
        <button class="form-button" type="submit">Ingresar</button>
    </form>
    </div>
</body>
</html>