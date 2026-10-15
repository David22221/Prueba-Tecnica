INSERT INTO bodega (nombre) VALUES ('Bodega 1'), ('Bodega 2'), ('Bodega 3');

INSERT INTO sucursal (bodega_id, nombre) VALUES
 (1, 'Sucursal 1'), (1, 'Sucursal 2'),
 (2, 'Sucursal 3'), (2, 'Sucursal 4'),
 (3, 'Sucursal 5');

INSERT INTO moneda (nombre) VALUES ('PESO'), ('DÓLAR'), ('EURO');

INSERT INTO material (nombre) VALUES
 ('Plástico'), ('Metal'), ('Madera'), ('Vidrio'), ('Textil');