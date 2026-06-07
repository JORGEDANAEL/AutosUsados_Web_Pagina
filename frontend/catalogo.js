// Variable global para almacenar el inventario completo
let todosLosAutos = [];

document.addEventListener('DOMContentLoaded', () => {
    verificarEstadoSesion(); // Mandamos llamar al "cadenero" amigable
    cargarAutos();
    
    // Escuchamos cuando el usuario escribe o cambia los selectores
    document.getElementById('inputBuscar').addEventListener('input', filtrarInventario);
    document.getElementById('selectPrecio').addEventListener('change', filtrarInventario);
    document.getElementById('selectOrden').addEventListener('change', filtrarInventario);
});

// --- NUEVA LÓGICA DEL BOTÓN DE SESIÓN ---
function verificarEstadoSesion() {
    fetch('../backend/verificar_sesion.php')
    .then(response => response.json())
    .then(data => {
        const botonAuth = document.getElementById('btnCerrarSesion');
        
        if (!data.activa) {
            // SI ES UN INVITADO: Cambiamos el texto y lo mandamos al login al hacer clic
            botonAuth.textContent = 'Iniciar Sesión / Registrarse';
            botonAuth.style.backgroundColor = '#d32f2f'; // Opcional: lo pintamos de rojo para que destaque
            botonAuth.style.color = 'white';
            
            botonAuth.onclick = () => {
                window.location.href = 'index.html';
            };
        } else {
            // SI TIENE SESIÓN: Funciona como botón de cerrar sesión normal
            botonAuth.textContent = 'Cerrar Sesión';
            
            botonAuth.onclick = () => {
                fetch('../backend/logout.php')
                .then(res => res.json())
                .then(logoutData => {
                    if(logoutData.success) {
                        window.location.href = 'index.html';
                    }
                });
            };
        }
    })
    .catch(error => console.error("Error al verificar sesión:", error));
}

// Obtiene los autos desde el backend (ESTO SE QUEDA IGUAL)
function cargarAutos() {
// ... de aquí para abajo tu código queda exactamente igual
    fetch('../backend/autos.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            todosLosAutos = data.datos; 
            renderizarTarjetas(todosLosAutos); 
        } else {
            document.getElementById('contenedorAutos').innerHTML = '<p style="text-align:center; width:100%;">No hay autos disponibles en este momento.</p>';
        }
    })
    .catch(error => {
        console.error('Error al cargar autos:', error);
        document.getElementById('contenedorAutos').innerHTML = '<p style="color:red; text-align:center; width:100%;">Error al conectar con el servidor.</p>';
    });
}

// Filtra y ordena el inventario en memoria
function filtrarInventario() {
    const textoBuscar = document.getElementById('inputBuscar').value.toLowerCase().trim();
    const precioMaximo = document.getElementById('selectPrecio').value;
    const orden = document.getElementById('selectOrden').value;
    
    // 1. Primero filtramos por texto y precio máximo
    let autosFiltrados = todosLosAutos.filter(auto => {
        const marcaModeloAnio = `${auto.marca} ${auto.modelo} ${auto.anio}`.toLowerCase();
        const coincideTexto = marcaModeloAnio.includes(textoBuscar);
        
        let coincidePrecio = true;
        if (precioMaximo !== 'todos') {
            const max = parseFloat(precioMaximo);
            const precioAuto = parseFloat(auto.precio) || 0;
            coincidePrecio = precioAuto <= max;
        }
        
        return coincideTexto && coincidePrecio;
    });
    
    // 2. Luego aplicamos el ordenamiento al arreglo resultante
    if (orden === 'menor_mayor') {
        autosFiltrados.sort((a, b) => parseFloat(a.precio) - parseFloat(b.precio));
    } else if (orden === 'mayor_menor') {
        autosFiltrados.sort((a, b) => parseFloat(b.precio) - parseFloat(a.precio));
    }
    
    // 3. Dibujamos las tarjetas ya filtradas y ordenadas
    renderizarTarjetas(autosFiltrados);
}

// Construye el HTML de las tarjetas
function renderizarTarjetas(lista) {
    const contenedor = document.getElementById('contenedorAutos');
    
    if (lista.length === 0) {
        contenedor.innerHTML = '<p style="text-align:center; width:100%; padding: 40px; color: #666; font-size: 1.1em;">No encontramos ningún vehículo que coincida con tus filtros de búsqueda.</p>';
        return;
    }
    
    let html = '';
    
    lista.forEach(auto => {
        const rutaImagen = auto.imagen_url ? '../' + auto.imagen_url : 'https://via.placeholder.com/250x150?text=Sin+Imagen';
        
        let botonHTML = '';
        let estiloTarjeta = '';
        const cantidad = parseInt(auto.cantidad) || 0;

        if (cantidad > 0) {
            botonHTML = `<button onclick="window.location.href='detalles.html?id=${auto.id}'" style="width:100%; padding:10px; background:#1a1a1a; color:white; border:none; border-radius:4px; cursor:pointer; font-size:16px; font-weight:bold; margin-top: 10px;">Ver Detalles</button>`;
        } else {
            estiloTarjeta = 'opacity: 0.6; filter: grayscale(50%);';
            botonHTML = `<button disabled style="width:100%; padding:10px; background:#6c757d; color:white; border:none; border-radius:4px; cursor:not-allowed; font-size:16px; font-weight:bold; margin-top: 10px;">Agotado</button>`;
        }
        
        html += `
            <div class="card-auto" style="${estiloTarjeta}">
                <img src="${rutaImagen}" alt="${auto.marca} ${auto.modelo}">
                <div class="card-content" style="padding: 15px;">
                    <h3 style="margin: 0 0 10px 0;">${auto.marca} ${auto.modelo}</h3>
                    <p style="margin: 5px 0;">Año: ${auto.anio}</p>
                    <p style="margin: 5px 0;">Disponibles: <strong>${cantidad}</strong></p>
                    <p class="precio" style="color: #d32f2f; font-size: 1.2em; font-weight: bold; margin: 15px 0;">$${auto.precio} MXN</p>
                    ${botonHTML}
                </div>
            </div>
        `;
    });
    
    contenedor.innerHTML = html;
}