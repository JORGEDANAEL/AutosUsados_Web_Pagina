// Seguridad
fetch('../backend/verificar_sesion.php')
.then(response => response.json())
.then(data => { if (!data.activa) window.location.replace('index.html'); });

// Cerrar sesión
document.getElementById('btnCerrarSesion').addEventListener('click', () => {
    fetch('../backend/logout.php').then(() => window.location.href = 'index.html');
});

document.addEventListener('DOMContentLoaded', () => {
    fetch('../backend/mis_compras.php')
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('listaMisCompras');
        if (data.success && data.datos.length > 0) {
            let html = '';
            data.datos.forEach(c => {
                const img = c.imagen_url ? '../'+c.imagen_url : 'https://via.placeholder.com/100x60';
                html += `
                    <tr>
                        <td><img src="${img}" style="width:80px; border-radius:4px;"></td>
                        <td>#${c.folio}</td>
                        <td><strong>${c.marca} ${c.modelo}</strong></td>
                        <td style="color: #d32f2f; font-weight: bold;">$${c.precio} MXN</td>
                        <td>${c.fecha_solicitud}</td>
                        <td><span class="estatus-pill">${c.estatus}</span></td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:50px;">Aún no tienes vehículos reservados. <br><br> <a href="catalogo.html" style="color:#d32f2f; font-weight:bold;">Ir al catálogo</a></td></tr>';
        }
    });
});