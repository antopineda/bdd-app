<?php
function createTrigger($db){

    // Eliminar cualquier trigger existente con el mismo nombre
    $dropTriggerQuery = "DROP TRIGGER IF EXISTS trigger_calcular_calificacion_after_insert ON historial;";
    $db->exec($dropTriggerQuery);

    // Crear el nuevo trigger y función
    $createTriggerQuery = "
    DROP FUNCTION IF EXISTS calcular_calificacion_after_insert;

    CREATE OR REPLACE FUNCTION calcular_calificacion_after_insert()
    RETURNS TRIGGER AS $$
    DECLARE
        new_cal VARCHAR(10);
        nota FLOAT;
    BEGIN
        -- Asumiendo que NEW.nota ya tiene el valor correcto de la nota después de aplicar las reglas de negocio
        nota := NEW.nota;

        -- Cálculo de calificación basado en la nota
        CASE
            WHEN nota >= 6.6 AND nota <= 7.0 THEN new_cal := 'SO';
            WHEN nota >= 6.0 AND nota < 6.6 THEN new_cal := 'MB';
            WHEN nota >= 5.0 AND nota < 6.0 THEN new_cal := 'B';
            WHEN nota >= 4.0 AND nota < 5.0 THEN new_cal := 'SU';
            WHEN nota >= 3.0 AND nota < 4.0 THEN new_cal := 'I';
            WHEN nota >= 2.0 AND nota < 3.0 THEN new_cal := 'M';
            WHEN nota >= 1.0 AND nota < 2.0 THEN new_cal := 'MM';
            WHEN nota IS NULL THEN new_cal := 'P';
            WHEN nota = 'NP' THEN new_cal := 'NP';
            WHEN nota = 'EX' THEN new_cal := 'A';
            WHEN nota = 'A' THEN new_cal := 'A';
            WHEN nota = 'R' THEN new_cal := 'R';
            ELSE
                RAISE EXCEPTION 'Nota no reconocida: %', nota;
        END CASE;

        UPDATE historial
        SET calificacion = new_cal
        WHERE historial.num_alumno = NEW.num_alumno AND historial.calificacion IS NULL;

        RETURN NEW;
    END;
    $$ LANGUAGE plpgsql;

    CREATE TRIGGER trigger_calcular_calificacion_after_insert
    AFTER INSERT ON historial
    FOR EACH ROW
    EXECUTE FUNCTION calcular_calificacion_after_insert();
    ";
    $db->exec($createTriggerQuery);
}

?>
