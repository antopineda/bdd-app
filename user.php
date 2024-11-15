<?php 
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php"); 
    exit(); 
}

include('templates/header.html'); 
?>

<body>
  <div class="container">
  <h1 class="title">Bananer</h1>
  <p class="description">Aquí podrás hacer tus consultas.</p>

  <h2 class="subtitle">Consulta A-T-C </h2>
  <p class="prompt">Consulta SELECT A FROM T WHERE C (A: atributos, T: tabla, C: condición)</p>
  <form class="form" action="consultas/E3/cuatro_4.php" method="post">
    <input class="form-input" type="text" required placeholder="Ingresa los atributos" name="atributos"> 
    <input class="form-input" type="text" required placeholder="Ingresa la tabla" name="tabla"> 
    <input class="form-input" type="text" required placeholder="Ingresa la condición" name="condicion">
    <br>
    <input class="form-button" type="submit" value="Buscar">
  </form>
  <br>
  <br>

  <h2 class="subtitle">Consultas</h2>

  <p class="prompt">Reporte: Cantidad de estudiantes vigentes dentro y fuera de nivel</p>
  <form class="form" action="consultas/estudiantes_vigentes.php" method="post">
    <input class="form-button" type="submit" value="Buscar">
  </form>
  <br>
  <br>

  <p class="prompt">Reporte: Porcentaje de aprobación periodo</p>
  <form class="form" action="consultas/aprobacion.php" method="post">
    <input class="form-input" type="text" required placeholder="Ingresa un periodo" name="periodo"> 
    <br>
    <input class="form-button" type="submit" value="Buscar">
  </form>
  <br>
  <br>

  <p class="prompt">Reporte: Promedio del porcentajes de aprobación histórico por profesor</p>
  <form class="form" action="consultas/aprobacion_historica.php" method="post">
    <input class="form-input" type="text" required placeholder="Ingresa la sigla de un curso" name="sigla"> 
    <br>
    <input class="form-button" type="submit" value="Buscar">
  </form>
  <br>
  <br>

  <p class="prompt">Reporte de proyección de cursos 2025 para estudiante</p>
  <form class="form" action="consultas/propuesta.php" method="post">
    <input class="form-input" type="text" required placeholder="Ingresa un numero de alumno" name="num_alumno"> 
    <br>
    <input class="form-button" type="submit" value="Buscar">
  </form>
  <br>
  <br>

  <p class="prompt">Historial académico del estudiante</p>
  <form class="form" action="consultas/historial.php" method="post">
    <input class="form-input" type="text" required placeholder="Ingresa un numero de alumno" name="num_alumno"> 
    <br>
    <input class="form-button" type="submit" value="Buscar">
  </form>
  <br>
  <br>

  <p class="prompt">Carga de notas desde el archivo CSV</p>
  <form class="form" action="consultas/consulta_curso.php" method="post">
    <input class="form-input" type="text" required placeholder="Ingresa un nombre de archivo" name="archivo"> 
    <br>
    <input class="form-button" type="submit" value="Buscar">
  </form>
  <br>
  <br>

  <form method="POST" action="consultas/logout.php">
    <button type="submit" class="form-button">Volver a Iniciar Sesión</button>
  </form>
  </div>
</body>
</html>