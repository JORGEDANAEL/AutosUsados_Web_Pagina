// Validación de sesión
fetch('../backend/verificar_sesion.php')
.then(response => response.json())
.then(data => { if (!data.activa) window.location.replace('index.html'); });

// Cerrar sesión
document.getElementById('btnCerrarSesion').addEventListener('click', () => {
    fetch('../backend/logout.php').then(() => window.location.href = 'index.html');
});

// Obtener el ID de la URL (ej. detalles.html?id=3)
const urlParams = new URLSearchParams(window.location.search);
const idAuto = urlParams.get('id');

if (!idAuto) {
    window.location.href = 'catalogo.html';
}

// Cargar la información del auto
fetch(`../backend/autos.php?id=${idAuto}`)
.then(response => response.json())
.then(data => {
    const contenedor = document.getElementById('contenedorDetalle');
    
    if (data.success && data.datos) {
        const auto = data.datos;
        const rutaImagen = auto.imagen_url ? '../' + auto.imagen_url : 'https://via.placeholder.com/600x400?text=Sin+Imagen';
        const cantidad = parseInt(auto.cantidad) || 0;
        
        let botonHTML = '';
        if (cantidad > 0) {
            botonHTML = `<button onclick="window.location.href='pago.html?id=${auto.id}'" style="margin-top: 20px;">Proceder al Pago Seguro</button>`;
        } else {
            botonHTML = `<button disabled style="background:#6c757d; cursor:not-allowed; margin-top: 20px;">Vehículo Agotado</button>`;
        }

        contenedor.innerHTML = `
            <div class="detalle-container">
                <div class="detalle-imagen">
                    <img src="${rutaImagen}" alt="${auto.marca} ${auto.modelo}">
                </div>
                <div class="detalle-info">
                    <h1 style="margin: 0; color: var(--dark-bg); font-size: 2.5em;">${auto.marca} ${auto.modelo}</h1>
                    <p style="font-size: 1.2em; color: #666; margin-top: 5px;">Año de fabricación: <strong>${auto.anio}</strong></p>
                    
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                    
                    <h2 style="color: var(--primary-color); font-size: 2em; margin: 0;">$${auto.precio} MXN</h2>
                    <p style="color: #28a745; font-weight: bold;">✓ Unidades disponibles: ${cantidad}</p>
                    <p style="color: #666; font-size: 0.9em;">* El vehículo se entregará con documentación en regla y revisión mecánica aprobada.</p>
                    
                    ${botonHTML}
                </div>
            </div>
        `;
    } else {
        contenedor.innerHTML = '<h2 style="text-align:center; color:red;">No se encontró el vehículo.</h2>';
    }
})
.catch(error => console.error('Error:', error));