CREATE TABLE bodega (
    id     SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE sucursal (
    id        SERIAL PRIMARY KEY,
    bodega_id INT NOT NULL REFERENCES bodega(id),
    nombre    VARCHAR(50) NOT NULL,
    UNIQUE (bodega_id, nombre)
);

CREATE TABLE moneda (
    id     SERIAL PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE material (
    id     SERIAL PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE producto (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(15)   NOT NULL UNIQUE,
    nombre      VARCHAR(50)   NOT NULL,
    bodega_id   INT NOT NULL REFERENCES bodega(id),
    sucursal_id INT NOT NULL REFERENCES sucursal(id),
    moneda_id   INT NOT NULL REFERENCES moneda(id),
    precio      NUMERIC(15,2) NOT NULL CHECK (precio > 0),
    descripcion TEXT NOT NULL
        CHECK (char_length(descripcion) BETWEEN 10 AND 1000),
    fecha   TIMESTAMP NOT NULL DEFAULT now()
);

CREATE TABLE producto_material (
    producto_id INT NOT NULL REFERENCES producto(id) ON DELETE CASCADE,
    material_id INT NOT NULL REFERENCES material(id),
    PRIMARY KEY (producto_id, material_id)
);