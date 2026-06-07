// Variable global para controlar la paginación de logs
let paginaActualLogs = 1;

// --- GUARDIA DE SEGURIDAD DEL FRONTEND ---
fetch('../backend/verificar_sesion.php')
.then(response => response.json())
.then(data => {
    // Si no hay sesión o si el rol NO es admin, lo expulsamos inmediatamente
    if (!data.activa || data.rol !== 'admin') {
        window.location.replace('index.html');
    }
});
// -----------------------------------------

// A partir de aquí sigue tu código normal (document.addEventListener...)

// Esperamos a que el HTML cargue completamente
// Esperamos a que el HTML cargue completamente
document.addEventListener('DOMContentLoaded', () => {
    cargarLogs();
    cargarVentas();
    cargarInventarioAdmin();

    // --- FUNCIONALIDAD DE CERRAR SESIÓN ---
    document.getElementById('btnCerrarSesion').addEventListener('click', () => {
        fetch('../backend/logout.php')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.location.href = 'index.html'; // Lo regresamos al login
            }
        })
        .catch(error => console.error('Error al cerrar sesión:', error));
    });
    // --------------------------------------
});

function cargarLogs() {
    const limitePorPagina = 10; // Puedes cambiar cuántos logs ver aquí
    
    // Pedimos al backend la página actual y el límite
    fetch(`../backend/logs.php?page=${paginaActualLogs}&limit=${limitePorPagina}`)
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('tablaLogs');
        const contenedorPaginacion = document.getElementById('paginacionLogs');
        
        if (data.success) {
            let filas = '';
            
            if(data.datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No hay registros aún.</td></tr>';
                contenedorPaginacion.innerHTML = ''; // Ocultamos paginación
                return;
            }

            // Recorremos los datos paginados
            data.datos.forEach(log => {
                filas += `
                    <tr>
                        <td>${log.id}</td>
                        <td><strong>${log.nombre}</strong></td>
                        <td>${log.fecha_hora_login}</td>
                        <td>${log.ip || 'Localhost'}</td>
                    </tr>
                `;
            });
            
            // Inyectamos filas
            tbody.innerHTML = filas;
            
            // --- DIBUJAMOS LOS CONTROLES DE PAGINACIÓN ---
            renderizarControlesPaginacion(data.paginacion);
            
        } else {
            console.error(data.mensaje);
            if (data.mensaje === "No tienes permisos") { window.location.href = 'index.html'; }
        }
    })
    .catch(error => console.error('Error al cargar logs:', error));
}

document.getElementById('formAgregarAuto').addEventListener('submit', function(e) {
    e.preventDefault(); 

    // Usamos FormData en lugar de JSON para poder enviar archivos
   const formData = new FormData();
    formData.append('marca', document.getElementById('marca').value);
    formData.append('modelo', document.getElementById('modelo').value);
    formData.append('anio', document.getElementById('anio').value);
    formData.append('precio', document.getElementById('precio').value);
    
    // NUEVA LÍNEA: Agregamos la cantidad
    formData.append('cantidad', document.getElementById('cantidad').value);

    // Capturamos el archivo de imagen si el usuario seleccionó uno
    const imagenInput = document.getElementById('imagen');
    if(imagenInput.files.length > 0) {
        formData.append('imagen', imagenInput.files[0]);
    }

    // Hacemos la petición al backend (Fíjate que quitamos el Content-Type)
    fetch('../backend/autos.php', {
        method: 'POST',
        body: formData 
    })
    .then(response => response.json())
    .then(data => {
        const mensajeEl = document.getElementById('mensajeAuto');
        if(data.success) {
            mensajeEl.style.color = 'green';
            mensajeEl.innerText = data.mensaje;
            document.getElementById('formAgregarAuto').reset(); 
                cargarInventarioAdmin(); // Recargamos la tabla de gestión
        } else {
            mensajeEl.style.color = 'red';
            mensajeEl.innerText = data.mensaje;
        }
        setTimeout(() => { mensajeEl.innerText = ''; }, 3000);
    })
    .catch(error => console.error('Error al guardar auto:', error));

});


// Función para cargar las ventas
function cargarVentas() {
    fetch('../backend/ventas.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.getElementById('tablaVentas');
            let filas = '';
            
            if(data.datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Aún no hay autos apartados.</td></tr>';
                return;
            }

            data.datos.forEach(venta => {
                filas += `
                    <tr>
                        <td>#${venta.folio}</td>
                        <td><strong>${venta.cliente}</strong></td>
                        <td><a href="mailto:${venta.email}" style="color: #007bff;">${venta.email}</a></td>
                        <td>${venta.marca} ${venta.modelo}</td>
                        <td>${venta.fecha_solicitud}</td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = filas;
        }
    })
    .catch(error => console.error('Error al cargar ventas:', error));
}

// Carga la lista de autos con botón de Editar para el Admin
function cargarInventarioAdmin() {
    fetch('../backend/autos.php')
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('tablaInventarioAdmin');
        if (data.success && data.datos.length > 0) {
            let filas = '';
            data.datos.forEach(auto => {
                const imgUrl = auto.imagen_url ? '../' + auto.imagen_url : 'https://via.placeholder.com/80x50?text=No+Img';
                filas += `
                    <tr>
                        <td><img src="${imgUrl}" style="width:60px; height:40px; object-fit:cover; border-radius:4px;"></td>
                        <td><strong>${auto.marca} ${auto.modelo}</strong></td>
                        <td>${auto.anio}</td>
                        <td>$${auto.precio}</td>
                        <td>${auto.cantidad} u.</td>
                        <td>
                            <button onclick='abrirModalEditar(${JSON.stringify(auto)})' style="width:auto; padding:5px 10px; background:#007bff; font-size:13px;">Editar</button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = filas;
        } else {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay autos en el inventario.</td></tr>';
        }
    });
}

// Abre el modal y precarga los campos con la info actual del carro
function abrirModalEditar(auto) {
    document.getElementById('edit_id').value = auto.id;
    document.getElementById('edit_marca').value = auto.marca;
    document.getElementById('edit_modelo').value = auto.modelo;
    document.getElementById('edit_anio').value = auto.anio;
    document.getElementById('edit_precio').value = auto.precio;
    document.getElementById('edit_cantidad').value = auto.cantidad;
    
    document.getElementById('modalEditar').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
    document.getElementById('formEditarAuto').reset();

}

// Procesa el envío del formulario de edición
document.getElementById('formEditarAuto').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('id', document.getElementById('edit_id').value);
    formData.append('marca', document.getElementById('edit_marca').value);
    formData.append('modelo', document.getElementById('edit_modelo').value);
    formData.append('anio', document.getElementById('edit_anio').value);
    formData.append('precio', document.getElementById('edit_precio').value);
    formData.append('cantidad', document.getElementById('edit_cantidad').value);

    const imgInput = document.getElementById('edit_imagen');
    if (imgInput.files.length > 0) {
        formData.append('imagen', imgInput.files[0]);
    }

    fetch('../backend/modificar_auto.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.mensaje);
            cerrarModal();
            cargarInventarioAdmin(); // Recargamos la tabla de gestión
            if (typeof cargarAutos === "function") cargarAutos(); // Si comparte funciones
        } else {
            alert("Error: " + data.mensaje);
        }
    })
    .catch(error => console.error('Error:', error));
});

// Función para dibujar los botones de Anterior/Siguiente de los Logs
function renderizarControlesPaginacion(info) {
    const contenedor = document.getElementById('paginacionLogs');
    contenedor.innerHTML = ''; // Limpiamos controles viejos
    
    if(info.total_paginas <= 1) return; // Si solo hay una página, no dibujamos nada

    // Botón Anterior
    const btnAnterior = document.createElement('button');
    btnAnterior.innerText = '← Anterior';
    btnAnterior.style.width = 'auto'; // Para que no ocupe todo el ancho
    btnAnterior.style.background = paginaActualLogs === 1 ? '#ccc' : '#1a1a1a';
    btnAnterior.disabled = paginaActualLogs === 1;
    btnAnterior.style.cursor = paginaActualLogs === 1 ? 'not-allowed' : 'pointer';
    btnAnterior.onclick = () => {
        paginaActualLogs--;
        cargarLogs();
    };
    contenedor.appendChild(btnAnterior);

    // Indicador de página (ej. 1 de 5)
    const infoPagina = document.createElement('span');
    infoPagina.innerText = ` Página ${info.pagina_actual} de ${info.total_paginas} `;
    infoPagina.style.alignSelf = 'center';
    contenedor.appendChild(infoPagina);

    // Botón Siguiente
    const btnSiguiente = document.createElement('button');
    btnSiguiente.innerText = 'Siguiente →';
    btnSiguiente.style.width = 'auto';
    btnSiguiente.style.background = paginaActualLogs === info.total_paginas ? '#ccc' : '#1a1a1a';
    btnSiguiente.disabled = paginaActualLogs === info.total_paginas;
    btnSiguiente.style.cursor = paginaActualLogs === info.total_paginas ? 'not-allowed' : 'pointer';
    btnSiguiente.onclick = () => {
        paginaActualLogs++;
        cargarLogs();
    };
    contenedor.appendChild(btnSiguiente);
}