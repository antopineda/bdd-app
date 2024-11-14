<!-- Para el Trigger
CREATE TRIGGER calcular_calificacion 

AFTER INSERT ON acta 

FOR EACH ROW 

BEGIN 

    UPDATE Notas 

    SET calificacion = CASE 

        WHEN NEW.nota >= 7 THEN 'Aprobado' 

        ELSE 'Reprobado' 

    END 

    WHERE alumno = NEW.alumno; 

END;  -->