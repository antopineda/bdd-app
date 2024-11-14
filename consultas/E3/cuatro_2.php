<!-- 
Para la tabla temporal acta
CREATE TEMPORARY TABLE acta (
    numero_alumno INT,
    curso VARCHAR(50),
    periodo VARCHAR(10),
    nombre_estudiante VARCHAR(100),
    nombre_profesor VARCHAR(100),
    nota_oportunidad1 DECIMAL(3, 2),
    nota_oportunidad2 DECIMAL(3, 2)
);







Para la vista
CREATE VIEW ActaNotas AS
SELECT 
    numero_alumno,
    curso,
    periodo,
    nombre_estudiante,
    nombre_profesor,
    CASE 
        WHEN nota_oportunidad1 IS NOT NULL AND nota_oportunidad2 IS NOT NULL THEN 
            (nota_oportunidad1 + nota_oportunidad2) / 2  -- Aquí aplicarías la regla de negocio específica
        WHEN nota_oportunidad1 IS NOT NULL THEN nota_oportunidad1
        WHEN nota_oportunidad2 IS NOT NULL THEN nota_oportunidad2
        ELSE NULL 
    END AS nota_final
FROM acta;





Para el Stored Procedure:
DELIMITER //
CREATE PROCEDURE cargar_y_validar_acta()
BEGIN
    DECLARE error_msg VARCHAR(255);
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION 
        SET error_msg = 'Error al cargar los datos en acta. Revise los valores y vuelva a intentarlo.';

    START TRANSACTION;

    -- Validación de que curso, alumno y profesor existan
    IF NOT EXISTS (SELECT 1 FROM Cursos WHERE curso = (SELECT curso FROM acta LIMIT 1)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El curso especificado no existe.';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM Alumnos WHERE numero_alumno = (SELECT numero_alumno FROM acta LIMIT 1)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El alumno especificado no existe.';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM Profesores WHERE nombre_profesor = (SELECT nombre_profesor FROM acta LIMIT 1)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El profesor especificado no existe.';
    END IF;

    -- Validación de rango de notas
    IF EXISTS (SELECT 1 FROM acta WHERE nota_oportunidad1 < 1 OR nota_oportunidad1 > 7 OR nota_oportunidad2 < 1 OR nota_oportunidad2 > 7) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una o más notas están fuera del rango permitido (1.0 a 7.0).';
    END IF;

    -- Si no hubo errores, completar la transacción
    COMMIT;

    -- Mensaje de éxito
    SELECT 'Datos cargados exitosamente en la vista ActaNotas' AS resultado;
END //

DELIMITER ; 





-->
