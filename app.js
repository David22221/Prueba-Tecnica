'use strict';

const form = document.getElementById('formProducto');
const btnGuardar = document.getElementById('btnGuardar');

const campos = {
    codigo:      document.getElementById('codigo'),
    nombre:      document.getElementById('nombre'),
    bodega:      document.getElementById('bodega'),
    sucursal:    document.getElementById('sucursal'),
    moneda:      document.getElementById('moneda'),
    precio:      document.getElementById('precio'),
    descripcion: document.getElementById('descripcion'),
};

// Al menos una letra y un numero y solo letras y numeros
const REGEX_CODIGO = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]+$/;
// Numero positivo con hasta dos decimales
const REGEX_PRECIO = /^\d+(\.\d{1,2})?$/;


async function pedirJson(url) {
    const respuesta = await fetch(url);
    if (!respuesta.ok) {
        throw new Error('HTTP ' + respuesta.status);
    }
    return respuesta.json();
}

function llenarSelect(select, filas) {
    select.innerHTML = '<option value=""></option>';   
    filas.forEach((fila) => {
        const opcion = document.createElement('option');
        opcion.value = fila.id;
        opcion.textContent = fila.nombre;
        select.appendChild(opcion);
    });
}

async function iniciar() {
    try {
        const [bodegas, monedas] = await Promise.all([
            pedirJson('php/bodegas.php'),
            pedirJson('php/monedas.php'),
        ]);
        llenarSelect(campos.bodega, bodegas);
        llenarSelect(campos.moneda, monedas);
    } catch (error) {
        alert('No se pudieron cargar los datos del formulario.');
    }
}

campos.bodega.addEventListener('change', async () => {
    llenarSelect(campos.sucursal, []);        

    const bodegaId = campos.bodega.value;
    if (bodegaId === '') {
        return;
    }

    try {
        const sucursales = await pedirJson(
            'php/sucursales.php?bodega_id=' + encodeURIComponent(bodegaId)
        );
        llenarSelect(campos.sucursal, sucursales);
    } catch (error) {
        alert('No se pudieron cargar las sucursales.');
    }
});

// validaciones
function fallo(campo, mensaje) {
    alert(mensaje);
    campo.focus();
    return false;
}

function validar() {
    // Codigo
    const codigo = campos.codigo.value.trim();
    campos.codigo.value = codigo;
    if (codigo === '') {
        return fallo(campos.codigo, 'El código del producto no puede estar en blanco.');
    }
    if (!REGEX_CODIGO.test(codigo)) {
        return fallo(campos.codigo, 'El código del producto debe contener letras y números');
    }
    if (codigo.length < 5 || codigo.length > 15) {
        return fallo(campos.codigo, 'El código del producto debe tener entre 5 y 15 caracteres.');
    }

    // Nombre
    const nombre = campos.nombre.value.trim();
    campos.nombre.value = nombre;
    if (nombre === '') {
        return fallo(campos.nombre, 'El nombre del producto no puede estar en blanco.');
    }
    if (nombre.length < 2 || nombre.length > 50) {
        return fallo(campos.nombre, 'El nombre del producto debe tener entre 2 y 50 caracteres.');
    }

    // Bodega
    if (campos.bodega.value === '') {
        return fallo(campos.bodega, 'Debe seleccionar una bodega.');
    }

    // Sucursal
    if (campos.sucursal.value === '') {
        return fallo(campos.sucursal, 'Debe seleccionar una sucursal para la bodega seleccionada.');
    }

    // Moneda
    if (campos.moneda.value === '') {
        return fallo(campos.moneda, 'Debe seleccionar una moneda para el producto.');
    }

    // Precio
    const precio = campos.precio.value.trim();
    campos.precio.value = precio;
    if (precio === '') {
        return fallo(campos.precio, 'El precio del producto no puede estar en blanco.');
    }
    if (!REGEX_PRECIO.test(precio) || parseFloat(precio) <= 0) {
        return fallo(campos.precio, 'El precio del producto debe ser un número positivo con hasta dos decimales.');
    }

    // Materiales
    const marcados = form.querySelectorAll('input[name="materiales[]"]:checked');
    if (marcados.length < 2) {
        return fallo(
            form.querySelector('input[name="materiales[]"]'),
            'Debe seleccionar al menos dos materiales para el producto.'
        );
    }

    // Descripcion
    const descripcion = campos.descripcion.value;
    if (descripcion.trim() === '') {
        return fallo(campos.descripcion, 'La descripción del producto no puede estar en blanco.');
    }
    if (descripcion.length < 10 || descripcion.length > 1000) {
        return fallo(campos.descripcion, 'La descripción del producto debe tener entre 10 y 1000 caracteres.');
    }

    return true;
}
// AJAX
form.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    if (!validar()) {
        return;
    }

    btnGuardar.disabled = true;     

    try {
        const respuesta = await fetch('php/guardar_producto.php', {
            method: 'POST',
            body: new FormData(form),
        });
        const datos = await respuesta.json();

        alert(datos.mensaje);

        if (datos.ok) {
            form.reset();
            llenarSelect(campos.sucursal, []);
        }
    } catch (error) {
        alert('No se pudo guardar el producto. Intente nuevamente.');
    } finally {
        btnGuardar.disabled = false;
    }
});

iniciar();