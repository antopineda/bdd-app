<?php
function createTrigger($db){

    $dropTriggerQuery = "DROP TRIGGER IF EXISTS trigger_calcular_calificacion_after_insert ON historial;";
    $db->exec($dropTriggerQuery);

    $createTriggerQuery = "
    DROP TRIGGER IF EXISTS trigger_calcular_calificacion_after_insert ON historial;
    DROP FUNCTION IF EXISTS calcular_calificacion_after_insert;
    
    CREATE OR REPLACE FUNCTION calcular_calificacion_after_insert()
    RETURNS TRIGGER AS $$
    DECLARE
        calificacion VARCHAR(10);
        nota_final FLOAT;
    BEGIN
        -- Asumiendo que NEW.nota_final ya tiene el valor correcto de la nota final después de aplicar las reglas de negocio
        nota_final := NEW.nota_final;

        -- Cálculo de calificación basado en la nota final
        CASE
            WHEN nota_final >= 6.6 AND nota_final <= 7.0 THEN calificacion := 'SO';
            WHEN nota_final >= 6.0 AND nota_final < 6.6 THEN calificacion := 'MB';
            WHEN nota_final >= 5.0 AND nota_final < 6.0 THEN calificacion := 'B';
            WHEN nota_final >= 4.0 AND nota_final < 5.0 THEN calificacion := 'SU';
            WHEN nota_final >= 3.0 AND nota_final < 4.0 THEN calificacion := 'I';
            WHEN nota_final >= 2.0 AND nota_final < 3.0 THEN calificacion := 'M';
            WHEN nota_final >= 1.0 AND nota_final < 2.0 THEN calificacion := 'MM';
            WHEN nota_final IS NULL THEN calificacion := 'P';
            WHEN nota_final = 'NP' THEN calificacion := 'NP';
            WHEN nota_final = 'EX' THEN calificacion := 'A';
            WHEN nota_final = 'A' THEN calificacion := 'A';
            WHEN nota_final = 'R' THEN calificacion := 'R';
            ELSE
                RAISE EXCEPTION 'Nota final no reconocida: %', nota_final;
        END CASE;

        -- Actualizar la calificación en la tabla historial
        UPDATE historial
        SET calificacion = $1.calificacion -- Aquí usamos $1 para referirnos a NEW
        WHERE historial.num_alumno = NEW.numero_alumno AND historial.calificacion IS NULL;

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
