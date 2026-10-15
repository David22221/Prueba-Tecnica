CREATE OR REPLACE FUNCTION registrar_producto(
    p_codigo      VARCHAR,
    p_nombre      VARCHAR,
    p_bodega_id   INT,
    p_sucursal_id INT,
    p_moneda_id   INT,
    p_precio      NUMERIC,
    p_descripcion TEXT,
    p_materiales  INT[]
) RETURNS INT
LANGUAGE plpgsql
AS $$
DECLARE
    v_id INT;
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM sucursal
        WHERE id = p_sucursal_id AND bodega_id = p_bodega_id
    ) THEN
        RAISE EXCEPTION 'La sucursal no pertenece a la bodega seleccionada.'
            USING ERRCODE = 'P0001';
    END IF;

    INSERT INTO producto
        (codigo, nombre, bodega_id, sucursal_id, moneda_id, precio, descripcion)
    VALUES
        (p_codigo, p_nombre, p_bodega_id, p_sucursal_id, p_moneda_id, p_precio, p_descripcion)
    RETURNING id INTO v_id;

    INSERT INTO producto_material (producto_id, material_id)
    SELECT v_id, UNNEST(p_materiales);

    RETURN v_id;
END;
$$;